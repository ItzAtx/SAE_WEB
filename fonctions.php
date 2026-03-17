<?php

    function getConnection() {
        /*Inclus le fichier de paramètres de connexion et établit la connexion avec la BDD*/
        include_once("myparam.inc.php");
        $conn = oci_connect(MYUSER, MYPASS, MYHOST);
        if (!$conn) {
            $e = oci_error();
            die("Erreur de connexion : " . htmlentities($e['message']));
        }
        return $conn;
    }

    function requireLogin() {
        /*Commence la session et vérifie que l'utilisateur s'est bien connecté*/
        session_start();
        if (!isset($_SESSION['id'])) {
            header("Location: index.php");
            exit();
        }
    }

    function execQuery($conn, $sql, $params = []) {
        /*Entrée :
        -Variable de la connexion
        -Requête SQL
        -Paramètre pour les binds

        Sortie :
        -Résultat de la requête sous la forme d'un objet

        Exécute une requête et retourne le résultat*/
        $req = oci_parse($conn, $sql);

        if (!$req) {
            $e = oci_error($conn);
            die("Erreur parse SQL : " . htmlspecialchars($e['message']));
        }

        foreach ($params as $name => &$value) {
            oci_bind_by_name($req, $name, $value);
        }
        unset($value);

        $ok = oci_execute($req);

        if (!$ok) {
            $e = oci_error($req);
            die(
                "Erreur Oracle : " . htmlspecialchars($e['message']) .
                "<br>Requête : " . htmlspecialchars($sql)
            );
        }

        return $req;
    }

    function fetchOne($conn, $requeteP, $params = []) {
        /*Entrée :
        -Variable de la connexion
        -Requête SELECT
        -Paramètre pour les binds

        Sortie :
        -Tableau associatif de la ligne

        Exécute un SELECT et retourne la première (on l'utilise pour les requêtes qui retournet une unique ligne) ligne du résultat*/
        $req = execQuery($conn, $requeteP, $params);
        return oci_fetch_assoc($req);
    }

    function fetchAllRows($conn, $req, $binds = []) {
        //Préparation de la requête
        $requeteP = oci_parse($conn, $req);

        //On remplace chaque paramètres par leurs valeurs
        foreach ($binds as $key => $value) {
            oci_bind_by_name($requeteP, $key, $binds[$key]);
        }
        oci_execute($requeteP);//Execution

        //On remplis le tableau par les lignes du résultat
        $rows = [];
        while ($row = oci_fetch_assoc($requeteP)) {
            $rows[] = $row;
        }

        return $rows;
    }

    function postFieldsFilled(array $fields) {
        /*Entrée :
        -Liste des noms de champs POST à vérifier

        Sortie :
        -Booléen

        Vérifie que tous les champs POST listés sont non vides*/
        foreach ($fields as $field) {
            if (empty($_POST[$field])) return false;
        }
        return true;
    }

    function getIdZone($conn, $libelle) {
        /*Entrée :
        -Variable de la connexion
        -Nom de la zone

        Sortie :
        -id_zone si trouvée, null sinon

        Retourne l'id_zone correspondant à un libellé de zone*/
        $row = fetchOne($conn,
            "SELECT id_zone FROM Zone_zoo WHERE libelle_zone = :libelle",
            [":libelle" => $libelle]
        );
        return $row ? $row['ID_ZONE'] : null;
    }

    function deleteWhere($conn, $table, $column, $value) {
        /*Entrée :
        -Variable de la connexion
        -Nom de la table
        -Nom de la colonne de condition
        -Valeur à matcher

        Supprime toutes les lignes d'une table qui correspondent à une condition*/
        execQuery($conn,
            "DELETE FROM $table WHERE $column = :val",
            [":val" => $value]
        );
    }

    function redirectTo($page) {
        /*Entrée :
        -Nom de la page

        Redirige vers la page en paramètre*/
        header("Location: " . $page);
        exit();
    }

    function redirectSelf() {
        /*Redirige vers la page courante (gestion.php) en conservant les tables cochées
        Les tables actives sont passées en POST (champs cachés dans chaque formulaire) puis retransmises en GET*/
        $params = [];
        foreach (['tablePersonnel','tableEnclos','tableBoutiques','tableAnimaux','tableEspeces'] as $t) {
            if (!empty($_POST[$t])) {
                $params[] = $t.'='.$_POST[$t]; //Si la case est cochée, on l'ajoute dans la liste des tables à garder
            }
        }
        $url = count($params) ? '?'.implode('&', $params) : ''; //S'il y a au moins une table, alors on la met dans l'url, sinon on ne met rien
        header("Location: gestion.php".$url);
        exit();
    }

    function hiddenTables() {
        /*Génère les champs cachés pour transmettre les tables actives lors d'un POST
        Permet à redirectSelf() de reconstruire les paramètres GET après soumission*/
        foreach (['tablePersonnel','tableEnclos','tableBoutiques','tableAnimaux','tableEspeces'] as $t) {
            if (!empty($_GET[$t])) {
                echo '<input type="hidden" name="'.$t.'" value="'.htmlspecialchars($_GET[$t]).'">';
            }
        }
    }

    function deleteAnimal($conn, $rfid) {
        /*Entrée :
        -Variable de la connexion
        -RFID de l'animal à supprimer

        Supprime un animal et toutes ses dépendances :
        Attitre, Soins, Parrainer, Contient (via Repas), Repas, Animal
        On met aussi à NULL les références père/mère des autres animaux*/

        //Suppression dans Contient pour chaque repas de l'animal
        $reqRepas = oci_parse($conn, "SELECT id_repas FROM Repas WHERE RFID = :rfid");
        oci_bind_by_name($reqRepas, ':rfid', $rfid);
        oci_execute($reqRepas, OCI_NO_AUTO_COMMIT);
        while ($r = oci_fetch_assoc($reqRepas)) {
            deleteWhere($conn, 'Contient', 'id_repas', $r['ID_REPAS']);
        }

        //Suppression des liens père/mère vers cet animal
        execQuery($conn, "UPDATE Animal SET RFID_a_pour_pere = NULL WHERE RFID_a_pour_pere = :rfid", [':rfid' => $rfid]);
        execQuery($conn, "UPDATE Animal SET RFID_a_pour_mere = NULL WHERE RFID_a_pour_mere = :rfid", [':rfid' => $rfid]);

        //Suppressions simples dans les autres tables
        deleteWhere($conn, 'Attitre', 'RFID', $rfid);
        deleteWhere($conn, 'Soins', 'RFID', $rfid);
        deleteWhere($conn, 'Parrainer', 'RFID', $rfid);
        deleteWhere($conn, 'Repas', 'RFID', $rfid);
        deleteWhere($conn, 'Animal', 'RFID', $rfid);
    }

    function btnSupprimer($hiddenName, $hiddenValue) {
        /*Entrée :
        -Nom du champs caché
        -Valeur de l'ID à supprimer

        Génère le formulaire HTML du bouton Supprimer pour une ligne de tableau*/
        echo '<form method="post">';
        hiddenTables();
        echo '<input type="hidden" name="'.$hiddenName.'" value="'.htmlspecialchars($hiddenValue).'">';
        echo '<input type="submit" value="Supprimer">';
        echo '</form>';
    }

    function btnModifier($param, $value) {
        /*Entrée :
        -Nom du paramètre GET
        -Valeur de l'ID à modifier

        Génère le bouton Modifier sous forme d'un lien avec conservation des tables actuellement affichées*/
        $params = [];

        foreach (['tablePersonnel','tableEnclos','tableBoutiques','tableAnimaux','tableEspeces'] as $t) {
            if (!empty($_GET[$t])) {
                $params[] = $t.'='.$_GET[$t];  //Construction de la liste des tables cochées
            }
        }

        $params[] = $param.'='.$value; //Ajout de l'identification de la ligne modifiée, exemple : edit_boutique=2 => modification de la ligne 2 dans Boutique

        $url = 'gestion.php?'.implode('&', $params);
        echo '<a href="'.$url.'"><button type="button">Modifier</button></a>';
    }

    function btnAnnuler() {
        /*Génère le bouton Annuler*/
        $params = [];
        foreach (['tablePersonnel','tableEnclos','tableBoutiques','tableAnimaux','tableEspeces'] as $t) {
            if (!empty($_GET[$t])) {
                $params[] = $t.'='.$_GET[$t]; //Construction de la liste des tables cochées
            }
        }
        $url = 'gestion.php'.(count($params) ? '?'.implode('&', $params) : ''); //Si le nombre de tableaux dans la liste est différent de 0, on crée le lien
        echo '<a href="'.$url.'"><button type="button">Annuler</button></a>';
    }

    function selectZone($name, $selected = '') {
        /*Entrée :
        -Attribut name du <select>

        Génère un <select> avec les zones du zoo*/
        $zones = ['Zone Afrique','Zone Asie','Zone France','Zone Dinosaure','Zone Aquatique'];
        echo '<select name="'.$name.'">';
        foreach ($zones as $z) {
            $sel = ($z === $selected) ? ' selected' : '';
            echo '<option value="'.$z.'"'.$sel.'>'.$z.'</option>';
        }
        echo '</select>';
    }

    function selectFonction($name, $selected = '') {
        /*Entrée :
        -Attribut name du <select>
        -Valeur à présélectionner

        Génère un <select> avec les fonctions*/
        $fonctions = ['Directeur','Technicien','Soigneur','Employe de magasin','Directeur de magasin'];
        echo '<select name="'.$name.'">';
        foreach ($fonctions as $f) {
            $sel = ($f === $selected) ? ' selected' : '';
            echo '<option value="'.$f.'"'.$sel.'>'.$f.'</option>';
        }
        echo '</select>';
    }

    function selectResponsableBoutique($conn, $name, $selected = '') {
        /*Entrée :
        -Variable de la connexion
        -Attribut name du <select>
        -Valeur pré-sélectionnée (prénom nom)

        Génère un <select> avec uniquement les Directeurs de magasin*/
        $req = oci_parse($conn,
            "SELECT P.id_personnel, P.prenom_personnel, P.nom_personnel
            FROM Personnel P, Contrat C, Fonction F
            WHERE P.id_personnel = C.id_personnel
            AND C.id_fonction = F.id_fonction
            AND F.fonction = 'Directeur de magasin'
            AND P.archiver_personnel = 'N'
            ORDER BY P.nom_personnel"
        );
        oci_execute($req);
        echo '<select name="'.$name.'">'; //Création du select
        while ($row = oci_fetch_assoc($req)) {
            $label = $row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL']; //Concaténation du prénom et du nom
            $sel = ($label === $selected) ? ' selected' : ''; //Si ça correspond au responsable actuellement sélectionnée (paramètre), on met selected sinon chaîne vide
            echo '<option value="'.htmlspecialchars($label).'"'.$sel.'>'.htmlspecialchars($label).'</option>'; //Ajout de l'option selected si besoin
        }
        echo '</select>';
    }

    function selectEspece($conn, $name, $selected = '') {
        /*Entrée :
        -Variable de la connexion
        -Attribut name du <select>
        -Valeur pré-sélectionnée (nom_usuel)

        Génère un <select> avec toutes les espèces connues*/
        $req = oci_parse($conn, "SELECT nom_usuel FROM Espece ORDER BY nom_usuel");
        oci_execute($req);
        echo '<select name="'.$name.'">'; //Création du select
        while ($row = oci_fetch_assoc($req)) {
            $val = $row['NOM_USUEL'];
            $sel = ($val === $selected) ? ' selected' : '';
            echo '<option value="'.htmlspecialchars($val).'"'.$sel.'>'.htmlspecialchars($val).'</option>';
        }
        echo '</select>';
    }
?>