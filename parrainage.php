<?php
include_once("fonctions.php");
requireLogin();
$conn = getConnection();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Suppression visiteur
    if (!empty($_POST['supprimer_id_visiteur'])) {
        deleteWhere($conn, 'Parrainer', 'id_visiteur', $_POST['supprimer_id_visiteur']);
        deleteWhere($conn, 'Visiteurs', 'id_visiteur', $_POST['supprimer_id_visiteur']);
        oci_commit($conn);
        $message = "Visiteur supprimé avec succès.";
    }

    //Ajout visiteur
    if (isset($_POST['ajouter_visiteur'])) {
        if (!postFieldsFilled(['nom_visiteur', 'prenom_visiteur', 'numero_telephone'])) {
            $message = "Veuillez remplir tous les champs du visiteur.";
        } else {
            $nom = $_POST['nom_visiteur'];
            $prenom = $_POST['prenom_visiteur'];
            $telephone = $_POST['numero_telephone'];

            //Vérifie que le numéro ne contient que des chiffres
            if (!preg_match('/^\d+$/', $telephone)) {
                $message = "Le numéro de téléphone ne doit contenir que des chiffres, sans espaces.";
            } else {
                $nextId = getNextId($conn, "Visiteurs", "id_visiteur");

                execQuery($conn,
                    "INSERT INTO Visiteurs VALUES (:id, :nom, :prenom, :tel)",
                    [':id' => $nextId, ':nom' => $nom, ':prenom' => $prenom, ':tel' => $telephone]
                );

                oci_commit($conn);
                $message = "Visiteur ajouté avec succès.";
            }
        }
    }

    //Suppression parrainage
    if (!empty($_POST['supprimer_parrainage'])) {
        $parts = explode('|', $_POST['supprimer_parrainage']);
        execQuery($conn,
            "DELETE FROM Parrainer WHERE RFID=:rfid AND id_visiteur=:iv AND id_prestation=:ip",
            [':rfid' => $parts[0], ':iv' => $parts[1], ':ip' => $parts[2]]
        );
        oci_commit($conn);
        $message = "Parrainage supprimé avec succès.";
    }

    //Ajout parrainage
    if (isset($_POST['ajouter_parrainage'])) {
        $fields = ['rfid_parrainage', 'id_visiteur_parrainage', 'libelle_prestation_parrainage', 'niveau_prestation_parrainage'];

        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs du parrainage.";
        } else {
            $rfid = $_POST['rfid_parrainage'];
            $idV = $_POST['id_visiteur_parrainage'];
            $libelle = $_POST['libelle_prestation_parrainage'];
            $niveau = $_POST['niveau_prestation_parrainage'];

            $prestation = fetchOne($conn,
                "SELECT id_prestation 
                FROM Prestations 
                WHERE libelle_prestation = :l AND niveau_contribution = :n",
                [':l' => $libelle, ':n' => $niveau]
            );

            if ($prestation) {
                $idP = $prestation['ID_PRESTATION'];
            } else {
                $idP = getNextId($conn, "Prestations", "id_prestation");

                execQuery(
                    $conn,
                    "INSERT INTO Prestations VALUES (:id,:lib,:niv)",
                    [':id' => $idP, ':lib' => $libelle, ':niv' => $niveau
                    ]
                );
            }

            $existe = fetchOne($conn,
                "SELECT *
                FROM Parrainer 
                WHERE RFID=:rfid AND id_visiteur=:iv AND id_prestation=:ip",
                [':rfid' => $rfid, ':iv'=> $idV, ':ip' => $idP]
            );

            if ($existe) {
                $message = "Ce parrainage existe déjà.";
            } else {
                execQuery(
                    $conn,
                    "INSERT INTO Parrainer VALUES (:rfid,:iv,:ip)",
                    [':rfid' => $rfid, ':iv' => $idV, ':ip' => $idP]
                );
                $message = "Parrainage ajouté avec succès.";
            }

            oci_commit($conn);
        }
    }
}

$visiteurs = fetchAllRows(
    $conn,
    "SELECT id_visiteur, nom_visiteur, prenom_visiteur, numero_telephone
    FROM Visiteurs"
);

