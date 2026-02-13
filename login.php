<?php session_start(); 
error_reporting(E_ALL);
ini_set('display_errors', 1); // Gestion des erreurs
?>
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
                        
                        $id = $_POST['identifiant'];
                        $mdp = $_POST['mdp'];
                        $mdp_hash = md5($mdp);

                        $requete="SELECT numero_personnel FROM Personnel WHERE identifiant_personnel = '$id' AND mdp_personnel = '$mdp_hash'";

                        $idco=connex("myparam", "zoo");
                        $result = mysqli_query($idco, $requete);

                        if ($row = mysqli_fetch_array($result)){
                            $_SESSION['id'] = $row['numero_personnel'];
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $_SESSION['erreur'] = "Identifiant ou mot de passe incorrect";
                            header("Location: login.php");
                            exit();
                        }

                    } else {
                        $_SESSION['erreur'] = "Veuillez remplir les deux champs";
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
                    echo "<p class='erreur'>" . $_SESSION['erreur'] . "</p>";
                    unset($_SESSION['erreur']);
                } 

            ?>
        </div>
    </body>
</html>