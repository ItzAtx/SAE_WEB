<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

include_once("myparam.inc.php");
$conn = oci_connect(MYUSER, MYPASS, MYHOST);

$message = "";

/* =========================
   SUPPRESSION PERSONNEL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_personnel'])) {
    $id_personnel_suppr = $_POST['supprimer_id_personnel'];

    $requeteP = oci_parse($conn,
        "DELETE FROM Contrat
         WHERE id_personnel = :id_personnel"
    );
    oci_bind_by_name($requeteP, ":id_personnel", $id_personnel_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Personnel
         WHERE id_personnel = :id_personnel"
    );
    oci_bind_by_name($requeteP, ":id_personnel", $id_personnel_suppr);
    oci_execute($requeteP);

    oci_commit($conn);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* =========================
   AJOUT PERSONNEL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_personnel'])) {
    if (
        !empty($_POST['id_personnel']) &&
        !empty($_POST['prenom_personnel']) &&
        !empty($_POST['nom_personnel']) &&
        !empty($_POST['mot_de_passe']) &&
        !empty($_POST['id_connexion']) &&
        !empty($_POST['zone_personnel']) &&
        !empty($_POST['id_contrat']) &&
        !empty($_POST['salaire']) &&
        !empty($_POST['date_debut']) &&
        !empty($_POST['fonction'])
    ) {
        $id_personnel = $_POST['id_personnel'];
        $prenom_personnel = $_POST['prenom_personnel'];
        $nom_personnel = $_POST['nom_personnel'];
        $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        $id_connexion = $_POST['id_connexion'];
        $zone_personnel = $_POST['zone_personnel'];
        $id_contrat = $_POST['id_contrat'];
        $salaire = $_POST['salaire'];
        $date_debut = $_POST['date_debut'];
        $fonction = $_POST['fonction'];

        $requeteP = oci_parse($conn,
            "SELECT id_zone
             FROM Zone_zoo
             WHERE libelle = :libelle"
        );
        oci_bind_by_name($requeteP, ":libelle", $zone_personnel);
        oci_execute($requeteP);
        $rowZone = oci_fetch_assoc($requeteP);

        if (!$rowZone) {
            $message = "Zone du personnel introuvable.";
        } else {
            $id_zone = $rowZone['ID_ZONE'];

            $requeteP = oci_parse($conn,
                "INSERT INTO Personnel
                 VALUES (:id_personnel, :nom_personnel, :prenom_personnel, :mot_de_passe, :id_connexion, :id_zone)"
            );
            oci_bind_by_name($requeteP, ":id_personnel", $id_personnel);
            oci_bind_by_name($requeteP, ":nom_personnel", $nom_personnel);
            oci_bind_by_name($requeteP, ":prenom_personnel", $prenom_personnel);
            oci_bind_by_name($requeteP, ":mot_de_passe", $mot_de_passe);
            oci_bind_by_name($requeteP, ":id_connexion", $id_connexion);
            oci_bind_by_name($requeteP, ":id_zone", $id_zone);
            oci_execute($requeteP);

            $requeteP = oci_parse($conn,
                "SELECT id_fonction
                 FROM Fonction
                 WHERE fonction = :fonction"
            );
            oci_bind_by_name($requeteP, ":fonction", $fonction);
            oci_execute($requeteP);
            $rowFonction = oci_fetch_assoc($requeteP);

            if (!$rowFonction) {
                oci_rollback($conn);
                $message = "Fonction introuvable.";
            } else {
                $id_fonction = $rowFonction['ID_FONCTION'];

                $requeteP = oci_parse($conn,
                    "INSERT INTO Contrat
                     VALUES (:id_contrat, :salaire, TO_DATE(:date_debut, 'YYYY-MM-DD'), NULL, :id_fonction, :id_personnel)"
                );
                oci_bind_by_name($requeteP, ":id_contrat", $id_contrat);
                oci_bind_by_name($requeteP, ":salaire", $salaire);
                oci_bind_by_name($requeteP, ":date_debut", $date_debut);
                oci_bind_by_name($requeteP, ":id_fonction", $id_fonction);
                oci_bind_by_name($requeteP, ":id_personnel", $id_personnel);
                oci_execute($requeteP);

                oci_commit($conn);
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs du personnel.";
    }
}

/* =========================
   SUPPRESSION ENCLOS
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_enclos'])) {
    $id_enclos_suppr = $_POST['supprimer_id_enclos'];

    $requeteP = oci_parse($conn,
        "DELETE FROM Possede
         WHERE id_enclos = :id_enclos"
    );
    oci_bind_by_name($requeteP, ":id_enclos", $id_enclos_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Reparation
         WHERE id_enclos = :id_enclos"
    );
    oci_bind_by_name($requeteP, ":id_enclos", $id_enclos_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Enclos
         WHERE id_enclos = :id_enclos"
    );
    oci_bind_by_name($requeteP, ":id_enclos", $id_enclos_suppr);
    oci_execute($requeteP);

    oci_commit($conn);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* =========================
   AJOUT ENCLOS
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_enclos'])) {
    if (
        !empty($_POST['id_enclos']) &&
        !empty($_POST['latitude']) &&
        !empty($_POST['longitude']) &&
        !empty($_POST['surface']) &&
        !empty($_POST['zone_enclos'])
    ) {
        $id_enclos = $_POST['id_enclos'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $surface = $_POST['surface'];
        $zone_enclos = $_POST['zone_enclos'];

        $requeteP = oci_parse($conn,
            "SELECT id_zone
             FROM Zone_zoo
             WHERE libelle = :libelle"
        );
        oci_bind_by_name($requeteP, ":libelle", $zone_enclos);
        oci_execute($requeteP);
        $rowZone = oci_fetch_assoc($requeteP);

        if (!$rowZone) {
            $message = "Zone de l'enclos introuvable.";
        } else {
            $id_zone = $rowZone['ID_ZONE'];

            $requeteP = oci_parse($conn,
                "INSERT INTO Enclos
                 VALUES (:id_enclos, :latitude, :longitude, :surface, :id_zone)"
            );
            oci_bind_by_name($requeteP, ":id_enclos", $id_enclos);
            oci_bind_by_name($requeteP, ":latitude", $latitude);
            oci_bind_by_name($requeteP, ":longitude", $longitude);
            oci_bind_by_name($requeteP, ":surface", $surface);
            oci_bind_by_name($requeteP, ":id_zone", $id_zone);
            oci_execute($requeteP);

            oci_commit($conn);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $message = "Veuillez remplir tous les champs de l'enclos.";
    }
}

/* =========================
   SUPPRESSION BOUTIQUE
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_boutique'])) {
    $id_boutique_suppr = $_POST['supprimer_id_boutique'];

    $requeteP = oci_parse($conn,
        "DELETE FROM Travaille
         WHERE id_boutique = :id_boutique"
    );
    oci_bind_by_name($requeteP, ":id_boutique", $id_boutique_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Chiffre_affaire
         WHERE id_boutique = :id_boutique"
    );
    oci_bind_by_name($requeteP, ":id_boutique", $id_boutique_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Boutique
         WHERE id_boutique = :id_boutique"
    );
    oci_bind_by_name($requeteP, ":id_boutique", $id_boutique_suppr);
    oci_execute($requeteP);

    oci_commit($conn);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* =========================
   AJOUT BOUTIQUE
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_boutique'])) {
    if (
        !empty($_POST['id_boutique']) &&
        !empty($_POST['nom_boutique']) &&
        !empty($_POST['type_boutique']) &&
        !empty($_POST['responsable_boutique']) &&
        !empty($_POST['zone_boutique'])
    ) {
        $id_boutique = $_POST['id_boutique'];
        $nom_boutique = $_POST['nom_boutique'];
        $type_boutique = $_POST['type_boutique'];
        $responsable_boutique = $_POST['responsable_boutique'];
        $zone_boutique = $_POST['zone_boutique'];

        $requeteP = oci_parse($conn,
            "SELECT id_personnel
             FROM Personnel
             WHERE prenom_personnel || ' ' || nom_personnel = :responsable"
        );
        oci_bind_by_name($requeteP, ":responsable", $responsable_boutique);
        oci_execute($requeteP);
        $rowPers = oci_fetch_assoc($requeteP);

        $requeteP = oci_parse($conn,
            "SELECT id_zone
             FROM Zone_zoo
             WHERE libelle = :libelle"
        );
        oci_bind_by_name($requeteP, ":libelle", $zone_boutique);
        oci_execute($requeteP);
        $rowZone = oci_fetch_assoc($requeteP);

        if (!$rowPers) {
            $message = "Responsable de boutique introuvable.";
        } elseif (!$rowZone) {
            $message = "Zone de boutique introuvable.";
        } else {
            $id_personnel_boutique = $rowPers['ID_PERSONNEL'];
            $id_zone_boutique = $rowZone['ID_ZONE'];

            $requeteP = oci_parse($conn,
                "INSERT INTO Boutique
                 VALUES (:id_boutique, :nom_boutique, :type_boutique, :id_personnel, :id_zone)"
            );
            oci_bind_by_name($requeteP, ":id_boutique", $id_boutique);
            oci_bind_by_name($requeteP, ":nom_boutique", $nom_boutique);
            oci_bind_by_name($requeteP, ":type_boutique", $type_boutique);
            oci_bind_by_name($requeteP, ":id_personnel", $id_personnel_boutique);
            oci_bind_by_name($requeteP, ":id_zone", $id_zone_boutique);
            oci_execute($requeteP);

            oci_commit($conn);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $message = "Veuillez remplir tous les champs de la boutique.";
    }
}

/* =========================
   SUPPRESSION ANIMAL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_rfid'])) {
    $rfid_suppr = $_POST['supprimer_rfid'];

    $requeteP = oci_parse($conn,
        "DELETE FROM Attitre
         WHERE RFID = :rfid"
    );
    oci_bind_by_name($requeteP, ":rfid", $rfid_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Soins
         WHERE RFID = :rfid"
    );
    oci_bind_by_name($requeteP, ":rfid", $rfid_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Consomme
         WHERE RFID = :rfid"
    );
    oci_bind_by_name($requeteP, ":rfid", $rfid_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Parrainer
         WHERE RFID = :rfid"
    );
    oci_bind_by_name($requeteP, ":rfid", $rfid_suppr);
    oci_execute($requeteP);

    $requeteP = oci_parse($conn,
        "DELETE FROM Animal
         WHERE RFID = :rfid"
    );
    oci_bind_by_name($requeteP, ":rfid", $rfid_suppr);
    oci_execute($requeteP);

    oci_commit($conn);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* =========================
   AJOUT ANIMAL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_animal'])) {
    if (
        !empty($_POST['rfid']) &&
        !empty($_POST['nom_animal']) &&
        !empty($_POST['date_naissance']) &&
        !empty($_POST['poids']) &&
        !empty($_POST['id_enclos_animal']) &&
        !empty($_POST['espece_animal'])
    ) {
        $rfid = $_POST['rfid'];
        $nom_animal = $_POST['nom_animal'];
        $date_naissance = $_POST['date_naissance'];
        $poids = $_POST['poids'];
        $id_enclos_animal = $_POST['id_enclos_animal'];
        $espece_animal = $_POST['espece_animal'];

        $requeteP = oci_parse($conn,
            "SELECT nom_latin
             FROM Espece
             WHERE nom_usuel = :nom_usuel"
        );
        oci_bind_by_name($requeteP, ":nom_usuel", $espece_animal);
        oci_execute($requeteP);
        $rowEspece = oci_fetch_assoc($requeteP);

        if (!$rowEspece) {
            $message = "Espèce introuvable.";
        } else {
            $nom_latin = $rowEspece['NOM_LATIN'];

            $requeteP = oci_parse($conn,
                "INSERT INTO Animal
                 VALUES (:rfid, :nom_animal, TO_DATE(:date_naissance, 'YYYY-MM-DD'), :poids, NULL, NULL, :id_enclos, :nom_latin)"
            );
            oci_bind_by_name($requeteP, ":rfid", $rfid);
            oci_bind_by_name($requeteP, ":nom_animal", $nom_animal);
            oci_bind_by_name($requeteP, ":date_naissance", $date_naissance);
            oci_bind_by_name($requeteP, ":poids", $poids);
            oci_bind_by_name($requeteP, ":id_enclos", $id_enclos_animal);
            oci_bind_by_name($requeteP, ":nom_latin", $nom_latin);
            oci_execute($requeteP);

            oci_commit($conn);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $message = "Veuillez remplir tous les champs de l'animal.";
    }
}

/* =========================
   REQUÊTES D'AFFICHAGE
========================= */

