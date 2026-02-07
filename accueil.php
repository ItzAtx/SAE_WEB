<html>
    <head>
        <link rel="stylesheet" href="css/accueil.css">
    </head>
    <body>
        <div class="container">

        <h1>Connexion Zoo</h1>
            <?php
                include("connex.inc.php");
                $erreur = "";
                if (isset($_POST['identifiant']) && isset($_POST['mdp'])){
                    if (!empty($_POST['identifiant']) && !empty($_POST['mdp'])){
                        
                        $id = $_POST['identifiant'];
                        $mdp = $_POST['mdp'];

                        $requete="SELECT prenom_personnel FROM Personnel WHERE identifiant_personnel = '$id' AND mdp_personnel = '$mdp'";

                        $idco=connex("myparam", "zoo");
                        $result = mysqli_query($idco, $requete);

                        if (mysqli_num_rows($result) > 0){
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $erreur = "Identifiant ou mot de passe incorrect";
                        }

                    } else {
                        $erreur = "Veuillez remplir les deux champs <br>";
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
                if ($erreur != ""){
                    echo "<p class='erreur'>$erreur</p>";
                }
            ?>
        </div>
    </body>
</html>