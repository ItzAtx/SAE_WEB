<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //Ajout réparation
        if (isset($_POST['ajouter_reparation'])) {
            if (!postFieldsFilled(['nature_reparation', 'id_enclos_reparation', 'id_personnel_reparation'])) {
                $message = "Veuillez remplir tous les champs obligatoires.";
            } else {
                $nature = $_POST['nature_reparation'];
                $libelle= $_POST['libelle_reparation'] ?? null;
                $idEnclos = $_POST['id_enclos_reparation'];
                $idPerso = $_POST['id_personnel_reparation'];
                $idPrest = !empty($_POST['id_prestataire_reparation']) ? $_POST['id_prestataire_reparation'] : null;

                $idR = getNextId($conn, "Reparation", "id_reparation");

                execQuery($conn,
                    "INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos)
                    VALUES (:id, :nat, :lib, :enc)",
                    [':id' => $idR, ':nat' => $nature, ':lib' => $libelle, ':enc' => $idEnclos]
                );

                execQuery($conn,
                    "INSERT INTO Entretient (id_personnel, id_reparation) VALUES (:ip, :ir)",
                    [':ip' => $idPerso, ':ir' => $idR]
                );

                if ($idPrest) {
                    execQuery($conn,
                        "INSERT INTO Participe (id_prestataire, id_reparation) VALUES (:ipr, :ir)",
                        [':ipr' => $idPrest, ':ir' => $idR]
                    );
                }

                oci_commit($conn);
                $message = "Réparation ajoutée avec succès.";
            }
        }
    }

    $reparations = fetchAllRows($conn,
        "SELECT id_reparation, nature_reparation, libelle_reparation, id_enclos,
                libelle_zone, nom_personnel, prenom_personnel, nom_societe
        FROM Vue_Reparation
        ORDER BY id_reparation"
    );

    $enclos = fetchAllRows($conn,
        "SELECT id_enclos, libelle_zone FROM Vue_Enclos ORDER BY id_enclos"
    );

    //Uniquement les techniciens non archivés via Vue_Personnel
    $techniciens = fetchAllRows($conn,
        "SELECT id_personnel, nom_personnel, prenom_personnel
        FROM Vue_Personnel
        WHERE LOWER(fonction) LIKE '%technicien%'
        AND archiver_personnel = 'N'
        ORDER BY nom_personnel"
    );

    $prestataires = fetchAllRows($conn,
        "SELECT id_prestataire, nom_societe FROM Prestataire ORDER BY nom_societe"
    );

    $nextIdR = getNextId($conn, "Reparation", "id_reparation");

    /* =========================
    AJOUT PRESTATAIRE
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_prestataire'])) {
        if (!postFieldsFilled(['nom_societe', 'adresse_societe', 'telephone_societe'])) {
            $message = "Veuillez remplir tous les champs.";
        } else {
            $idPrest = getNextId($conn, "Prestataire", "id_prestataire");

            execQuery(
                $conn,
                "INSERT INTO Prestataire (id_prestataire, adresse_societe, nom_societe, telephone_societe)
                VALUES (:id, :adresse, :nom, :tel)",
                [
                    ':id'      => $idPrest,
                    ':adresse' => $_POST['adresse_societe'],
                    ':nom'     => $_POST['nom_societe'],
                    ':tel'     => $_POST['telephone_societe']
                ]
            );

            oci_commit($conn);
            $message = "Prestataire ajouté avec succès.";
        }
    }

    /* =========================
    AFFICHAGE PRESTATAIRES
    ========================= */
    $prestataires = fetchAllRows(
        $conn,
        "SELECT id_prestataire, nom_societe, adresse_societe, telephone_societe
        FROM Prestataire
        ORDER BY nom_societe"
    );

    $nextIdPrest = getNextId($conn, "Prestataire", "id_prestataire");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réparations</title>
    <link rel="stylesheet" href="css/main.css">
</head>
    <body>

        <a href="search.php"><button type="button">Accueil</button></a>

        <h2>Gestion des réparations</h2>

        <?php if ($message): ?>
            <p><strong><?php echo htmlspecialchars($message) ?></strong></p>
        <?php endif; ?>

        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nature</th>
                <th>Libellé</th>
                <th>Enclos (Zone)</th>
                <th>Personnel</th>
                <th>Prestataire</th>
            </tr>

            <?php foreach ($reparations as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['ID_REPARATION']) ?></td>
                <td><?php echo htmlspecialchars($r['NATURE_REPARATION']) ?></td>
                <td><?php echo htmlspecialchars($r['LIBELLE_REPARATION'] ?? '-') ?></td>
                <td>Enclos <?php echo htmlspecialchars($r['ID_ENCLOS']) ?> – <?php echo htmlspecialchars($r['LIBELLE_ZONE']) ?></td>
                <td><?php echo htmlspecialchars($r['PRENOM_PERSONNEL'].' '.$r['NOM_PERSONNEL']) ?></td>
                <td><?php echo !empty($r['NOM_SOCIETE']) ? htmlspecialchars($r['NOM_SOCIETE']) : 'Interne' ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- Ligne d'ajout -->
            <tr>
                <form method="post">
                    <td><input type="text" value="<?php echo htmlspecialchars($nextIdR) ?>" readonly></td>

                    <td>
                        <input type="text" name="nature_reparation" required>
                    </td>

                    <td>
                        <input type="text" name="libelle_reparation" placeholder="(optionnel)">
                    </td>

                    <td>
                        <select name="id_enclos_reparation" required>
                            <?php foreach ($enclos as $e): ?>
                            <option value="<?php echo $e['ID_ENCLOS'] ?>">
                                Enclos <?php echo htmlspecialchars($e['ID_ENCLOS']) ?> – <?php echo htmlspecialchars($e['LIBELLE_ZONE']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <select name="id_personnel_reparation" required>
                            <?php foreach ($techniciens as $p): ?>
                            <option value="<?php echo $p['ID_PERSONNEL'] ?>">
                                <?php echo htmlspecialchars($p['PRENOM_PERSONNEL'].' '.$p['NOM_PERSONNEL']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <select name="id_prestataire_reparation">
                            <option value="">Interne (aucun prestataire)</option>
                            <?php foreach ($prestataires as $pr): ?>
                            <option value="<?php echo $pr['ID_PRESTATAIRE'] ?>">
                                <?php echo htmlspecialchars($pr['NOM_SOCIETE']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td><input type="submit" name="ajouter_reparation" value="Ajouter"></td>
                </form>
            </tr>
        </table>

        <h2>Gestion des prestataires</h2>

        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nom société</th>
                <th>Adresse</th>
                <th>Téléphone</th>
            </tr>

            <?php foreach ($prestataires as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['ID_PRESTATAIRE']) ?></td>
                <td><?php echo htmlspecialchars($p['NOM_SOCIETE']) ?></td>
                <td><?php echo htmlspecialchars($p['ADRESSE_SOCIETE']) ?></td>
                <td><?php echo '0'.htmlspecialchars($p['TELEPHONE_SOCIETE']) ?></td>
            </tr>
            <?php endforeach; ?>

            <tr>
                <form method="post">
                    <td>
                        <input type="text" value="<?php echo htmlspecialchars($nextIdPrest) ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="nom_societe" required>
                    </td>
                    <td>
                        <input type="text" name="adresse_societe" required>
                    </td>
                    <td>
                        <input type="text" name="telephone_societe" required>
                    </td>
                    <td>
                        <input type="submit" name="ajouter_prestataire" value="Ajouter">
                    </td>
                </form>
            </tr>
        </table>

    </body>
</html>