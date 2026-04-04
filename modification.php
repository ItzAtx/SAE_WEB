<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $succes = "";
    $erreur = "";

    if (!empty($_SESSION['succes'])) {
        $succes = $_SESSION['succes'];
        unset($_SESSION['succes']);
    }

    $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur

    //Récupération des informations de l'utilisateur
    $row = fetchOne($conn,
        "SELECT prenom_personnel, nom_personnel, id_connexion, TO_CHAR(date_debut,'YYYY-MM-DD') AS date_debut
        FROM Vue_Personnel
        WHERE id_personnel = :identifiant",
        [":identifiant" => $id_session]
    );

    $prenom = $row['PRENOM_PERSONNEL'];
    $nom = $row['NOM_PERSONNEL'];
    $identifiant = $row['ID_CONNEXION'];
    $date = $row['DATE_DEBUT'];


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['date']) && !empty($_POST['identifiant'])) {
            $prenom = $_POST['prenom'];
            $nom = $_POST['nom'];
            $date = $_POST['date'];
            $identifiant = $_POST['identifiant'];

            //Mise à jour des informations avec les nouvelles valeurs
            execQuery($conn,
                "UPDATE Personnel
                SET prenom_personnel = :prenom, nom_personnel = :nom, id_connexion = :identifiant_connex
                WHERE id_personnel = :identifiant",
                [":prenom" => $prenom, ":nom" => $nom, ":identifiant_connex" => $identifiant, ":identifiant" => $id_session]
            );

            execQuery($conn,
                "UPDATE Contrat
                SET date_debut = TO_DATE(:date_debut, 'YYYY-MM-DD')
                WHERE id_personnel = :identifiant
                AND date_fin IS NULL",
                [":date_debut" => $date, ":identifiant" => $id_session]
            );

            oci_commit($conn);

            $_SESSION['succes'] = "Informations mises à jour !";
            redirectTo("modification.php");
            exit();

        } else {
            $erreur = "Il faut remplir tous les champs";
        }
    }
?>

<html>
    <head>
        <title>Modification</title>
        <link rel="stylesheet" href="css/modification.css">
    </head>
    <body>
        <div class="container">
            <h3>Modifier les données</h3>

            <?php if ($succes !== ""): ?>
                <p class='succes'><?php echo $succes; ?></p>
            <?php endif; ?>

            <?php if ($erreur !== ""): ?>
                <p class='erreur'><?php echo $erreur; ?></p>
            <?php endif; ?>

            <!-- Affichage des informations -->
            <form method="post">
                <label>Prénom</label> <input type="text" value="<?php echo $prenom ?>" name="prenom"> <br><br>
                <label>Nom</label> <input type="text" value="<?php echo $nom ?>" name="nom"> <br><br>
                <label>Début du contrat</label> <input type="date" value="<?php echo $date ?>" name="date"> <br><br>
                <label>Identifiant</label> <input type="text" value="<?php echo $identifiant ?>" name="identifiant"> <br><br>
                <input type="submit" value="Mettre à jour">
            </form>

            <div class="actions">
                <a href="profil.php">
                    <button>Profil</button>
                </a>
            </div>
        </div>
    </body>
</html>