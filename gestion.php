<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";

    /* ====================================================================================================================================================== */
    /* ===================================================================== PERSONNEL ====================================================================== */
    /* ====================================================================================================================================================== */

    //ARCHIVER
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_personnel'])) {
        $id = $_POST['supprimer_id_personnel'];

        //Récupération de la fonction du personnel à archiver
        $rowFonction = fetchOne($conn,
            "SELECT fonction 
            FROM Vue_Personnel
            WHERE id_personnel = :id",
            [':id' => $id]
        );

        //Récupération de la date de début de contrat du personnel à archiver
        $rowContrat = fetchOne($conn,
            "SELECT TO_CHAR(date_debut, 'YYYY-MM-DD') AS date_debut
            FROM Contrat
            WHERE id_personnel = :id AND date_fin IS NULL",
            [':id' => $id]
        );
        $fonction = $rowFonction['FONCTION'];

        if ($_POST['date_fin_archivage'] <= $rowContrat['DATE_DEBUT']) {
            $message = "La date de fin doit être postérieure à la date de début (".$rowContrat['DATE_DEBUT'].")";
        } else {
            $dateFin = $_POST['date_fin_archivage'];

            //Gère l'archivage selon la fonction du personnel à archiver
            $resultat = gererDepartFonction($conn, $id, $fonction);
            if ($resultat !== true) {
                $message = $resultat;
            } else {
                archiverPersonnel($conn, $id, $dateFin);
            }
        }
    }

    //AJOUT
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_personnel'])) {
        $fields = ['id_personnel', 'prenom_personnel', 'nom_personnel', 'mot_de_passe', 'id_connexion', 'zone_personnel', 'id_contrat', 'salaire', 'date_debut', 'fonction']; //Champs POST à vérifier
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs du personnel.";
        } else {
            $id_zone = getIdZone($conn, $_POST['zone_personnel']);
            $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
            //Ajout des données dans Personnel
            execQuery($conn,
                "INSERT INTO Personnel VALUES (:id_personnel, :nom_personnel, :prenom_personnel, :mot_de_passe, :id_connexion, :id_zone, 'N')",
                [":id_personnel" => $_POST['id_personnel'],":nom_personnel" => $_POST['nom_personnel'], ":prenom_personnel" => $_POST['prenom_personnel'],":mot_de_passe" => $mot_de_passe, ":id_connexion" => $_POST['id_connexion'],":id_zone" => $id_zone]
            );
            //On cherche l'id_fonction correspondant à la fonction donnée
            $rowFonction = fetchOne($conn,
                "SELECT id_fonction FROM Fonction WHERE fonction = :fonction",
                [":fonction" => $_POST['fonction']]
            );
            //Ajout des données dans Contrat
            execQuery($conn,
                "INSERT INTO Contrat VALUES (:id_contrat, :salaire, TO_DATE(:date_debut,'YYYY-MM-DD'), NULL, :id_fonction, :id_personnel)",
                [":id_contrat" => $_POST['id_contrat'],":salaire" => $_POST['salaire'], ":date_debut" => $_POST['date_debut'], ":id_fonction" => $rowFonction['ID_FONCTION'], ":id_personnel" => $_POST['id_personnel']]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    //MODIFICATION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_personnel'])) {
        $fields = ['edit_id_personnel', 'edit_prenom_personnel', 'edit_nom_personnel', 'edit_id_connexion', 'edit_zone_personnel', 'edit_salaire', 'edit_date_debut', 'edit_fonction'];
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs pour la modification.";
        } else {
            $id = $_POST['edit_id_personnel'];
            $nouvelleFonction = $_POST['edit_fonction'];

            //Récupération de la fonction actuelle
            $rowFonctionActuelle = fetchOne($conn,
                "SELECT fonction FROM Vue_Personnel WHERE id_personnel = :id",
                [':id' => $id]
            );
            $fonctionActuelle = $rowFonctionActuelle['FONCTION'];

            $peutModifier = true;

            if ($fonctionActuelle !== $nouvelleFonction) {
                $resultat = gererDepartFonction($conn, $id, $fonctionActuelle); //On enlève la personne de son métier actuel
                if ($resultat !== true) {
                    $message = $resultat;
                    $peutModifier = false;
                }
            }

            if ($peutModifier) {
                $id_zone = getIdZone($conn, $_POST['edit_zone_personnel']);

                //Modification du personnel avec les nouvelles valeurs
                execQuery($conn,
                    "UPDATE Personnel SET prenom_personnel = :prenom, nom_personnel = :nom, id_connexion = :id_connexion, id_zone = :id_zone
                    WHERE id_personnel = :id_personnel",
                    [":prenom" => $_POST['edit_prenom_personnel'], ":nom" => $_POST['edit_nom_personnel'], ":id_connexion" => $_POST['edit_id_connexion'], ":id_zone" => $id_zone, ":id_personnel" => $id]
                );

                $rowFonction = fetchOne($conn,
                    "SELECT id_fonction FROM Fonction WHERE fonction = :fonction",
                    [":fonction" => $nouvelleFonction]
                );

                execQuery($conn,
                    "UPDATE Contrat SET salaire = :salaire, date_debut = TO_DATE(:date_debut,'YYYY-MM-DD'), id_fonction = :id_fonction
                    WHERE id_personnel = :id_personnel AND date_fin IS NULL",
                    [":salaire" => $_POST['edit_salaire'], ":date_debut" => $_POST['edit_date_debut'], ":id_fonction" => $rowFonction['ID_FONCTION'], ":id_personnel" => $id]
                );

                oci_commit($conn);
                redirectSelf();
            }
        }
    }

    /* ====================================================================================================================================================== */
    /* ====================================================================== ENCLOS ======================================================================== */
    /* ====================================================================================================================================================== */

    //SUPPRESSION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_enclos'])) {
        $id = $_POST['supprimer_id_enclos'];

        //Suppression en cascade de tous les animaux de l'enclos
        $reqAnimaux = execQuery($conn, 
            "SELECT RFID 
            FROM Vue_Animal 
            WHERE id_enclos = :id",
            [':id' => $id]
        );
        while ($a = oci_fetch_assoc($reqAnimaux)) {
            deleteAnimal($conn, $a['RFID']);
        }

        //Suppression des réparations et de ce qui en dépend de l'enclos
        $reqRep = execQuery($conn,
            "SELECT id_reparation 
            FROM Reparation 
            WHERE id_enclos = :id",
            [':id' => $id]
        );
        while ($r = oci_fetch_assoc($reqRep)) {
            deleteWhere($conn, 'Participe', 'id_reparation', $r['ID_REPARATION']);
            deleteWhere($conn, 'Entretient', 'id_reparation', $r['ID_REPARATION']);
        }
        deleteWhere($conn, 'Reparation', 'id_enclos', $id);

        //Suppressions simples dans les autres tables
        deleteWhere($conn, 'Possede', 'id_enclos', $id);
        deleteWhere($conn, 'Enclos', 'id_enclos', $id);
        oci_commit($conn);
        redirectSelf();
    }

    //AJOUT
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_enclos'])) {
        $fields = ['id_enclos', 'latitude', 'longitude', 'surface', 'zone_enclos']; //Champs POST à vérifier
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs de l'enclos.";
        } else {
            $id_zone = getIdZone($conn, $_POST['zone_enclos']);
            //Ajout des données dans Enclos
            execQuery($conn,
                "INSERT INTO Enclos VALUES (:id_enclos, :latitude, :longitude, :surface, :id_zone)",
                [":id_enclos" => $_POST['id_enclos'],":latitude" => $_POST['latitude'], ":longitude" => $_POST['longitude'],":surface" => $_POST['surface'],":id_zone" => $id_zone]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    //MODIFICATION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_enclos'])) {
        $fields = ['edit_id_enclos', 'edit_latitude', 'edit_longitude', 'edit_surface', 'edit_zone_enclos']; //Champs POST à vérifier
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs pour la modification.";
        } else {
            $id_zone = getIdZone($conn, $_POST['edit_zone_enclos']);
            //Modification des données dans Enclos
            execQuery($conn,
                "UPDATE Enclos SET latitude = :latitude, longitude = :longitude, surface = :surface, id_zone = :id_zone 
                WHERE id_enclos = :id_enclos",
                [":latitude" => $_POST['edit_latitude'],":longitude" => $_POST['edit_longitude'], ":surface" => $_POST['edit_surface'],":id_zone" => $id_zone, ":id_enclos" => $_POST['edit_id_enclos']]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    /* ====================================================================================================================================================== */
    /* ===================================================================== BOUTIQUE ======================================================================= */
    /* ====================================================================================================================================================== */

    //SUPPRESSION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_boutique'])) {
        $id = $_POST['supprimer_id_boutique'];

        //Vérifier s'il y a des employés actifs travaillant dans cette boutique
        $employes = fetchAllRows($conn,
            "SELECT P.id_personnel, P.prenom_personnel, P.nom_personnel
            FROM Travaille T, Personnel P
            WHERE T.id_boutique = :id
            AND T.id_personnel = P.id_personnel
            AND P.archiver_personnel = 'N'",
            [':id' => $id]
        );

        if (!empty($employes)) {
            $noms = array_map(function($e) {
                return $e['PRENOM_PERSONNEL'].' '.$e['NOM_PERSONNEL'];
            }, $employes);
            $message = "Impossible de supprimer cette boutique : ".count($employes)." employé(s) y travaillent encore (".implode(', ', $noms)."). Veuillez d'abord les archiver.";
        } else {
            deleteWhere($conn, 'Travaille', 'id_boutique', $id); //Suppression dans Travaille
            deleteWhere($conn, 'Chiffre_affaire', 'id_boutique', $id); //Suppression dans Chiffre_Affaire
            deleteWhere($conn, 'Boutique', 'id_boutique', $id); //Suppression dans Boutique
            oci_commit($conn);
            redirectSelf();
        }
    }

    //AJOUT
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_boutique'])) {
        $fields = ['id_boutique', 'nom_boutique', 'type_boutique', 'responsable_boutique', 'zone_boutique']; //Champs POST à vérifier
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs de la boutique.";
        } else {
            //On cherche l'id_personnel correspondant au responsable de la boutique donnée
            $rowPers = fetchOne($conn,
                "SELECT id_personnel FROM Vue_Personnel WHERE prenom_personnel || ' ' || nom_personnel = :responsable",
                [":responsable" => $_POST['responsable_boutique']]
            );
            $id_zone = getIdZone($conn, $_POST['zone_boutique']);
            //Ajout des données dans Boutique
            execQuery($conn,
                "INSERT INTO Boutique VALUES (:id_boutique, :nom_boutique, :type_boutique, :id_personnel, :id_zone)",
                [":id_boutique" => $_POST['id_boutique'],":nom_boutique" => $_POST['nom_boutique'], ":type_boutique" => $_POST['type_boutique'],":id_personnel" => $rowPers['ID_PERSONNEL'], ":id_zone" => $id_zone]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    //MODIFICATION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_boutique'])) {
        $fields = ['edit_id_boutique', 'edit_nom_boutique', 'edit_type_boutique', 'edit_responsable_boutique', 'edit_zone_boutique']; //Champs POST à vérifier
        if (!postFieldsFilled($fields)) { 
            $message = "Veuillez remplir tous les champs pour la modification.";
        } else {
            //On cherche l'id_personnel correspondant au responsable de la boutique donnée
            $rowPers = fetchOne($conn,
                "SELECT id_personnel FROM Vue_Personnel WHERE prenom_personnel || ' ' || nom_personnel = :responsable",
                [":responsable" => $_POST['edit_responsable_boutique']]
            );
            $id_zone = getIdZone($conn, $_POST['edit_zone_boutique']); //Récupération de l'id_zone
            //Modification des données dans Boutique
            execQuery($conn,
                "UPDATE Boutique SET nom_boutique = :nom, type_boutique = :type, id_personnel = :id_personnel, id_zone = :id_zone 
                WHERE id_boutique = :id_boutique",
                [":nom" => $_POST['edit_nom_boutique'],":type" => $_POST['edit_type_boutique'], ":id_personnel" => $rowPers['ID_PERSONNEL'],":id_zone" => $id_zone, ":id_boutique" => $_POST['edit_id_boutique']]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    /* ====================================================================================================================================================== */
    /* ====================================================================== ANIMAL ======================================================================== */
    /* ====================================================================================================================================================== */

    //SUPPRESSION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_rfid'])) {
        $rfid = $_POST['supprimer_rfid'];
        deleteAnimal($conn, $rfid); //Suppression en cascade via deleteAnimal
        oci_commit($conn);
        redirectSelf();
    }

    //AJOUT
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_animal'])) {
        $fields = ['rfid', 'nom_animal', 'date_naissance', 'poids', 'id_enclos_animal', 'espece_animal']; //Champs POST à vérifier
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
                [":rfid" => $_POST['rfid'],":nom_animal" => $_POST['nom_animal'], ":date_naissance" => $_POST['date_naissance'],":poids" => $_POST['poids'], ":id_enclos" => $_POST['id_enclos_animal'],":nom_latin" => $rowEspece['NOM_LATIN']]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    //MODIFICATION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_animal'])) {
        $fields = ['edit_rfid', 'edit_nom_animal', 'edit_date_naissance', 'edit_poids', 'edit_id_enclos_animal', 'edit_espece_animal']; //Champs POST à vérifier
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
                "UPDATE Animal SET nom_animal = :nom, date_naissance=TO_DATE(:dn,'YYYY-MM-DD'), poids = :poids, id_enclos = :id_enclos, nom_latin = :nom_latin 
                WHERE RFID = :rfid",
                [":nom" => $_POST['edit_nom_animal'],":dn" => $_POST['edit_date_naissance'], ":poids" => $_POST['edit_poids'],":id_enclos" => $_POST['edit_id_enclos_animal'], ":nom_latin" => $rowEspece['NOM_LATIN'],":rfid" => $_POST['edit_rfid']]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    /* ====================================================================================================================================================== */
    /* ====================================================================== ESPECE ======================================================================== */
    /* ====================================================================================================================================================== */

    //SUPPRESSION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_nom_latin'])) {
        $nl = $_POST['supprimer_nom_latin'];
        deleteWhere($conn, 'Specialiser', 'nom_latin', $nl); //Suppression dans Specialiser
        deleteWhere($conn, 'Cohabiter', 'nom_latin_est_cohabiter_par',  $nl); //Suppression dans Cohabiter
        deleteWhere($conn, 'Cohabiter', 'nom_latin_cohabite_avec', $nl); //Suppression dans Cohabiter

        //Les animaux de cette espèce doivent être supprimés en cascade
        $animaux = execQuery($conn,
            "SELECT RFID 
            FROM Vue_Animal 
            WHERE nom_latin = :nl",
            [':nl' => $nl]
        );
        while ($a = oci_fetch_assoc($animaux)) {
            deleteAnimal($conn, $a['RFID']);
        }

        deleteWhere($conn, 'Espece', 'nom_latin', $nl); //Suppression dans Espece
        oci_commit($conn);
        redirectSelf();
    }

    //AJOUT
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_espece'])) {
        $fields = ['nom_latin', 'nom_usuel', 'menace']; //Champs POST à vérifier
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs de l'espèce.";
        } else {
            //Ajout des données dans Espece
            execQuery($conn,
                "INSERT INTO Espece VALUES (:nom_latin, :nom_usuel, :menace)",
                [":nom_latin" => $_POST['nom_latin'], ":nom_usuel" => $_POST['nom_usuel'], ":menace" => $_POST['menace']]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    //MODIFICATION
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_espece'])) {
        $fields = ['edit_nom_latin', 'edit_nom_usuel', 'edit_menace']; //Champs POST à vérifier
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs pour la modification.";
        } else {
            //Modification des données dans Espece
            execQuery($conn,
                "UPDATE Espece SET nom_usuel = :nom_usuel, menace = :menace 
                WHERE nom_latin = :nom_latin",
                [":nom_usuel" => $_POST['edit_nom_usuel'], ":menace" => $_POST['edit_menace'], ":nom_latin" => $_POST['edit_nom_latin']]
            );
            oci_commit($conn);
            redirectSelf();
        }
    }

    /* ====================================================================================================================================================== */
    /* ============================================================= RÉCUPÉRATION DE DONNÉES ================================================================ */
    /* ====================================================================================================================================================== */

    //Récupère les données de Personnel
    $requetePersonnel = execQuery($conn,
        "SELECT id_personnel, prenom_personnel, nom_personnel, id_connexion, libelle_zone, id_contrat, salaire, TO_CHAR(date_debut,'YYYY-MM-DD') AS date_debut, fonction
        FROM Vue_Personnel
        WHERE archiver_personnel = 'N'
        ORDER BY id_personnel",
        []
    );

    //Récupère les données de Enclos
    $requeteEnclos = execQuery($conn,
        "SELECT id_enclos, latitude, longitude, surface, libelle_zone
        FROM Vue_Enclos
        ORDER BY id_enclos",
        []
    );

    //Récupère les données de Boutique
    $requeteBoutique = execQuery($conn,
        "SELECT id_boutique, nom_boutique, type_boutique, prenom_personnel || ' ' || nom_personnel AS responsable, libelle_zone
        FROM Vue_Boutique
        ORDER BY id_boutique",
        []
    );

    //Récupère les données de Animal
    $requeteAnimal = execQuery($conn,
        "SELECT RFID, nom_animal, TO_CHAR(date_naissance,'YYYY-MM-DD') AS date_naissance, poids, id_enclos, nom_usuel
        FROM Vue_Animal
        ORDER BY RFID",
        []
    );

    //Récupère les données de Espece
    $requeteEspece = execQuery($conn,
        "SELECT nom_latin, nom_usuel, menace 
        FROM Espece
        ORDER BY nom_usuel",
        []
    );

    //Calcul des ids
    $nextIdPersonnel = getNextId($conn, "Personnel", "id_personnel");
    $nextIdContrat = getNextId($conn, "Contrat", "id_contrat");
    $nextIdEnclos = getNextId($conn, "Enclos", "id_enclos");
    $nextIdBoutique = getNextId($conn, "Boutique", "id_boutique");
    $nextRfid  = getNextId($conn, "Animal", "RFID");

    $confirmerArchivage = $_GET['confirmer_archivage'] ?? null;

    //ID en cours d'édition
    $editPersonnel = $_GET['edit_personnel'] ?? null;
    $editEnclos = $_GET['edit_enclos'] ?? null;
    $editBoutique  = $_GET['edit_boutique'] ?? null;
    $editAnimal = $_GET['edit_animal'] ?? null;
    $editEspece = $_GET['edit_espece'] ?? null;

    //Tables à afficher
    $tablePersonnel = $_GET['tablePersonnel'] ?? 0;
    $tableEnclos = $_GET['tableEnclos'] ?? 0;
    $tableBoutiques = $_GET['tableBoutiques'] ?? 0;
    $tableAnimaux = $_GET['tableAnimaux'] ?? 0;
    $tableEspeces = $_GET['tableEspeces'] ?? 0;
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
?>

<!-- ===================== PERSONNEL ===================== -->
<?php if ($tablePersonnel): ?>
    <h2>Gestion du personnel</h2>

    <!-- FORMULAIRE D'ARCHIVAGE -->
    <?php if ($confirmerArchivage): ?>

        <?php
            $rowAArchiver = fetchOne($conn,
                "SELECT prenom_personnel, nom_personnel, TO_CHAR(date_fin,'YYYY-MM-DD') AS date_fin
                FROM Vue_Personnel
                WHERE id_personnel = :id",
                [':id' => $confirmerArchivage]
            );
        ?>

        <p>
            Vous allez archiver <strong><?php echo htmlspecialchars($rowAArchiver['PRENOM_PERSONNEL'].' '.$rowAArchiver['NOM_PERSONNEL']) ?></strong>.
            <?php if ($rowAArchiver['DATE_FIN']): ?>
                Date de fin actuelle : <?php echo htmlspecialchars($rowAArchiver['DATE_FIN']) ?> — veuillez saisir la nouvelle date de licenciement.
            <?php else: ?>
                Veuillez saisir la date de fin de contrat.
            <?php endif; ?>
        </p>
        
        <form method="post">
            <?php hiddenTables(); ?>
            <input type="hidden" name="supprimer_id_personnel" value="<?php echo htmlspecialchars($confirmerArchivage) ?>">
            <label>Date de fin de contrat :
                <input type="date" name="date_fin_archivage" required>
            </label>
            <input type="submit" value="Confirmer l'archivage">
            <?php btnAnnuler(); ?>
        </form>
    <?php endif; ?>

    <table>
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
                <tr id="edit-<?php echo htmlspecialchars($row['ID_PERSONNEL']); ?>">
                    <form method="post">
                        <input type="hidden" name="edit_id_personnel" value="<?php echo $row['ID_PERSONNEL']; ?>">
                        <?php hiddenTables(); ?>
                        <td><?php echo htmlspecialchars($row['ID_PERSONNEL']); ?></td>
                        <td><input type="text" name="edit_prenom_personnel" value="<?php echo htmlspecialchars($row['PRENOM_PERSONNEL']); ?>"></td>
                        <td><input type="text" name="edit_nom_personnel"    value="<?php echo htmlspecialchars($row['NOM_PERSONNEL']); ?>"></td>
                        <td><input type="text" name="edit_id_connexion"     value="<?php echo htmlspecialchars($row['ID_CONNEXION']); ?>"></td>
                        <td><?php selectZone('edit_zone_personnel', $row['LIBELLE_ZONE']); ?></td>
                        <td><?php echo htmlspecialchars($row['ID_CONTRAT']); ?></td>
                        <td><input type="number" name="edit_salaire"    value="<?php echo htmlspecialchars($row['SALAIRE']); ?>"></td>
                        <td><input type="date" name="edit_date_debut" value="<?php echo htmlspecialchars($row['DATE_DEBUT']); ?>"></td>
                        <td><?php selectFonction($conn, 'edit_fonction', $row['FONCTION']); ?></td>
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
                    <td><?php echo htmlspecialchars($row['LIBELLE_ZONE']); ?></td>
                    <td><?php echo htmlspecialchars($row['ID_CONTRAT']); ?></td>
                    <td><?php echo htmlspecialchars($row['SALAIRE']); ?></td>
                    <td><?php echo htmlspecialchars($row['DATE_DEBUT']); ?></td>
                    <td><?php echo htmlspecialchars($row['FONCTION']); ?></td>
                    <td>************</td>
                    <td>
                        <?php btnModifier('edit_personnel', $row['ID_PERSONNEL']); ?>
                        <?php btnArchiver($row['ID_PERSONNEL']); ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>

        <!-- LIGNE AJOUT -->
        <tr>
            <form method="post">
                <?php hiddenTables(); ?>
                <td><input type="text" name="id_personnel" value="<?php echo $nextIdPersonnel; ?>" readonly></td>
                <td><input type="text" name="prenom_personnel"></td>
                <td><input type="text" name="nom_personnel"></td>
                <td><input type="text" name="id_connexion"></td>
                <td><?php selectZone('zone_personnel'); ?></td>
                <td><input type="text" name="id_contrat" value="<?php echo $nextIdContrat; ?>" readonly></td>
                <td><input type="number" name="salaire"></td>
                <td><input type="date" name="date_debut"></td>
                <td><?php selectFonction($conn, 'fonction'); ?></td>
                <td><input type="text" name="mot_de_passe"></td>
                <td><input type="submit" name="ajouter_personnel" value="Ajouter"></td>
            </form>
        </tr>
    </table>
<?php endif; ?>

<br><br>

<!-- ===================== ENCLOS ===================== -->
 <?php if ($tableEnclos): ?>
    <h2>Gestion des enclos</h2>
    <table>
        <tr>
            <th>ID_ENCLOS</th><th>LATITUDE</th><th>LONGITUDE</th><th>SURFACE</th><th>ZONE</th><th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requeteEnclos)): ?>
            <?php if ($editEnclos == $row['ID_ENCLOS']): ?>
                <!-- MODE ÉDITION -->
                 <tr id="edit-<?php echo htmlspecialchars($row['ID_ENCLOS']); ?>">
                    <form method="post">
                        <input type="hidden" name="edit_id_enclos" value="<?php echo $row['ID_ENCLOS']; ?>">
                        <?php hiddenTables(); ?>
                        <td><?php echo htmlspecialchars($row['ID_ENCLOS']); ?></td>
                        <td><input type="text" name="edit_latitude"  value="<?php echo htmlspecialchars($row['LATITUDE']); ?>"></td>
                        <td><input type="text" name="edit_longitude" value="<?php echo htmlspecialchars($row['LONGITUDE']); ?>"></td>
                        <td><input type="number" name="edit_surface"   value="<?php echo htmlspecialchars($row['SURFACE']); ?>"></td>
                        <td><?php selectZone('edit_zone_enclos', $row['LIBELLE_ZONE']); ?></td>
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
                    <td><?php echo htmlspecialchars($row['LIBELLE_ZONE']); ?></td>
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
                <?php hiddenTables(); ?>
                <td><input type="text" name="id_enclos" value="<?php echo $nextIdEnclos; ?>" readonly></td>
                <td><input type="text" name="latitude"></td>
                <td><input type="text" name="longitude"></td>
                <td><input type="number" name="surface"></td>
                <td><?php selectZone('zone_enclos'); ?></td>
                <td><input type="submit" name="ajouter_enclos" value="Ajouter"></td>
            </form>
        </tr>
    </table>
