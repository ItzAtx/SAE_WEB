<html>
    <body>
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

        <form method="post" action="">
            Identifiant : <input type="text" name="identifiant">
            <br/><br/>
            Mot de passe : <input type="password" name="mdp">
            <br/><br/>
            <input type="submit">
        </form>

        <?php   
            if ($erreur != ""){
                echo "<p>$erreur</p>";
            }
        ?>
    </body>
</html>