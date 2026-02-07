<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: login.php");
        exit();
    } 
?>
<html>
    <head>
        <link rel="stylesheet" href="css/dashboard.css">
    </head>

    <body>
        <div class="container">
            <h1>Dashboard</h1>
            <?php
                include("connex.inc.php");
                $idco = connex("myparam", "zoo");

                $id = $_SESSION['id'];
                $requete = "SELECT nom_personnel, prenom_personnel FROM Personnel WHERE numero_personnel = $id";
                $result = mysqli_query($idco, $requete);

                $row = mysqli_fetch_array($result);
                $nom = $row['nom_personnel'];
                $prenom = $row['prenom_personnel'];

                echo "<p class='bonjour'>Bonjour $nom $prenom </p>  ";
            ?>

            <div class="menu">
                <a href="profil.php"><button>Profil</button></a>
                <a href="employes.php"><button>Gérer employés</button></a>
                <a href="enclos.php"><button>Gérer enclos</button></a>
            </div>

            <?php
                if (isset($_GET['profil'])){
                    header("Location: profil.php");
                    exit();
                }
            ?>
        </div>
    </body>
</html>