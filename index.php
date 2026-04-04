<?php
    session_start();
    include_once("fonctions.php");
    $conn = getConnection();

    $erreur = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        if (!empty($_POST['identifiant']) && !empty($_POST['mdp'])){
                        
            //Récupération des données remplis par l'utilisateur
            $id = $_POST['identifiant'];
            $mdp = $_POST['mdp'];

            //Récupération de l'id_personnel et du mot de passe du personnel ayant l'id_connexion correspondant
            $row = fetchOne($conn,
                "SELECT id_personnel, mot_de_passe
                FROM Vue_Personnel
                WHERE id_connexion = :identifiant
                AND archiver_personnel = 'N'",
                [":identifiant" => $id]
            );

            //Si row n'est pas vide (signifie qu'il y a des données pour l'identifiant entré) et que le mot de passe est bon
            if ($row && password_verify($mdp, $row['MOT_DE_PASSE'])){ 
                $_SESSION['id'] = $row['ID_PERSONNEL']; //On sauvegarde l'id_personnel dans une session
                header("Location: search.php"); //On emmène l'utilisateur à l'accueil
                exit();
            } else {
                $_SESSION['erreur'] = "Identifiant ou mot de passe incorrect"; //Erreur si données mauvaises
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['erreur'] = "Veuillez remplir les deux champs"; //Erreur si un des champs n'est pas remplis
            header("Location: index.php");
            exit();
        }
    }   
?>

<html>
    <head>
        <title>Connexion</title>
        <link rel="stylesheet" href="css/index.css">
    </head>
    <body>
        <div class="container">

        <h1>Connexion</h1>

            <?php
                //AFFICHAGE DE L'ERRREUR
                if (!empty($_SESSION['erreur'])){
                    echo "<p class='erreur'>".$_SESSION['erreur']."</p>";
                    unset($_SESSION['erreur']);
                }
            ?>

            <form method="post">
                <label>Identifiant</label>
                <input type="text" name="identifiant">
                <label>Mot de passe</label>
                <input type="password" name="mdp">
                <input type="submit" value="Entrer dans le zoo">
            </form>
        </div>
    </body>
</html>