<?php endif; ?>

<br><br>

<!-- ===================== BOUTIQUES ===================== -->
<?php if ($tableBoutiques): ?>
    <h2>Gestion des boutiques</h2>
    <table>
        <tr>
            <th>ID_BOUTIQUE</th><th>NOM_BOUTIQUE</th><th>TYPE_BOUTIQUE</th>
            <th>RESPONSABLE</th><th>ZONE</th><th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requeteBoutique)): ?>
            <?php if ($editBoutique == $row['ID_BOUTIQUE']): ?>
                <!-- MODE ÉDITION -->
                <tr id="edit-<?php echo htmlspecialchars($row['ID_BOUTIQUE']); ?>">
                    <form method="post">
                        <input type="hidden" name="edit_id_boutique" value="<?php echo $row['ID_BOUTIQUE']; ?>">
                        <?php hiddenTables(); ?>
                        <td><?php echo htmlspecialchars($row['ID_BOUTIQUE']); ?></td>
                        <td><input type="text" name="edit_nom_boutique" value="<?php echo htmlspecialchars($row['NOM_BOUTIQUE']); ?>"></td>
                        <td><input type="text" name="edit_type_boutique" value="<?php echo htmlspecialchars($row['TYPE_BOUTIQUE']); ?>"></td>
                        <td><?php selectResponsableBoutique($conn, 'edit_responsable_boutique', $row['RESPONSABLE']); ?></td>
                        <td><?php selectZone('edit_zone_boutique', $row['LIBELLE_ZONE']); ?></td>
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
                    <td><?php echo htmlspecialchars($row['LIBELLE_ZONE']); ?></td>
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
                <?php hiddenTables(); ?>
                <td><input type="text" name="id_boutique" value="<?php echo $nextIdBoutique; ?>" readonly></td>
                <td><input type="text" name="nom_boutique"></td>
                <td><input type="text" name="type_boutique"></td>
                <td><?php selectResponsableBoutique($conn, 'responsable_boutique'); ?></td>
                <td><?php selectZone('zone_boutique'); ?></td>
                <td><input type="submit" name="ajouter_boutique" value="Ajouter"></td>
            </form>
        </tr>
    </table>