$parrainages = fetchAllRows($conn,
    "SELECT PA.RFID, PA.id_visiteur, PA.id_prestation, A.nom_animal, V.nom_visiteur, V.prenom_visiteur, PR.libelle_prestation, PR.niveau_contribution
    FROM Parrainer PA, Animal A, Visiteurs V, Prestations PR
    WHERE PA.RFID = A.RFID
    AND PA.id_visiteur = V.id_visiteur
    AND PA.id_prestation = PR.id_prestation"
);

$animaux = fetchAllRows($conn,
    "SELECT A.RFID, A.nom_animal, E.nom_usuel
    FROM Animal A, Espece E
    WHERE A.nom_latin = E.nom_latin
    ORDER BY A.nom_animal"
);

$nextIdV = getNextId($conn, "Visiteurs", "id_visiteur");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Visiteurs et parrainages</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<a href="search.php"><button type="button">Retour à l'accueil</button></a>

<h2>Gestion des visiteurs</h2>

<?php if ($message): ?>
    <p><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Prénom</th>
        <th>Nom</th>
        <th>Téléphone</th>
        <th>Action</th>
    </tr>

    <?php foreach ($visiteurs as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['ID_VISITEUR']) ?></td>
        <td><?= htmlspecialchars($r['PRENOM_VISITEUR']) ?></td>
        <td><?= htmlspecialchars($r['NOM_VISITEUR']) ?></td>
        <td><?= htmlspecialchars($r['NUMERO_TELEPHONE']) ?></td>
        <td>
            <form method="post" style="display:inline">
                <input type="hidden" name="supprimer_id_visiteur" value="<?= $r['ID_VISITEUR'] ?>">
                <input type="submit" value="Supprimer">
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

    <tr>
        <form method="post">
            <td><input type="text" value="<?= htmlspecialchars($nextIdV) ?>" readonly></td>
            <td><input type="text" name="prenom_visiteur" required></td>
            <td><input type="text" name="nom_visiteur" required></td>
            <td>
                <input 
                    type="text" 
                    name="numero_telephone" 
                    required
                    pattern="[0-9]+"
                    maxlength="10"
                    title="Entrez uniquement des chiffres, sans espaces."
                >
            </td>
            <td><input type="submit" name="ajouter_visiteur" value="Ajouter"></td>
        </form>
    </tr>
</table>

<br><br>

<h2>Parrainages</h2>

<table border="1">
    <tr>
        <th>Visiteur</th>
        <th>Animal parrainé</th>
        <th>Prestation</th>
        <th>Niveau</th>
        <th>Action</th>
    </tr>

    <?php foreach ($parrainages as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['PRENOM_VISITEUR'].' '.$r['NOM_VISITEUR']) ?></td>
        <td><?= htmlspecialchars($r['NOM_ANIMAL']) ?></td>
        <td><?= htmlspecialchars($r['LIBELLE_PRESTATION']) ?></td>
        <td><?= htmlspecialchars($r['NIVEAU_CONTRIBUTION']) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="supprimer_parrainage" value="<?= htmlspecialchars($r['RFID'].'|'.$r['ID_VISITEUR'].'|'.$r['ID_PRESTATION']) ?>">
                <input type="submit" value="Supprimer">
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

    <tr>
        <form method="post">
            <td>
                <select name="id_visiteur_parrainage" required>
                    <?php foreach ($visiteurs as $v): ?>
                    <option value="<?= $v['ID_VISITEUR'] ?>">
                        <?= htmlspecialchars($v['PRENOM_VISITEUR'].' '.$v['NOM_VISITEUR']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <select name="rfid_parrainage" required>
                    <?php foreach ($animaux as $a): ?>
                    <option value="<?= $a['RFID'] ?>">
                        <?= htmlspecialchars($a['NOM_ANIMAL'].' ('.$a['NOM_USUEL'].')') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <input type="text" name="libelle_prestation_parrainage" placeholder="Libellé de la prestation" required>
            </td>

            <td>
                <select name="niveau_prestation_parrainage" required>
                    <option value="Bronze">Bronze</option>
                    <option value="Argent">Argent</option>
                    <option value="Or">Or</option>
                </select>
            </td>

            <td><input type="submit" name="ajouter_parrainage" value="Ajouter"></td>
        </form>
    </tr>
</table>

</body>
</html>