$requetePersonnel = oci_parse($conn,
    "SELECT P.id_personnel, P.prenom_personnel, P.nom_personnel, P.id_connexion,
            Z.libelle AS zone_libelle, C.id_contrat, C.salaire,
            TO_CHAR(C.date_debut,'YYYY-MM-DD') AS date_debut, F.fonction
     FROM Personnel P, Contrat C, Fonction F, Zone_zoo Z
     WHERE C.id_personnel = P.id_personnel
       AND C.id_fonction = F.id_fonction
       AND P.id_zone = Z.id_zone(+)
     ORDER BY P.id_personnel"
);
oci_execute($requetePersonnel);

$requeteEnclos = oci_parse($conn,
    "SELECT E.id_enclos, E.latitude, E.longitude, E.surface, Z.libelle AS zone_libelle
     FROM Enclos E, Zone_zoo Z
     WHERE E.id_zone = Z.id_zone
     ORDER BY E.id_enclos"
);
oci_execute($requeteEnclos);

$requeteBoutique = oci_parse($conn,
    "SELECT B.id_boutique, B.nom_boutique, B.type_boutique,
            P.prenom_personnel || ' ' || P.nom_personnel AS responsable,
            Z.libelle AS zone_libelle
     FROM Boutique B, Personnel P, Zone_zoo Z
     WHERE B.id_personnel = P.id_personnel(+)
       AND B.id_zone = Z.id_zone
     ORDER BY B.id_boutique"
);
oci_execute($requeteBoutique);

