<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include_once("myparam.inc.php");
$conn = oci_connect(MYUSER, MYPASS, MYHOST); //Connexion à la BDD

$message = "";

/* ==========
   FONCTIONS
============= */

function execQuery($conn, $requeteP, $params = []) {
    /*Entrée :
      -Variable de la connexion
      -Requête voulue
      -Paramètre pour les binds

      Sortie :
      -Ressource contenant le résultat de la requête

      La fonction permet de préparer, de mettre les paramètres et d'éxecuter la requête*/
    $req = oci_parse($conn, $requeteP);
    foreach ($params as $name => &$value) { //Le & permet à oci_bind_by_name de lire la valeur au moment de oci_execute et non au moment du bind
        oci_bind_by_name($req, $name, $value);
    }
    unset($value); //Coupe la référence vers le dernier élément du foreach
    oci_execute($req, OCI_NO_AUTO_COMMIT);
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

function redirectSelf() {
    /*Redirige vers la page courante (gestion.php)*/
    header("Location: gestion.php");
    exit();
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
        "SELECT id_zone FROM Zone_zoo WHERE libelle = :libelle",
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

/* =========================
   SUPPRESSION PERSONNEL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_personnel'])) {
    $id = $_POST['supprimer_id_personnel'];
    deleteWhere($conn, 'Contrat', 'id_personnel', $id); //Suppression dans Contrat
    deleteWhere($conn, 'Personnel', 'id_personnel', $id); //Suppression dans Personnel
    oci_commit($conn);
    redirectSelf();
}

/* =========================
   AJOUT PERSONNEL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_personnel'])) {
    $fields = ['id_personnel','prenom_personnel','nom_personnel','mot_de_passe', 'id_connexion','zone_personnel','id_contrat','salaire','date_debut','fonction']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) {
        $message = "Veuillez remplir tous les champs du personnel.";
    } else {
        $id_zone = getIdZone($conn, $_POST['zone_personnel']);
        $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        //Ajout des données dans Personnel
        execQuery($conn,
            "INSERT INTO Personnel VALUES (:id_personnel, :nom_personnel, :prenom_personnel, :mot_de_passe, :id_connexion, :id_zone)",
            [":id_personnel"=>$_POST['id_personnel'],":nom_personnel"=>$_POST['nom_personnel'], ":prenom_personnel"=>$_POST['prenom_personnel'],":mot_de_passe"=>$mot_de_passe, ":id_connexion"=>$_POST['id_connexion'],":id_zone"=>$id_zone]
        );
        //On cherche l'id_fonction correspondant à la fonction donnée
        $rowFonction = fetchOne($conn,
            "SELECT id_fonction FROM Fonction WHERE fonction = :fonction",
            [":fonction" => $_POST['fonction']]
        );
        //Ajout des données dans Contrat
        execQuery($conn,
            "INSERT INTO Contrat VALUES (:id_contrat, :salaire, TO_DATE(:date_debut,'YYYY-MM-DD'), NULL, :id_fonction, :id_personnel)",
            [":id_contrat"=>$_POST['id_contrat'],":salaire"=>$_POST['salaire'], ":date_debut"=>$_POST['date_debut'],":id_fonction"=>$rowFonction['ID_FONCTION'], ":id_personnel"=>$_POST['id_personnel']]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   MODIFICATION PERSONNEL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_personnel'])) {
    $fields = ['edit_id_personnel','edit_prenom_personnel','edit_nom_personnel', 'edit_id_connexion','edit_zone_personnel','edit_salaire','edit_date_debut','edit_fonction']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) {
        $message = "Veuillez remplir tous les champs pour la modification.";
    } else {
        $id_zone = getIdZone($conn, $_POST['edit_zone_personnel']);
        //Modification des données dans Personnel
        execQuery($conn,
            "UPDATE Personnel SET prenom_personnel=:prenom, nom_personnel=:nom, id_connexion=:id_connexion, id_zone=:id_zone
            WHERE id_personnel=:id_personnel",
            [":prenom"=>$_POST['edit_prenom_personnel'],":nom"=>$_POST['edit_nom_personnel'], ":id_connexion"=>$_POST['edit_id_connexion'],":id_zone"=>$id_zone, ":id_personnel"=>$_POST['edit_id_personnel']]
        );
        //On cherche l'id_fonction correspondant à la fonction donnée
        $rowFonction = fetchOne($conn,
            "SELECT id_fonction FROM Fonction WHERE fonction = :fonction",
            [":fonction" => $_POST['edit_fonction']]
        );
        //Modification des données dans Contrat
        execQuery($conn,
            "UPDATE Contrat SET salaire=:salaire, date_debut=TO_DATE(:date_debut,'YYYY-MM-DD'), id_fonction=:id_fonction
            WHERE id_personnel=:id_personnel",
            [":salaire"=>$_POST['edit_salaire'],":date_debut"=>$_POST['edit_date_debut'], ":id_fonction"=>$rowFonction['ID_FONCTION'],":id_personnel"=>$_POST['edit_id_personnel']]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   SUPPRESSION ENCLOS
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_enclos'])) {
    $id = $_POST['supprimer_id_enclos'];
    deleteWhere($conn, 'Possede', 'id_enclos', $id); //Suppression dans Possede
    deleteWhere($conn, 'Reparation', 'id_enclos', $id); //Suppression dans Reparation
    deleteWhere($conn, 'Enclos', 'id_enclos', $id); //Suppression dans Enclos
    oci_commit($conn);
    redirectSelf();
}

/* =========================
   AJOUT ENCLOS
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_enclos'])) {
    $fields = ['id_enclos','latitude','longitude','surface','zone_enclos']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) {
        $message = "Veuillez remplir tous les champs de l'enclos.";
    } else {
        $id_zone = getIdZone($conn, $_POST['zone_enclos']);
        //Ajout des données dans Enclos
        execQuery($conn,
            "INSERT INTO Enclos VALUES (:id_enclos, :latitude, :longitude, :surface, :id_zone)",
            [":id_enclos"=>$_POST['id_enclos'],":latitude"=>$_POST['latitude'], ":longitude"=>$_POST['longitude'],":surface"=>$_POST['surface'],":id_zone"=>$id_zone]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   MODIFICATION ENCLOS
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_enclos'])) {
    $fields = ['edit_id_enclos','edit_latitude','edit_longitude','edit_surface','edit_zone_enclos']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) {
        $message = "Veuillez remplir tous les champs pour la modification.";
    } else {
        $id_zone = getIdZone($conn, $_POST['edit_zone_enclos']);
        //Modification des données dans Enclos
        execQuery($conn,
            "UPDATE Enclos SET latitude=:latitude, longitude=:longitude, surface=:surface, id_zone=:id_zone 
            WHERE id_enclos=:id_enclos",
            [":latitude"=>$_POST['edit_latitude'],":longitude"=>$_POST['edit_longitude'], ":surface"=>$_POST['edit_surface'],":id_zone"=>$id_zone, ":id_enclos"=>$_POST['edit_id_enclos']]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   SUPPRESSION BOUTIQUE
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_boutique'])) {
    $id = $_POST['supprimer_id_boutique'];
    deleteWhere($conn, 'Travaille', 'id_boutique', $id); //Suppression dans Travaille
    deleteWhere($conn, 'Chiffre_affaire', 'id_boutique', $id); //Suppression dans Chiffre_Affaire
    deleteWhere($conn, 'Boutique', 'id_boutique', $id); //Suppression dans Boutique
    oci_commit($conn);
    redirectSelf();
}

/* =========================
   AJOUT BOUTIQUE
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_boutique'])) {
    $fields = ['id_boutique','nom_boutique','type_boutique','responsable_boutique','zone_boutique']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) {
        $message = "Veuillez remplir tous les champs de la boutique.";
    } else {
        //On cherche l'id_personnel correspondant au responsable de la boutique donnée
        $rowPers = fetchOne($conn,
            "SELECT id_personnel FROM Personnel WHERE prenom_personnel || ' ' || nom_personnel = :responsable",
            [":responsable" => $_POST['responsable_boutique']]
        );
        $id_zone = getIdZone($conn, $_POST['zone_boutique']);
        //Ajout des données dans Boutique
        execQuery($conn,
            "INSERT INTO Boutique VALUES (:id_boutique, :nom_boutique, :type_boutique, :id_personnel, :id_zone)",
            [":id_boutique"=>$_POST['id_boutique'],":nom_boutique"=>$_POST['nom_boutique'], ":type_boutique"=>$_POST['type_boutique'],":id_personnel"=>$rowPers['ID_PERSONNEL'], ":id_zone"=>$id_zone]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   MODIFICATION BOUTIQUE
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_boutique'])) {
    $fields = ['edit_id_boutique','edit_nom_boutique','edit_type_boutique', 'edit_responsable_boutique','edit_zone_boutique']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) { 
        $message = "Veuillez remplir tous les champs pour la modification.";
    } else {
        //On cherche l'id_personnel correspondant au responsable de la boutique donnée
        $rowPers = fetchOne($conn,
            "SELECT id_personnel FROM Personnel WHERE prenom_personnel || ' ' || nom_personnel = :responsable",
            [":responsable" => $_POST['edit_responsable_boutique']]
        );
        //Modification des données dans Boutique
        execQuery($conn,
            "UPDATE Boutique SET nom_boutique=:nom, type_boutique=:type, id_personnel=:id_personnel, id_zone=:id_zone 
            WHERE id_boutique=:id_boutique",
            [":nom"=>$_POST['edit_nom_boutique'],":type"=>$_POST['edit_type_boutique'], ":id_personnel"=>$rowPers['ID_PERSONNEL'],":id_zone"=>$id_zone, ":id_boutique"=>$_POST['edit_id_boutique']]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   SUPPRESSION ANIMAL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_rfid'])) {
    $rfid = $_POST['supprimer_rfid'];
    deleteWhere($conn, 'Attitre', 'RFID', $rfid); //Suppression dans Attitre
    deleteWhere($conn, 'Soins', 'RFID', $rfid); //Suppression dans Soins
    deleteWhere($conn, 'Consomme', 'RFID', $rfid); //Suppression dans Consomme
    deleteWhere($conn, 'Parrainer', 'RFID', $rfid); //Suppression dans Parrainer
    deleteWhere($conn, 'Animal', 'RFID', $rfid); //Suppression dans Animal
    oci_commit($conn);
    redirectSelf();
}

/* =========================
   AJOUT ANIMAL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_animal'])) {
    $fields = ['rfid','nom_animal','date_naissance','poids','id_enclos_animal','espece_animal']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) {
        $message = "Veuillez remplir tous les champs de l'animal.";
    } else {
        //On cherche le nom_latin correspondant a l'espèce donnée
        $rowEspece = fetchOne($conn,
            "SELECT nom_latin FROM Espece WHERE nom_usuel = :nom_usuel",
            [":nom_usuel" => $_POST['espece_animal']]
        );
        //Ajout des données dans Animal
        execQuery($conn,
            "INSERT INTO Animal VALUES (:rfid, :nom_animal, TO_DATE(:date_naissance,'YYYY-MM-DD'), :poids, NULL, NULL, :id_enclos, :nom_latin)",
            [":rfid"=>$_POST['rfid'],":nom_animal"=>$_POST['nom_animal'], ":date_naissance"=>$_POST['date_naissance'],":poids"=>$_POST['poids'], ":id_enclos"=>$_POST['id_enclos_animal'],":nom_latin"=>$rowEspece['NOM_LATIN']]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   MODIFICATION ANIMAL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_animal'])) {
    $fields = ['edit_rfid','edit_nom_animal','edit_date_naissance','edit_poids', 'edit_id_enclos_animal','edit_espece_animal']; //Champs POST à vérifier
    if (!postFieldsFilled($fields)) {
        $message = "Veuillez remplir tous les champs pour la modification.";
    } else {
        //On cherche le nom_latin correspondant a l'espèce donnée
        $rowEspece = fetchOne($conn,
            "SELECT nom_latin FROM Espece WHERE nom_usuel = :nom_usuel",
            [":nom_usuel" => $_POST['edit_espece_animal']]
        );
        //Modification des données dans Animal
        execQuery($conn,
            "UPDATE Animal SET nom_animal=:nom, date_naissance=TO_DATE(:dn,'YYYY-MM-DD'), poids=:poids, id_enclos=:id_enclos, nom_latin=:nom_latin 
            WHERE RFID=:rfid",
            [":nom"=>$_POST['edit_nom_animal'],":dn"=>$_POST['edit_date_naissance'], ":poids"=>$_POST['edit_poids'],":id_enclos"=>$_POST['edit_id_enclos_animal'], ":nom_latin"=>$rowEspece['NOM_LATIN'],":rfid"=>$_POST['edit_rfid']]
        );
        oci_commit($conn);
        redirectSelf();
    }
}

/* =========================
   REQUÊTES D'AFFICHAGE
========================= */

//Affiche les données de Personnel
$requetePersonnel = oci_parse($conn,
    "SELECT P.id_personnel, P.prenom_personnel, P.nom_personnel, P.id_connexion, Z.libelle AS zone_libelle, C.id_contrat, C.salaire, TO_CHAR(C.date_debut,'YYYY-MM-DD') AS date_debut, F.fonction
     FROM Personnel P, Contrat C, Fonction F, Zone_zoo Z
     WHERE C.id_personnel = P.id_personnel
     AND C.id_fonction = F.id_fonction
     AND P.id_zone = Z.id_zone(+)
     ORDER BY P.id_personnel"
);
oci_execute($requetePersonnel);

//Affiche les données de Enclos
$requeteEnclos = oci_parse($conn,
    "SELECT E.id_enclos, E.latitude, E.longitude, E.surface, Z.libelle AS zone_libelle
     FROM Enclos E, Zone_zoo Z
     WHERE E.id_zone = Z.id_zone
     ORDER BY E.id_enclos"
);
oci_execute($requeteEnclos);

//Affiche les données de Boutique
$requeteBoutique = oci_parse($conn,
    "SELECT B.id_boutique, B.nom_boutique, B.type_boutique, P.prenom_personnel || ' ' || P.nom_personnel AS responsable, Z.libelle AS zone_libelle
     FROM Boutique B, Personnel P, Zone_zoo Z
     WHERE B.id_personnel = P.id_personnel(+)
     AND B.id_zone = Z.id_zone
     ORDER BY B.id_boutique"
);
oci_execute($requeteBoutique);

//Affiche les données de Animal
$requeteAnimal = oci_parse($conn,
    "SELECT A.RFID, A.nom_animal, TO_CHAR(A.date_naissance,'YYYY-MM-DD') AS date_naissance, A.poids, A.id_enclos, E.nom_usuel
     FROM Animal A, Espece E
     WHERE A.nom_latin = E.nom_latin
     ORDER BY A.RFID"
);
oci_execute($requeteAnimal);

//ID en cours d'édition
$editPersonnel = $_GET['edit_personnel'] ?? null;
$editEnclos = $_GET['edit_enclos'] ?? null;
$editBoutique  = $_GET['edit_boutique'] ?? null;
$editAnimal = $_GET['edit_animal'] ?? null;

//Tables à afficher
$tablePersonnel = $_GET['tablePersonnel'] ?? 0;
$tableEnclos = $_GET['tableEnclos'] ?? 0;
$tableBoutiques = $_GET['tableBoutiques'] ?? 0;
$tableAnimaux = $_GET['tableAnimaux'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion</title>
    <link rel="stylesheet" href="css/gestion.css">
</head>
<body>

<a href="search.php"><button type="button">Accueil</button></a>

<?php if ($message !== "") {
    echo "<p>".htmlspecialchars($message)."</p>";
}

/* =========================
   FONCTIONS D'AFFICHAGE
========================= */

function btnSupprimer($hiddenName, $hiddenValue) {
    /*Entrée :
      -Nom du champs caché
      -Valeur de l'ID à supprimer

      Génère le formulaire HTML du bouton Supprimer pour une ligne de tableau*/
    echo '<form method="post">';
    echo '<input type="hidden" name="'.$hiddenName.'" value="'.htmlspecialchars($hiddenValue).'">';
    echo '<input type="submit" value="Supprimer">';
    echo '</form>';
}

function btnModifier($param, $value) {
    /*Entrée :
      -Nom du paramètre GET
      -Valeur de l'ID à modifier

      Génère le bouton Modifier sous forme d'un lien avec paramètre GET*/
    $url = 'gestion.php'.'?'.$param.'='.$value;
    echo '<a href="'.$url.'"><button type="button">Modifier</button></a>';
}

function btnAnnuler() {
    /*Génère le bouton Annuler qui ramène à la page sans paramètre GET*/
    echo '<a href="gestion.php"><button type="button">Annuler</button></a>';
}

function selectZone($name, $selected = '') {
    /*Entrée :
      -Attribut name du <select>

      Génère un <select> avec les zones du zoo*/
    $zones = ['Zone Afrique','Zone Asie','Zone France','Zone Dinosaure','Zone Aquatique'];
    echo '<select name="'.$name.'">';
    foreach ($zones as $z) {
        echo '<option value="'.$z.'">'.$z.'</option>';
    }
    echo '</select>';
}

function selectFonction($name) {
    /*Entrée :
      -Attribut name du <select>

      Génère un <select> avec les fonctions*/
    $fonctions = ['Directeur','Technicien','Soigneur','Employe de magasin','Directeur de magasin'];
    echo '<select name="'.$name.'">';
    foreach ($fonctions as $f) {
        echo '<option value="'.$f.'">'.$f.'</option>';
    }
    echo '</select>';
}
?>

<!-- ===================== PERSONNEL ===================== -->
<?php if ($tablePersonnel): ?>
    <h2>Gestion du personnel</h2>
    <table border="1">
        <tr>
            <th>ID_PERSONNEL</th>
            <th>PRENOM</th>
            <th>NOM</th>
            <th>ID_CONNEXION</th>
            <th>ZONE</th>
            <th>ID_CONTRAT</th>
            <th>SALAIRE</th>
            <th>DEBUT_CONTRAT</th>
            <th>FONCTION</th>
            <th>MDP</th>
            <th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requetePersonnel)): ?>
            <?php if ($editPersonnel == $row['ID_PERSONNEL']): ?>
                <!-- MODE ÉDITION -->
                <tr>
                    <form method="post">
                        <input type="hidden" name="edit_id_personnel" value="<?php echo $row['ID_PERSONNEL']; ?>">
                        <td><?php echo htmlspecialchars($row['ID_PERSONNEL']); ?></td>
                        <td><input type="text" name="edit_prenom_personnel" value="<?php echo htmlspecialchars($row['PRENOM_PERSONNEL']); ?>"></td>
                        <td><input type="text" name="edit_nom_personnel"   value="<?php echo htmlspecialchars($row['NOM_PERSONNEL']); ?>"></td>
                        <td><input type="text" name="edit_id_connexion"    value="<?php echo htmlspecialchars($row['ID_CONNEXION']); ?>"></td>
                        <td><?php selectZone('edit_zone_personnel', $row['ZONE_LIBELLE']); ?></td>
                        <td><?php echo htmlspecialchars($row['ID_CONTRAT']); ?></td>
                        <td><input type="text" name="edit_salaire"    value="<?php echo htmlspecialchars($row['SALAIRE']); ?>"></td>
                        <td><input type="date" name="edit_date_debut" value="<?php echo htmlspecialchars($row['DATE_DEBUT']); ?>"></td>
                        <td><?php selectFonction('edit_fonction'); ?></td>
                        <td><i>(inchangé)</i></td>
                        <td>
                            <input type="submit" name="modifier_personnel" value="Valider">
                            <?php btnAnnuler(); ?>
                        </td>
                    </form>
                </tr>
            <?php else: ?>
                <!-- MODE NORMAL -->
                <tr>
                    <td><?php echo htmlspecialchars($row['ID_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($row['PRENOM_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($row['NOM_PERSONNEL']); ?></td>
                    <td><?php echo htmlspecialchars($row['ID_CONNEXION']); ?></td>
                    <td><?php echo htmlspecialchars($row['ZONE_LIBELLE']); ?></td>
                    <td><?php echo htmlspecialchars($row['ID_CONTRAT']); ?></td>
                    <td><?php echo htmlspecialchars($row['SALAIRE']); ?></td>
                    <td><?php echo htmlspecialchars($row['DATE_DEBUT']); ?></td>
                    <td><?php echo htmlspecialchars($row['FONCTION']); ?></td>
                    <td>************</td>
                    <td>
                        <?php btnModifier('edit_personnel', $row['ID_PERSONNEL']); ?>
                        <?php btnSupprimer('supprimer_id_personnel', $row['ID_PERSONNEL']); ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>

        <!-- LIGNE AJOUT -->
        <tr>
            <form method="post">
                <td><input type="text" name="id_personnel"></td>
                <td><input type="text" name="prenom_personnel"></td>
                <td><input type="text" name="nom_personnel"></td>
                <td><input type="text" name="id_connexion"></td>
                <td><?php selectZone('zone_personnel'); ?></td>
                <td><input type="text" name="id_contrat"></td>
                <td><input type="text" name="salaire"></td>
                <td><input type="date" name="date_debut"></td>
                <td><?php selectFonction('fonction'); ?></td>
                <td><input type="password" name="mot_de_passe"></td>
                <td><input type="submit" name="ajouter_personnel" value="Ajouter"></td>
            </form>
        </tr>
    </table>

    <br><br>
<?php endif; ?>

<!-- ===================== ENCLOS ===================== -->
<?php if ($tableEnclos): ?>
    <h2>Gestion des enclos</h2>
    <table border="1">
        <tr>
            <th>ID_ENCLOS</th><th>LATITUDE</th><th>LONGITUDE</th><th>SURFACE</th><th>ZONE</th><th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requeteEnclos)): ?>
            <?php if ($editEnclos == $row['ID_ENCLOS']): ?>
                <!-- MODE ÉDITION -->
                <tr>
                    <form method="post">
                        <input type="hidden" name="edit_id_enclos" value="<?php echo $row['ID_ENCLOS']; ?>">
                        <td><?php echo htmlspecialchars($row['ID_ENCLOS']); ?></td>
                        <td><input type="text" name="edit_latitude" value="<?php echo htmlspecialchars($row['LATITUDE']); ?>"></td>
                        <td><input type="text" name="edit_longitude" value="<?php echo htmlspecialchars($row['LONGITUDE']); ?>"></td>
                        <td><input type="text" name="edit_surface" value="<?php echo htmlspecialchars($row['SURFACE']); ?>"></td>
                        <td><?php selectZone('edit_zone_enclos', $row['ZONE_LIBELLE']); ?></td>
                        <td>
                            <input type="submit" name="modifier_enclos" value="Valider">
                            <?php btnAnnuler(); ?>
                        </td>
                    </form>
                </tr>
            <?php else: ?>
                <!-- MODE NORMAL -->
                <tr>
                    <td><?php echo htmlspecialchars($row['ID_ENCLOS']); ?></td>
                    <td><?php echo htmlspecialchars($row['LATITUDE']); ?></td>
                    <td><?php echo htmlspecialchars($row['LONGITUDE']); ?></td>
                    <td><?php echo htmlspecialchars($row['SURFACE']); ?></td>
                    <td><?php echo htmlspecialchars($row['ZONE_LIBELLE']); ?></td>
                    <td>
                        <?php btnModifier('edit_enclos', $row['ID_ENCLOS']); ?>
                        <?php btnSupprimer('supprimer_id_enclos', $row['ID_ENCLOS']); ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>

        <!-- LIGNE AJOUT -->
        <tr>
            <form method="post">
                <td><input type="text" name="id_enclos"></td>
                <td><input type="text" name="latitude"></td>
                <td><input type="text" name="longitude"></td>
                <td><input type="text" name="surface"></td>
                <td><?php selectZone('zone_enclos'); ?></td>
                <td><input type="submit" name="ajouter_enclos" value="Ajouter"></td>
            </form>
        </tr>
    </table>

    <br><br>
<?php endif; ?>

    <!-- ===================== BOUTIQUES ===================== -->
<?php if ($tableBoutiques): ?>
    <h2>Gestion des boutiques</h2>
    <table border="1">
        <tr>
            <th>ID_BOUTIQUE</th><th>NOM_BOUTIQUE</th><th>TYPE_BOUTIQUE</th>
            <th>RESPONSABLE</th><th>ZONE</th><th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requeteBoutique)): ?>
            <?php if ($editBoutique == $row['ID_BOUTIQUE']): ?>
                <!-- MODE ÉDITION -->
                <tr>
                    <form method="post">
                        <input type="hidden" name="edit_id_boutique" value="<?php echo $row['ID_BOUTIQUE']; ?>">
                        <td><?php echo htmlspecialchars($row['ID_BOUTIQUE']); ?></td>
                        <td><input type="text" name="edit_nom_boutique" value="<?php echo htmlspecialchars($row['NOM_BOUTIQUE']); ?>"></td>
                        <td><input type="text" name="edit_type_boutique" value="<?php echo htmlspecialchars($row['TYPE_BOUTIQUE']); ?>"></td>
                        <td><input type="text" name="edit_responsable_boutique" value="<?php echo htmlspecialchars($row['RESPONSABLE']); ?>"></td>
                        <td><?php selectZone('edit_zone_boutique', $row['ZONE_LIBELLE']); ?></td>
                        <td>
                            <input type="submit" name="modifier_boutique" value="Valider">
                            <?php btnAnnuler(); ?>
                        </td>
                    </form>
                </tr>
            <?php else: ?>
                <!-- MODE NORMAL -->
                <tr>
                    <td><?php echo htmlspecialchars($row['ID_BOUTIQUE']); ?></td>
                    <td><?php echo htmlspecialchars($row['NOM_BOUTIQUE']); ?></td>
                    <td><?php echo htmlspecialchars($row['TYPE_BOUTIQUE']); ?></td>
                    <td><?php echo htmlspecialchars($row['RESPONSABLE']); ?></td>
                    <td><?php echo htmlspecialchars($row['ZONE_LIBELLE']); ?></td>
                    <td>
                        <?php btnModifier('edit_boutique', $row['ID_BOUTIQUE']); ?>
                        <?php btnSupprimer('supprimer_id_boutique', $row['ID_BOUTIQUE']); ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>

        <!-- LIGNE AJOUT -->
        <tr>
            <form method="post">
                <td><input type="text" name="id_boutique"></td>
                <td><input type="text" name="nom_boutique"></td>
                <td><input type="text" name="type_boutique"></td>
                <td><input type="text" name="responsable_boutique"></td>
                <td><?php selectZone('zone_boutique'); ?></td>
                <td><input type="submit" name="ajouter_boutique" value="Ajouter"></td>
            </form>
        </tr>
    </table>

    <br><br>