<?php endif; ?>

<br><br>

<!-- ===================== ANIMAUX ===================== -->
<?php if ($tableAnimaux): ?>
    <h2>Gestion des animaux</h2>
    <table>
        <tr>
            <th>RFID</th><th>NOM_ANIMAL</th><th>DATE_NAISSANCE</th>
            <th>POIDS</th><th>ID_ENCLOS</th><th>ESPECE</th><th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requeteAnimal)): ?>
            <?php if ($editAnimal == $row['RFID']): ?>
                <!-- MODE ÉDITION -->
                <tr id="edit-<?php echo htmlspecialchars($row['RFID']); ?>">
                    <form method="post">
                        <input type="hidden" name="edit_rfid" value="<?php echo $row['RFID']; ?>">
                        <?php hiddenTables(); ?>
                        <td><?php echo htmlspecialchars($row['RFID']); ?></td>
                        <td><input type="text" name="edit_nom_animal"       value="<?php echo htmlspecialchars($row['NOM_ANIMAL']); ?>"></td>
                        <td><input type="date" name="edit_date_naissance"   value="<?php echo htmlspecialchars($row['DATE_NAISSANCE']); ?>"></td>
                        <td><input type="text" name="edit_poids"            value="<?php echo htmlspecialchars($row['POIDS']); ?>"></td>
                        <td><input type="text" name="edit_id_enclos_animal" value="<?php echo htmlspecialchars($row['ID_ENCLOS']); ?>"></td>
                        <td><?php selectEspece($conn, 'edit_espece_animal', $row['NOM_USUEL']); ?></td>
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
                <?php hiddenTables(); ?>
                <td><input type="text" name="rfid" value="<?php echo $nextRfid; ?>" readonly></td>
                <td><input type="text" name="nom_animal"></td>
                <td><input type="date" name="date_naissance"></td>
                <td><input type="text" name="poids"></td>
                <td><input type="text" name="id_enclos_animal"></td>
                <td><?php selectEspece($conn, 'espece_animal'); ?></td>
                <td><input type="submit" name="ajouter_animal" value="Ajouter"></td>
            </form>
        </tr>
    </table>
