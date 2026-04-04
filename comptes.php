<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";
    if (isset($_GET['msg']) && $_GET['msg'] === 'reaffecte') {
        $message = "Employé réaffecté avec succès.";
    }

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

    /* =========================
    RETRAIT D'UN EMPLOYE D'UNE BOUTIQUE
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirer_employe'])) {
        $idPersonnel = $_POST['retirer_id_personnel'];
        $idBoutique  = $_POST['retirer_id_boutique'];

        // Compter les boutiques où il travaille encore (hors celle-ci)
        $autresBoutiques = fetchAllRows($conn,
            "SELECT T.id_boutique FROM Travaille T WHERE T.id_personnel = :id AND T.id_boutique <> :ib",
            [':id' => $idPersonnel, ':ib' => $idBoutique]
        );

        if (empty($autresBoutiques)) {
            // Il ne travaillera plus nulle part : rediriger vers écran de confirmation
            header('Location: comptes.php?confirmer_retrait='.$idPersonnel.'&quitter_boutique='.$idBoutique);
            exit;
        } else {
            deleteWhere($conn, 'Travaille', 'id_personnel', $idPersonnel);
            // On remet uniquement les boutiques restantes
            foreach ($autresBoutiques as $ab) {
                execQuery($conn,
                    "INSERT INTO Travaille (id_personnel, id_boutique) VALUES (:ip, :ib)",
                    [':ip' => $idPersonnel, ':ib' => $ab['ID_BOUTIQUE']]
                );
            }
            oci_commit($conn);
            $message = "Employé retiré de la boutique avec succès.";
        }
    }

    /* =========================
    ARCHIVAGE DEPUIS COMPTES
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archiver_employe_comptes'])) {
        $idPersonnel = $_POST['archiver_id_personnel'];
        $idBoutique  = $_POST['archiver_quitter_boutique'];
        $dateFin     = $_POST['date_fin_archivage'];

        $rowContrat = fetchOne($conn,
            "SELECT TO_CHAR(date_debut, 'YYYY-MM-DD') AS date_debut FROM Contrat WHERE id_personnel = :id AND date_fin IS NULL",
            [':id' => $idPersonnel]
        );

        if ($rowContrat && $dateFin <= $rowContrat['DATE_DEBUT']) {
            $message = "La date de fin doit être postérieure à la date de début (".$rowContrat['DATE_DEBUT'].").";
        } else {
            deleteWhere($conn, 'Travaille', 'id_boutique', $idBoutique);
            archiverPersonnel($conn, $idPersonnel, $dateFin);
            $message = "Employé archivé avec succès.";
        }
    }

    /* =========================
    REAFFECTATION DEPUIS COMPTES
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reaffecter_employe'])) {
        $idPersonnel    = $_POST['reaffecter_id_personnel'];
        $idBoutiqueOld  = $_POST['reaffecter_quitter_boutique'];
        $idBoutiqueNew  = $_POST['nouvelle_boutique'];

        execQuery($conn,
            "DELETE FROM Travaille WHERE id_personnel = :ip AND id_boutique = :ib",
            [':ip' => $idPersonnel, ':ib' => $idBoutiqueOld]
        );
        execQuery($conn,
            "INSERT INTO Travaille (id_personnel, id_boutique) VALUES (:ip, :ib)",
            [':ip' => $idPersonnel, ':ib' => $idBoutiqueNew]
        );
        oci_commit($conn);
        header('Location: comptes.php?msg=reaffecte');
        exit;

    }

    // Lecture des paramètres GET pour l'écran de confirmation de retrait
    $confirmerRetrait  = $_GET['confirmer_retrait']  ?? null;
    $quitterBoutique   = $_GET['quitter_boutique']   ?? null;

    // Données pour l'écran de confirmation
    $employe_a_retirer = null;
    $boutiques_dispo   = [];
    if ($confirmerRetrait && $quitterBoutique) {
        $employe_a_retirer = fetchOne($conn,
            "SELECT P.id_personnel, prenom_personnel, nom_personnel,
                    TO_CHAR(C.date_debut,'YYYY-MM-DD') AS date_debut
            FROM Personnel P, Contrat C
            WHERE P.id_personnel = :id
            AND C.id_personnel = P.id_personnel
            AND C.date_fin IS NULL",
            [':id' => $confirmerRetrait]
        );
        // Boutiques où il ne travaille pas encore
        $boutiques_dispo = fetchAllRows($conn,
            "SELECT B.id_boutique, B.nom_boutique FROM Boutique B
            WHERE NOT EXISTS (
                SELECT 1 FROM Travaille T
                WHERE T.id_boutique = B.id_boutique
                AND T.id_personnel = :id
            )",
            [':id' => $confirmerRetrait]
        );
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
    "SELECT P.id_personnel, P.nom_personnel, P.prenom_personnel, B.id_boutique, B.nom_boutique
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

        <?php if ($confirmerRetrait && $employe_a_retirer): ?>
            <p>
                <strong><?= htmlspecialchars($employe_a_retirer['PRENOM_PERSONNEL'].' '.$employe_a_retirer['NOM_PERSONNEL']) ?></strong>
                ne travaillera plus nulle part après ce retrait. Que souhaitez-vous faire ?
            </p>

            <form method="post">
                <input type="hidden" name="archiver_id_personnel"    value="<?= htmlspecialchars($confirmerRetrait) ?>">
                <input type="hidden" name="archiver_quitter_boutique" value="<?= htmlspecialchars($quitterBoutique) ?>">
                <label>Date de fin de contrat :
                    <input type="date" name="date_fin_archivage" required>
                </label>
                <input type="submit" name="archiver_employe_comptes" value="Archiver l'employé">
            </form>

            <?php if (!empty($boutiques_dispo)): ?>
                <form method="post">
                    <input type="hidden" name="reaffecter_id_personnel"    value="<?= htmlspecialchars($confirmerRetrait) ?>">
                    <input type="hidden" name="reaffecter_quitter_boutique" value="<?= htmlspecialchars($quitterBoutique) ?>">
                    <label>Réaffecter dans :
                        <select name="nouvelle_boutique">
                            <?php foreach ($boutiques_dispo as $bd): ?>
                                <option value="<?= htmlspecialchars($bd['ID_BOUTIQUE']) ?>">
                                    <?= htmlspecialchars($bd['NOM_BOUTIQUE']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <input type="submit" name="reaffecter_employe" value="Réaffecter">
                </form>
            <?php else: ?>
                <p><em>Aucune autre boutique disponible pour une réaffectation.</em></p>
            <?php endif; ?>
        <?php endif; ?>

        <table border="1">
            <tr>
                <th>Employé</th>
                <th>Boutique</th>
                <th>Action</th>
            </tr>
            <?php foreach ($affectations as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['PRENOM_PERSONNEL'].' '.$a['NOM_PERSONNEL']) ?></td>
                    <td><?= htmlspecialchars($a['NOM_BOUTIQUE']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="retirer_id_personnel" value="<?= htmlspecialchars($a['ID_PERSONNEL']) ?>">
                            <input type="hidden" name="retirer_id_boutique"  value="<?= htmlspecialchars($a['ID_BOUTIQUE']) ?>">
                            <input type="submit" name="retirer_employe" value="Retirer">
                        </form>
                    </td>
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