<?php endif; ?>

<!-- ===================== ANIMAUX ===================== -->
<?php if ($tableAnimaux): ?>
    <h2>Gestion des animaux</h2>
    <table border="1">
        <tr>
            <th>RFID</th><th>NOM_ANIMAL</th><th>DATE_NAISSANCE</th>
            <th>POIDS</th><th>ID_ENCLOS</th><th>ESPECE</th><th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requeteAnimal)): ?>
            <?php if ($editAnimal == $row['RFID']): ?>
                <!-- MODE ÉDITION -->
                <tr>
                    <form method="post">
                        <input type="hidden" name="edit_rfid" value="<?php echo $row['RFID']; ?>">
                        <td><?php echo htmlspecialchars($row['RFID']); ?></td>
                        <td><input type="text" name="edit_nom_animal"       value="<?php echo htmlspecialchars($row['NOM_ANIMAL']); ?>"></td>
                        <td><input type="date" name="edit_date_naissance"   value="<?php echo htmlspecialchars($row['DATE_NAISSANCE']); ?>"></td>
                        <td><input type="text" name="edit_poids"            value="<?php echo htmlspecialchars($row['POIDS']); ?>"></td>
                        <td><input type="text" name="edit_id_enclos_animal" value="<?php echo htmlspecialchars($row['ID_ENCLOS']); ?>"></td>
                        <td><input type="text" name="edit_espece_animal"    value="<?php echo htmlspecialchars($row['NOM_USUEL']); ?>"></td>
                        <td>
                            <input type="submit" name="modifier_animal" value="Valider">
                            <?php btnAnnuler(); ?>
                        </td>
                    </form>
                </tr>
            <?php else: ?>
                <!-- MODE NORMAL -->
                <tr>
                    <td><?php echo htmlspecialchars($row['RFID']); ?></td>
                    <td><?php echo htmlspecialchars($row['NOM_ANIMAL']); ?></td>
                    <td><?php echo htmlspecialchars($row['DATE_NAISSANCE']); ?></td>
                    <td><?php echo htmlspecialchars($row['POIDS']); ?></td>
                    <td><?php echo htmlspecialchars($row['ID_ENCLOS']); ?></td>
                    <td><?php echo htmlspecialchars($row['NOM_USUEL']); ?></td>
                    <td>
                        <?php btnModifier('edit_animal', $row['RFID']); ?>
                        <?php btnSupprimer('supprimer_rfid', $row['RFID']); ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>

        <!-- LIGNE AJOUT -->
        <tr>
            <form method="post">
                <td><input type="text" name="rfid"></td>
                <td><input type="text" name="nom_animal"></td>
                <td><input type="date" name="date_naissance"></td>
                <td><input type="text" name="poids"></td>
                <td><input type="text" name="id_enclos_animal"></td>
                <td><input type="text" name="espece_animal"></td>
                <td><input type="submit" name="ajouter_animal" value="Ajouter"></td>
            </form>
        </tr>
    </table>
<?php endif; ?>

</body>
</html>