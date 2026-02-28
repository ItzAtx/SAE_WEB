<?php session_start(); ?>
<html>
    <head>
        <link rel="stylesheet" href="css/accueil.css">
    </head>
    <body>
        <div class="container">

        <h1>Connexion</h1>
            <?php
                include("connex.inc.php"); 
                $erreur = "";

                if (isset($_POST['identifiant']) && isset($_POST['mdp'])){
                    if (!empty($_POST['identifiant']) && !empty($_POST['mdp'])){
                        
                        //Récupération des données remplis par l'utilisateur
                        $id = $_POST['identifiant'];
                        $mdp = $_POST['mdp'];

                        $idco=connex("myparam", "zoo"); //Connexion à la BDD

                        //Préparation de la requête et execution
                        $requeteP = $idco->prepare("SELECT numero_personnel, mdp_personnel
                                                    FROM Personnel 
                                                    WHERE identifiant_personnel = :identifiant"
                        );
                        $requeteP->execute(['identifiant' => $id]);

                        //Récupération des données de la BDD
                        $row = $requeteP->fetch(PDO::FETCH_ASSOC);

                        //Si row n'est pas vide (signifie qu'il y a des données pour l'identifiant entré) et que le mot de passe est bon
                        if ($row && password_verify($mdp, $row['mdp_personnel'])){ 
                            $_SESSION['id'] = $row['numero_personnel'];
                            header("Location: search.php"); //On émmène l'utilisateur à l'accueil
                            exit();
                        } else {
                            $_SESSION['erreur'] = "Identifiant ou mot de passe incorrect"; //Erreur si données mauvaises
                            header("Location: login.php");
                            exit();
                        }

                    } else {
                        $_SESSION['erreur'] = "Veuillez remplir les deux champs"; //Erreur si un des champs n'est pas remplis
                        header("Location: login.php");
                        exit();
                    }
                }
                
            ?>

            <form method="post">
                <label>Identifiant</label>
                <input type="text" name="identifiant">
                <label>Mot de passe</label>
                <input type="password" name="mdp">
                <input type="submit" value="Entrer dans le zoo">
            </form>

            <?php   
                if (isset($_SESSION['erreur'])){
                    echo "<p class='erreur'>" . $_SESSION['erreur'] . "</p>"; //Affichage de l'erreur
                    unset($_SESSION['erreur']);
                } 
            ?>
        </div>
    </body>
</html>