<?php endif; ?>

<br><br>

<!-- ===================== ESPECES ===================== -->
<?php if ($tableEspeces): ?>
    <h2>Gestion des espèces</h2>
    <table>
        <tr>
            <th>NOM_LATIN</th><th>NOM_USUEL</th><th>MENACÉE</th><th>ACTION</th>
        </tr>

        <?php while ($row = oci_fetch_assoc($requeteEspece)): ?>
            <?php if ($editEspece == $row['NOM_LATIN']): ?>
                <!-- MODE ÉDITION -->
                <tr id="edit-<?php echo htmlspecialchars($row['NOM_LATIN']); ?>">
                    <form method="post">
                        <?php hiddenTables(); ?>
                        <input type="hidden" name="edit_nom_latin" value="<?php echo htmlspecialchars($row['NOM_LATIN']); ?>">
                        <td><?php echo htmlspecialchars($row['NOM_LATIN']); ?></td>
                        <td><input type="text" name="edit_nom_usuel" value="<?php echo htmlspecialchars($row['NOM_USUEL']); ?>"></td>
                        <td>
                            <select name="edit_menace">
                                <option value="O"<?php echo $row['MENACE']==='O' ? ' selected' : ''; ?>>Oui</option>
                                <option value="N"<?php echo $row['MENACE']==='N' ? ' selected' : ''; ?>>Non</option>
                            </select>
                        </td>
                        <td>
                            <input type="submit" name="modifier_espece" value="Valider">
                            <?php btnAnnuler(); ?>
                        </td>
                    </form>
                </tr>
            <?php else: ?>
                <!-- MODE NORMAL -->
                <tr>
                    <td><?php echo htmlspecialchars($row['NOM_LATIN']); ?></td>
                    <td><?php echo htmlspecialchars($row['NOM_USUEL']); ?></td>
                    <td><?php echo $row['MENACE'] === 'O' ? 'Oui' : 'Non'; ?></td>
                    <td>
                        <?php btnModifier('edit_espece', $row['NOM_LATIN']); ?>
                        <?php btnSupprimer('supprimer_nom_latin', $row['NOM_LATIN']); ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>

        <!-- LIGNE AJOUT -->
        <tr>
            <form method="post">
                <?php hiddenTables(); ?>
                <td><input type="text" name="nom_latin"></td>
                <td><input type="text" name="nom_usuel"></td>
                <td>
                    <select name="menace">
                        <option value="O">Oui</option>
                        <option value="N">Non</option>
                    </select>
                </td>
                <td><input type="submit" name="ajouter_espece" value="Ajouter"></td>
            </form>
        </tr>
    </table>
<?php endif; ?>

<script>
    window.addEventListener('load', function () {
        const el = document.querySelector('tr[id^="edit-"]');
        if (el) {
            el.scrollIntoView({ behavior: 'instant', block: 'center' });
        }
    });
</script>

</body>
</html>