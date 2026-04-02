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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réparations</title>
    <link rel="stylesheet" href="css/main.css">
</head>
    <body>

        <a href="search.php"><button type="button">Retour à l'accueil</button></a>

        <h2>Gestion des réparations</h2>

        <?php if ($message): ?>
            <p><strong><?= htmlspecialchars($message) ?></strong></p>
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
                <td><?= htmlspecialchars($r['ID_REPARATION']) ?></td>
                <td><?= htmlspecialchars($r['NATURE_REPARATION']) ?></td>
                <td><?= htmlspecialchars($r['LIBELLE_REPARATION'] ?? '-') ?></td>
                <td>Enclos <?= htmlspecialchars($r['ID_ENCLOS']) ?> – <?= htmlspecialchars($r['LIBELLE_ZONE']) ?></td>
                <td><?= htmlspecialchars($r['PRENOM_PERSONNEL'].' '.$r['NOM_PERSONNEL']) ?></td>
                <td><?= !empty($r['NOM_SOCIETE']) ? htmlspecialchars($r['NOM_SOCIETE']) : 'Interne' ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- Ligne d'ajout -->
            <tr>
                <form method="post">
                    <td><input type="text" value="<?= htmlspecialchars($nextIdR) ?>" readonly></td>

                    <td>
                        <input type="text" name="nature_reparation" required>
                    </td>

                    <td>
                        <input type="text" name="libelle_reparation" placeholder="(optionnel)">
                    </td>

                    <td>
                        <select name="id_enclos_reparation" required>
                            <?php foreach ($enclos as $e): ?>
                            <option value="<?= $e['ID_ENCLOS'] ?>">
                                Enclos <?= htmlspecialchars($e['ID_ENCLOS']) ?> – <?= htmlspecialchars($e['LIBELLE_ZONE']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <select name="id_personnel_reparation" required>
                            <?php foreach ($techniciens as $p): ?>
                            <option value="<?= $p['ID_PERSONNEL'] ?>">
                                <?= htmlspecialchars($p['PRENOM_PERSONNEL'].' '.$p['NOM_PERSONNEL']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <select name="id_prestataire_reparation">
                            <option value="">Interne (aucun prestataire)</option>
                            <?php foreach ($prestataires as $pr): ?>
                            <option value="<?= $pr['ID_PRESTATAIRE'] ?>">
                                <?= htmlspecialchars($pr['NOM_SOCIETE']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td><input type="submit" name="ajouter_reparation" value="Ajouter"></td>
                </form>
            </tr>
        </table>

    </body>
</html>