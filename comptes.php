<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";

    //Récupération des boutiques
    $boutiques = fetchAllRows($conn,
        "SELECT id_boutique, nom_boutique FROM Boutique ORDER BY id_boutique"
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_ca'])) {
        $date = $_POST['date_ca'];
        $erreur = false;

        if (empty($date)) {
            $message = "Veuillez saisir une date.";
            $erreur = true;
        } else {
            foreach ($boutiques as $b) {
                $idBoutique = $b['ID_BOUTIQUE'];
                $montant = $_POST['montant_'.$idBoutique] ?? '';

                if ($montant !== '' && !$erreur) {
                    $dernierCA = fetchOne($conn,
                        "SELECT TO_CHAR(MAX(date_ca), 'YYYY-MM-DD') AS derniere_date
                        FROM Chiffre_affaire WHERE id_boutique = :id",
                        [':id' => $idBoutique]
                    );
                    $derniereDate = $dernierCA['DERNIERE_DATE'];

                    if ($derniereDate && $date < $derniereDate) {
                        $message = "Erreur : la date est antérieure au dernier CA.";
                        $erreur = true;
                    } else {
                        $idCA = getNextId($conn, "Chiffre_affaire", "id_ca");
                        
                        $montant = str_replace('.', ',', $montant);
                        execQuery($conn,
                            "INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique)
                            VALUES (:id, TO_DATE(:date_ca, 'YYYY-MM-DD'), :montant, :boutique)",
                            [':id' => $idCA, ':date_ca' => $date, ':montant' => $montant, ':boutique' => $idBoutique]
                        );
                    }
                }
            }

            if (!$erreur) {
                oci_commit($conn);
                $message = "Chiffres d'affaires ajoutés avec succès.";
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['affecter_employe'])) {
        $idPersonnel = $_POST['id_personnel'];
        $idBoutique  = $_POST['id_boutique_affectation'];

        execQuery($conn,
            "INSERT INTO Travaille (id_personnel, id_boutique) VALUES (:ip, :ib)",
            [':ip' => $idPersonnel, ':ib' => $idBoutique]
        );
        oci_commit($conn);
        $message = "Employé affecté avec succès.";
    }

    $dates = fetchAllRows($conn,
        "SELECT DISTINCT TO_CHAR(date_ca, 'YYYY-MM-DD') AS date_ca
        FROM Chiffre_affaire
        ORDER BY date_ca"
    );

    $allCA = fetchAllRows($conn,
        "SELECT id_boutique, TO_CHAR(date_ca, 'YYYY-MM-DD') AS date_ca, montant
        FROM Chiffre_affaire"
    );

    $caIndex = [];
    foreach ($allCA as $ca) {
        //On index chaque ca selon sa date et son id boutique (cast en float car Oracle renvoie string de base)
        $caIndex[$ca['DATE_CA']][$ca['ID_BOUTIQUE']] = (float) str_replace(',', '.', $ca['MONTANT']);
    }

    $derniersCA = [];
    //On trouve la date la plus récente pour chaque groupe d'id de boutique
    $rows = fetchAllRows($conn,
        "SELECT id_boutique, TO_CHAR(MAX(date_ca), 'YYYY-MM-DD') AS derniere_date
        FROM Chiffre_affaire
        GROUP BY id_boutique"
    );
    foreach ($rows as $row) {
        $derniersCA[$row['ID_BOUTIQUE']] = $row['DERNIERE_DATE'];
    }

    $maxDateGlobale = max($derniersCA);

    //Employés de magasin non encore dans Travaille
    $employes = fetchAllRows($conn,
        "SELECT DISTINCT P.id_personnel, P.nom_personnel, P.prenom_personnel
        FROM Personnel P, Contrat C, Fonction F
        WHERE P.id_personnel = C.id_personnel
        AND C.id_fonction = F.id_fonction
        AND F.fonction = 'Employe de magasin'
        AND P.archiver_personnel = 'N'
        ORDER BY P.nom_personnel"
    );

    $affectations = fetchAllRows($conn,
    "SELECT P.nom_personnel, P.prenom_personnel, B.nom_boutique
     FROM Travaille T, Personnel P, Boutique B
     WHERE T.id_personnel = P.id_personnel
     AND T.id_boutique = B.id_boutique
     ORDER BY P.nom_personnel"
    );
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Chiffres d'affaires</title>
        <link rel="stylesheet" href="css/main.css">
    </head>
    <body>

        <a href="search.php"><button type="button">Accueil</button></a>

        <h2>Chiffres d'affaires par boutique</h2>

        <?php if ($message): ?>
            <p><strong><?= htmlspecialchars($message) ?></strong></p>
        <?php endif; ?>

        <table border="1">
            <tr>
                <th>Date</th>
                <?php foreach ($boutiques as $b): ?>
                    <th><?= htmlspecialchars($b['NOM_BOUTIQUE']) ?></th>
                <?php endforeach; ?>
                <th>Total</th>
            </tr>

            <?php foreach ($dates as $d):
                $date = $d['DATE_CA'];
                $total = 0;
            ?>
                <tr>
                    <td><?= $date ?></td>
                    <?php foreach ($boutiques as $b):
                        $montant = $caIndex[$date][$b['ID_BOUTIQUE']] ?? 0;
                        $total += $montant;
                    ?>
                        <td><?= $montant ?> €</td>
                    <?php endforeach; ?>

                    <td><strong><?= $total ?> €</strong></td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <form method="post">
                    <td><input type="date" name="date_ca" required></td>
                    <?php foreach ($boutiques as $b): ?>
                        <td>
                            <input type="number" name="montant_<?= $b['ID_BOUTIQUE'] ?>" min="0" placeholder="0.00">
                        </td>
                    <?php endforeach; ?>
                    <td><input type="submit" name="ajouter_ca" value="Ajouter"></td>
                </form>
            </tr>
        </table>

        <br>

        <h2>Affectations des employés</h2>

        <table border="1">
            <tr>
                <th>Employé</th>
                <th>Boutique</th>
            </tr>
            <?php foreach ($affectations as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['PRENOM_PERSONNEL'].' '.$a['NOM_PERSONNEL']) ?></td>
                    <td><?= htmlspecialchars($a['NOM_BOUTIQUE']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <br>

        <h2>Affecter un employé à une boutique</h2>

        <form method="post">
            <select name="id_personnel">
                <?php foreach ($employes as $e): ?>
                    <option value="<?= $e['ID_PERSONNEL'] ?>">
                        <?= htmlspecialchars($e['PRENOM_PERSONNEL'].' '.$e['NOM_PERSONNEL']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="id_boutique_affectation">
                <?php foreach ($boutiques as $b): ?>
                    <option value="<?= $b['ID_BOUTIQUE'] ?>">
                        <?= htmlspecialchars($b['NOM_BOUTIQUE']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" name="affecter_employe" value="Affecter">
        </form>

        <br>

        

    </body>
</html>