$requeteAnimal = oci_parse($conn,
    "SELECT A.RFID, A.nom_animal, TO_CHAR(A.date_naissance,'YYYY-MM-DD') AS date_naissance,
            A.poids, A.id_enclos, E.nom_usuel
     FROM Animal A, Espece E
     WHERE A.nom_latin = E.nom_latin
     ORDER BY A.RFID"
);
oci_execute($requeteAnimal);
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

<?php if ($message !== ""): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<h2>Gestion du personnel</h2>
<table border="1">
    <tr>
        <th>ID_PERSONNEL</th>
        <th>PRENOM_PERSONNEL</th>
        <th>NOM_PERSONNEL</th>
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
        <tr>
            <td><?php echo $row['ID_PERSONNEL']; ?></td>
            <td><?php echo $row['PRENOM_PERSONNEL']; ?></td>
            <td><?php echo $row['NOM_PERSONNEL']; ?></td>
            <td><?php echo $row['ID_CONNEXION']; ?></td>
            <td><?php echo $row['ZONE_LIBELLE']; ?></td>
            <td><?php echo $row['ID_CONTRAT']; ?></td>
            <td><?php echo $row['SALAIRE']; ?></td>
            <td><?php echo $row['DATE_DEBUT']; ?></td>
            <td><?php echo $row['FONCTION']; ?></td>
            <td>************</td>
            <td>
                <form method="post">
                    <input type="hidden" name="supprimer_id_personnel" value="<?php echo $row['ID_PERSONNEL']; ?>">
                    <input type="submit" value="Supprimer" onclick="return confirm('Confirmer la suppression ?')">
                </form>
            </td>
        </tr>
    <?php endwhile; ?>

    <tr>
        <form method="post">
            <td><input type="text" name="id_personnel"></td>
            <td><input type="text" name="prenom_personnel"></td>
            <td><input type="text" name="nom_personnel"></td>
            <td><input type="text" name="id_connexion"></td>
            <td>
                <select name="zone_personnel">
                    <option value="Zone Afrique">Zone Afrique</option>
                    <option value="Zone Asie">Zone Asie</option>
                    <option value="Zone France">Zone France</option>
                    <option value="Zone Dinosaure">Zone Dinosaure</option>
                    <option value="Zone Aquatique">Zone Aquatique</option>
                </select>
            </td>
            <td><input type="text" name="id_contrat"></td>
            <td><input type="text" name="salaire"></td>
            <td><input type="date" name="date_debut"></td>
            <td>
                <select name="fonction">
                    <option value="Directeur">Directeur</option>
                    <option value="Technicien">Technicien</option>
                    <option value="Soigneur">Soigneur</option>
                    <option value="Employe de magasin">Employe de magasin</option>
                    <option value="Directeur de magasin">Directeur de magasin</option>
                </select>
            </td>
            <td><input type="password" name="mot_de_passe"></td>
            <td><input type="submit" name="ajouter_personnel" value="Ajouter"></td>
        </form>
    </tr>
