<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: login.php");
        exit();
    } 
?>
<html>
    <body>
        <?php
            include("connex.inc.php");
            $idco = connex("myparam", "zoo");

            $id = $_SESSION['id'];
            $requete = "SELECT nom_personnel, prenom_personnel FROM Personnel WHERE id_personnel = $id";
            $result = mysqli_query($idco, $requete);

            $row = mysqli_fetch_array($result);
            $nom = $row['nom_personnel'];
            $prenom = $row['prenom_personnel'];

            echo "Bonjour $nom $prenom";
        ?>
    </body>
</html>