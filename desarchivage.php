<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";
    $confirmerDesarchivage = $_GET['confirmer_desarchivage'] ?? null; //Prend l'id du personnel pour lequel on a cliqué sur désarchiver

    /* =========
    DÉSARCHIVAGE
    =============*/
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['desarchiver_id_personnel'])) {
        $id = $_POST['desarchiver_id_personnel'];

        if (empty($_POST['nouvelle_date_debut'])) {
            $message = "Veuillez saisir une date de début de contrat.";
        } else {
            //Récupération du dernier contrat
            $dernierContrat = fetchOne($conn,
                "SELECT salaire, TO_CHAR(date_debut,'YYYY-MM-DD') AS date_debut, id_fonction
                FROM Contrat
                WHERE id_personnel = :id
                AND date_debut = (SELECT MAX(date_debut) FROM Contrat WHERE id_personnel = :id)",
                [':id' => $id]
            );

            $nextIdContrat = getNextId($conn, "Contrat", "id_contrat");

            //Création du nouveau contrat
            execQuery($conn,
                "INSERT INTO Contrat VALUES (:id_contrat, :salaire, TO_DATE(:date_debut,'YYYY-MM-DD'), NULL, :id_fonction, :id_personnel)",
                [':id_contrat' => $nextIdContrat, ':salaire' => $dernierContrat['SALAIRE'], ':date_debut' => $_POST['nouvelle_date_debut'], ':id_fonction' => $dernierContrat['ID_FONCTION'], ':id_personnel' => $id]
            );

            //Désarchivage
            execQuery($conn,
                "UPDATE Personnel SET archiver_personnel = 'N' WHERE id_personnel = :id",
                [':id' => $id]
            );

            oci_commit($conn);
            $message = "Personnel désarchivé avec succès.";
            $confirmerDesarchivage = null; 
        }
    }

    /* ===================
    SUPPRESSION DÉFINITIVE
    ======================*/
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['supprimer_id_personnel'])) {
        $id = $_POST['supprimer_id_personnel'];

        //Suppression des dépendances en cascade
        deleteWhere($conn, 'Contrat', 'id_personnel', $id);
        deleteWhere($conn, 'Specialiser', 'id_personnel', $id);
        deleteWhere($conn, 'Soins', 'id_personnel', $id);
        $repas = fetchAllRows($conn, "SELECT id_repas FROM Repas WHERE id_personnel = :id", [':id' => $id]);
        foreach ($repas as $r) {
            deleteWhere($conn, 'Contient', 'id_repas', $r['ID_REPAS']);
        }
        deleteWhere($conn, 'Repas', 'id_personnel', $id);
        deleteWhere($conn, 'Attitre', 'id_personnel', $id);
        deleteWhere($conn, 'Entretient', 'id_personnel', $id);
        deleteWhere($conn, 'Travaille', 'id_personnel', $id);
        deleteWhere($conn, 'Personnel', 'id_personnel', $id);

        oci_commit($conn);
        $message = "Personnel supprimé définitivement.";
    }

    /* =========================
    RÉCUPÉRATION DU PERSONNEL ARCHIVÉ
    ========================= */
    $archives = fetchAllRows($conn,
        "SELECT id_personnel, prenom_personnel, nom_personnel, id_connexion, fonction, libelle_zone, salaire, TO_CHAR(date_debut,'YYYY-MM-DD') AS date_debut, TO_CHAR(date_fin,'YYYY-MM-DD') AS date_fin
        FROM Vue_Personnel
        WHERE archiver_personnel = 'O'
        ORDER BY nom_personnel"
    );
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Archives du personnel</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<a href="search.php"><button type="button">Accueil</button></a>

<h2>Personnel archivé</h2>

<?php if ($message !== ""): ?>
    <p><strong><?php echo $message ?></strong></p>
<?php endif; ?>

<!-- FORMULAIRE INTERMÉDIAIRE DE DÉSARCHIVAGE -->
<?php if ($confirmerDesarchivage): ?>
    <?php
        $rowADesarchiver = fetchOne($conn,
            "SELECT P.prenom_personnel, P.nom_personnel, C.salaire, F.fonction, TO_CHAR(C.date_debut,'YYYY-MM-DD') AS date_debut
            FROM Personnel P, Contrat C, Fonction F
            WHERE P.id_personnel = C.id_personnel
            AND C.id_fonction = F.id_fonction
            AND P.id_personnel = :id
            AND C.date_debut = (SELECT MAX(date_debut) FROM Contrat WHERE id_personnel = :id)",
            [':id' => $confirmerDesarchivage]
        );
    ?>

    <p>
        Vous allez désarchiver <strong><?php echo $rowADesarchiver['PRENOM_PERSONNEL'].' '.$rowADesarchiver['NOM_PERSONNEL'] ?></strong>.<br>
        Dernier contrat : <?php echo $rowADesarchiver['FONCTION'] ?>,
        salaire de <?php echo $rowADesarchiver['SALAIRE'] ?>€,
        débuté le <?php echo $rowADesarchiver['DATE_DEBUT'] ?>.<br>
        Un nouveau contrat sera créé avec les mêmes informations, vous pourrez les modifier dans la gestion du personnel. 
        <br>
        Veuillez saisir la nouvelle date de début de contrat.
    </p>

    <form method="post">
        <input type="hidden" name="desarchiver_id_personnel" value="<?php echo $confirmerDesarchivage ?>">
        <label>Nouvelle date de début (doit être après le <?php echo $rowADesarchiver['DATE_DEBUT'] ?>) :
            <input type="date" name="nouvelle_date_debut" min="<?php echo $rowADesarchiver['DATE_DEBUT'] ?>" required>
        </label>
        <input type="submit" value="Confirmer le désarchivage">
        <a href="desarchivage.php"><button type="button">Annuler</button></a>
    </form>

<?php endif; ?>

<?php if (empty($archives)): ?>
    <p>Aucun personnel archivé.</p>
<?php else: ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Identifiant</th>
            <th>Fonction</th>
            <th>Zone</th>
            <th>Salaire</th>
            <th>Début contrat</th>
            <th>Fin contrat</th>
            <th>Action</th>
        </tr>
        <?php foreach ($archives as $row): ?>
            <tr>
                <td><?php echo $row['ID_PERSONNEL'] ?></td>
                <td><?php echo $row['PRENOM_PERSONNEL'] ?></td>
                <td><?php echo $row['NOM_PERSONNEL'] ?></td>
                <td><?php echo $row['ID_CONNEXION'] ?></td>
                <td><?php echo $row['FONCTION'] ?></td>
                <td><?php echo $row['LIBELLE_ZONE'] ?></td>
                <td><?php echo $row['SALAIRE'] ?></td>
                <td><?php echo $row['DATE_DEBUT'] ?></td>
                <td><?php echo $row['DATE_FIN'] ?? 'Non renseignée' ?></td>
                <td>
                    <!-- Désarchiver : redirige vers formulaire intermédiaire -->
                    <a href="desarchivage.php?confirmer_desarchivage=<?php echo $row['ID_PERSONNEL'] ?>">
                        <button type="button">Désarchiver</button>
                    </a>
                    <!-- Supprimer définitivement -->
                    <form method="post" style="display:inline">
                        <input type="hidden" name="supprimer_id_personnel" value="<?php echo $row['ID_PERSONNEL'] ?>">
                        <input type="submit" value="Supprimer définitivement">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>