</table>

<br><br>

<h2>Gestion des enclos</h2>
<table border="1">
    <tr>
        <th>ID_ENCLOS</th>
        <th>LATITUDE</th>
        <th>LONGITUDE</th>
        <th>SURFACE</th>
        <th>ZONE</th>
        <th>ACTION</th>
    </tr>

    <?php while ($row = oci_fetch_assoc($requeteEnclos)): ?>
        <tr>
            <td><?php echo $row['ID_ENCLOS']; ?></td>
            <td><?php echo $row['LATITUDE']; ?></td>
            <td><?php echo $row['LONGITUDE']; ?></td>
            <td><?php echo $row['SURFACE']; ?></td>
            <td><?php echo $row['ZONE_LIBELLE']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="supprimer_id_enclos" value="<?php echo $row['ID_ENCLOS']; ?>">
                    <input type="submit" value="Supprimer" onclick="return confirm('Confirmer la suppression ?')">
                </form>
            </td>
        </tr>
    <?php endwhile; ?>

    <tr>
        <form method="post">
            <td><input type="text" name="id_enclos"></td>
            <td><input type="text" name="latitude"></td>
            <td><input type="text" name="longitude"></td>
            <td><input type="text" name="surface"></td>
            <td>
                <select name="zone_enclos">
                    <option value="Zone Afrique">Zone Afrique</option>
                    <option value="Zone Asie">Zone Asie</option>
                    <option value="Zone France">Zone France</option>
                    <option value="Zone Dinosaure">Zone Dinosaure</option>
                    <option value="Zone Aquatique">Zone Aquatique</option>
                </select>
            </td>
            <td><input type="submit" name="ajouter_enclos" value="Ajouter"></td>
        </form>
    </tr>
