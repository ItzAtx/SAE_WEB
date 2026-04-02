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

    function btnArchiver($id) {
        /*Entrée :
        -Nom du champs caché
        -Valeur de l'ID à archiver

        Génère le formulaire HTML du bouton Archiver pour une ligne de tableau*/
        $params = [];
        foreach (['tablePersonnel','tableEnclos','tableBoutiques','tableAnimaux','tableEspeces'] as $t) {
            if (!empty($_GET[$t])) {
                $params[] = $t.'='.$_GET[$t];
            }
        }
        $params[] = 'confirmer_archivage='.$id;
        $url = 'gestion.php?'.implode('&', $params);
        echo '<a href="'.$url.'"><button type="submit" class="btn-archive">Archiver</button></a>';
    }

    function btnSupprimer($hiddenName, $hiddenValue) {
        /*Entrée :
        -Nom du champs caché
        -Valeur de l'ID à supprimer

        Génère le formulaire HTML du bouton Supprimer pour une ligne de tableau*/
        echo '<form method="post">';
        hiddenTables();
        echo '<input type="hidden" name="'.$hiddenName.'" value="'.htmlspecialchars($hiddenValue).'">';
        echo '<button type="submit" class="btn-delete">Supprimer</button>';
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

    function selectFonction($conn, $name, $selected = '') {
        /*Entrée :
        -Attribut name du <select>
        -Valeur à présélectionner

        Génère un <select> avec les fonctions*/
        $req = oci_parse($conn, "SELECT fonction FROM Fonction ORDER BY fonction");
        oci_execute($req);
        echo '<select name="' . $name . '">';
        while ($row = oci_fetch_assoc($req)) {
            $f = $row['FONCTION'];
            $sel = ($f === $selected) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($f) . '"' . $sel . '>' . htmlspecialchars($f) . '</option>';
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
            "SELECT id_personnel, prenom_personnel, nom_personnel
            FROM Vue_Personnel
            WHERE fonction = 'Directeur de magasin'
            AND archiver_personnel = 'N'
            ORDER BY nom_personnel"
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

    function getNextId($conn, $table, $colonne){
        $row = fetchOne($conn, "SELECT NVL(MAX($colonne), 0) + 1 AS next_id FROM $table");
        return $row['NEXT_ID'];
    }

    function archiverPersonnel($conn, $id, $dateFin) {
        execQuery($conn, "UPDATE Personnel SET archiver_personnel = 'O' WHERE id_personnel = :id", [':id' => $id]);
        execQuery($conn, "UPDATE Contrat SET date_fin = TO_DATE(:date_fin, 'YYYY-MM-DD') WHERE id_personnel = :id AND date_fin IS NULL", [':date_fin' => $dateFin, ':id' => $id]);
        oci_commit($conn);
        redirectSelf();
    }

    function remplacerChefSoigneur($conn, $id) {
        $zoneResponsable = fetchOne($conn, "SELECT id_zone FROM Vue_Zone WHERE id_personnel = :id", [':id' => $id]);
        
        if (!$zoneResponsable) {
            execQuery($conn, "DELETE FROM Chef WHERE id_personnel_est_manager_par = :id", [':id' => $id]);
            return true;
        }

        $equipiers = fetchAllRows($conn,
            "SELECT id_personnel_est_manager_par AS id_equipier FROM Chef WHERE id_personnel_manager_de = :id ORDER BY id_personnel_est_manager_par",
            [':id' => $id]
        );

        if (empty($equipiers)) return false;

        $idRemplacant = $equipiers[0]['ID_EQUIPIER'];
        $autresEquipiers = array_slice($equipiers, 1);

        execQuery($conn, "DELETE FROM Chef WHERE id_personnel_manager_de = :id", [':id' => $id]);
        execQuery($conn, "DELETE FROM Chef WHERE id_personnel_est_manager_par = :r", [':r' => $idRemplacant]);

        foreach ($autresEquipiers as $eq) {
            execQuery($conn, "INSERT INTO Chef VALUES (:r, :e)", [':r' => $idRemplacant, ':e' => $eq['ID_EQUIPIER']]);
        }

        execQuery($conn, "UPDATE Zone_zoo SET id_personnel = :r WHERE id_zone = :z", [':r' => $idRemplacant, ':z' => $zoneResponsable['ID_ZONE']]);
        return true;
    }

    function gererDepartFonction($conn, $id, $fonction) {
        if ($fonction === 'Directeur') {
            $rowCount = fetchOne($conn,
                "SELECT COUNT(*) AS nb
                FROM Vue_Personnel
                WHERE fonction = 'Directeur'
                AND archiver_personnel = 'N'"
            );

            if ($rowCount['NB'] <= 1) {
                return "Impossible : ce personnel est le seul directeur.";
            }

            execQuery($conn,
                "UPDATE Zone_zoo SET id_personnel = NULL WHERE id_personnel = :id",
                [':id' => $id]
            );
            return true;
        }

        if ($fonction === 'Directeur de magasin') {
            $rowCount = fetchOne($conn,
                "SELECT COUNT(*) AS nb
                FROM Vue_Personnel
                WHERE fonction = 'Directeur de magasin'
                AND archiver_personnel = 'N'"
            );

            if ($rowCount['NB'] <= 1) {
                return "Impossible : ce personnel est le seul directeur de magasin.";
            }

            $autreDir = fetchOne($conn,
                "SELECT MIN(id_personnel) AS id_personnel
                FROM Vue_Personnel
                WHERE fonction = 'Directeur de magasin'
                AND archiver_personnel = 'N'
                AND id_personnel <> :id",
                [':id' => $id]
            );

            execQuery($conn,
                "UPDATE Boutique
                SET id_personnel = :new_id
                WHERE id_personnel = :id",
                [':new_id' => $autreDir['ID_PERSONNEL'], ':id' => $id]
            );
            return true;
        }

        if ($fonction === 'Employe de magasin') {
            deleteWhere($conn, 'Travaille', 'id_personnel', $id);
            return true;
        }

        if ($fonction === 'Soigneur') {
            if (!remplacerChefSoigneur($conn, $id)) {
                return "Impossible : ce chef soigneur n'a pas d'équipier pour le remplacer.";
            }
            deleteWhere($conn, 'Attitre', 'id_personnel', $id);
            return true;
        }

        if ($fonction === 'Technicien') {
            deleteWhere($conn, 'Entretient', 'id_personnel', $id);
            return true;
        }

        return true;
    }
?>