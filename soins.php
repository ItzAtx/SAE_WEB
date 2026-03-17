<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

$message = "";

/* ============
   ATTRIBUTION
=============== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfid'], $_POST['id_personnel'])) {
    $rfid = $_POST['rfid'];
    $id_personnel = $_POST['id_personnel'];

    /* Insertion de l'attribution */
    $insert = execQuery($conn,
        "INSERT INTO Attitre (RFID, id_personnel)
        VALUES (:rfid, :id_personnel)",
        [":rfid" => $rfid, ":id_personnel" => $id_personnel]
    );

    oci_commit($conn);
    $message = "<p>Soigneur attitré ajouté avec succès.</p>"; 
}

/* =========================
   RECUPERATION DES SOINS
========================= */
$reqSoins = execQuery(
    $conn,
    "SELECT s.id_soin, s.date_soin, s.complexite, a.RFID, a.nom_animal, a.nom_latin, p.id_personnel, p.nom_personnel, p.prenom_personnel
    FROM Soins s
    JOIN Animal a ON s.RFID = a.RFID
    JOIN Personnel p ON s.id_personnel = p.id_personnel
    ORDER BY s.date_soin DESC, s.id_soin DESC"
);

/* =========================
   ANIMAUX SANS SOIGNEUR ATTITRE
========================= */
$reqAnimauxSansSoigneur = execQuery(
    $conn,
    "SELECT a.RFID, a.nom_animal, a.nom_latin
    FROM Animal a
    WHERE NOT EXISTS (
        SELECT 1
        FROM Attitre at
        WHERE at.RFID = a.RFID
    )
    ORDER BY a.nom_animal"
);

/* ===================
   LISTE DES SOIGNEURS
====================== */
$reqSoigneurs = execQuery(
    $conn,
    "SELECT DISTINCT p.id_personnel, p.nom_personnel, p.prenom_personnel
    FROM Personnel p
    JOIN Contrat c ON p.id_personnel = c.id_personnel
    JOIN Fonction f ON c.id_fonction = f.id_fonction
    WHERE LOWER(f.fonction) = LOWER('Soigneur')
    AND p.archiver_personnel = 'N'
    ORDER BY p.nom_personnel, p.prenom_personnel"
);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soins</title>
</head>
<body>
    <div class="container">
        <a href="search.php">Retour à l'accueil</a>

        <h1>Liste des soins</h1>

        <div class="message">
            <?php echo $message; ?>
        </div>

        <div class="card">
            <table border="1">
                <tr>
                    <th>ID soin</th>
                    <th>Date</th>
                    <th>Complexité</th>
                    <th>Animal</th>
                    <th>RFID</th>
                    <th>Espèce</th>
                    <th>Personnel ayant réalisé le soin</th>
                </tr>

                <?php
                    while ($row = oci_fetch_assoc($reqSoins)) {
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($row['ID_SOIN'])."</td>";
                        echo "<td>".htmlspecialchars($row['DATE_SOIN'])."</td>";
                        echo "<td>".htmlspecialchars($row['COMPLEXITE'])."</td>";
                        echo "<td>".htmlspecialchars($row['NOM_ANIMAL'])."</td>";
                        echo "<td>".htmlspecialchars($row['RFID'])."</td>";
                        echo "<td>".htmlspecialchars($row['NOM_LATIN'])."</td>";
                        echo "<td>".htmlspecialchars($row['PRENOM_PERSONNEL']." ".$row['NOM_PERSONNEL'])."</td>";
                        echo "</tr>";
                    }
                ?>
            </table>
        </div>

        <div class="card">
            <h2>Attribuer soigneur</h2>

            <form method="post">
                <label><strong>Animal :</strong></label>
                <select name="rfid" required>
                    <?php
                        $animaux = [];
                        while ($row = oci_fetch_assoc($reqAnimauxSansSoigneur)) {
                            $animaux[] = $row;
                        }

                        foreach ($animaux as $animal) {
                            echo "<option value='".htmlspecialchars($animal['RFID'])."'>";
                            echo htmlspecialchars($animal['NOM_ANIMAL']." (RFID ".$animal['RFID']." - ".$animal['NOM_LATIN'].")");
                            echo "</option>";
                        }
                    ?>
                </select>

                <label for="id_personnel"><strong>Soigneur :</strong></label>
                <select name="id_personnel" id="id_personnel" required>
                    <?php
                        while ($row = oci_fetch_assoc($reqSoigneurs)) {
                            echo "<option value='".htmlspecialchars($row['ID_PERSONNEL'])."'>";
                            echo htmlspecialchars($row['PRENOM_PERSONNEL']." ".$row['NOM_PERSONNEL']." (id ".$row['ID_PERSONNEL'].")");
                            echo "</option>";
                        }
                    ?>
                </select>

                <button type="submit">Attribuer</button>
            </form>

            <?php if (count($animaux) === 0) : ?>
                <p class="empty">Tous les animaux ont déjà un soigneur attitré.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>