</table>

<br><br>

<h2>Gestion des boutiques</h2>
<table border="1">
    <tr>
        <th>ID_BOUTIQUE</th>
        <th>NOM_BOUTIQUE</th>
        <th>TYPE_BOUTIQUE</th>
        <th>RESPONSABLE</th>
        <th>ZONE</th>
        <th>ACTION</th>
    </tr>

    <?php while ($row = oci_fetch_assoc($requeteBoutique)): ?>
        <tr>
            <td><?php echo $row['ID_BOUTIQUE']; ?></td>
            <td><?php echo $row['NOM_BOUTIQUE']; ?></td>
            <td><?php echo $row['TYPE_BOUTIQUE']; ?></td>
            <td><?php echo $row['RESPONSABLE']; ?></td>
            <td><?php echo $row['ZONE_LIBELLE']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="supprimer_id_boutique" value="<?php echo $row['ID_BOUTIQUE']; ?>">
                    <input type="submit" value="Supprimer" onclick="return confirm('Confirmer la suppression ?')">
                </form>
            </td>
        </tr>
    <?php endwhile; ?>

    <tr>
        <form method="post">
            <td><input type="text" name="id_boutique"></td>
            <td><input type="text" name="nom_boutique"></td>
            <td><input type="text" name="type_boutique"></td>
            <td><input type="text" name="responsable_boutique"></td>
            <td>
                <select name="zone_boutique">
                    <option value="Zone Afrique">Zone Afrique</option>
                    <option value="Zone Asie">Zone Asie</option>
                    <option value="Zone France">Zone France</option>
                    <option value="Zone Dinosaure">Zone Dinosaure</option>
                    <option value="Zone Aquatique">Zone Aquatique</option>
                </select>
            </td>
            <td><input type="submit" name="ajouter_boutique" value="Ajouter"></td>
        </form>
    </tr>
</table>

<br><br>

<h2>Gestion des animaux</h2>
<table border="1">
    <tr>
        <th>RFID</th>
        <th>NOM_ANIMAL</th>
        <th>DATE_NAISSANCE</th>
        <th>POIDS</th>
        <th>ID_ENCLOS</th>
        <th>ESPECE</th>
        <th>ACTION</th>
    </tr>

    <?php while ($row = oci_fetch_assoc($requeteAnimal)): ?>
        <tr>
            <td><?php echo $row['RFID']; ?></td>
            <td><?php echo $row['NOM_ANIMAL']; ?></td>
            <td><?php echo $row['DATE_NAISSANCE']; ?></td>
            <td><?php echo $row['POIDS']; ?></td>
            <td><?php echo $row['ID_ENCLOS']; ?></td>
            <td><?php echo $row['NOM_USUEL']; ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="supprimer_rfid" value="<?php echo $row['RFID']; ?>">
                    <input type="submit" value="Supprimer" onclick="return confirm('Confirmer la suppression ?')">
                </form>
            </td>
        </tr>
    <?php endwhile; ?>

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

</body>
</html>