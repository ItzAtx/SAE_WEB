<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: login.php");
        exit();
    } 
?>

<html>
    <head>
        <link rel="stylesheet" href="css/modification.css">
    </head>
    <body>
        <div class="container">
            <h3> Modifier les données </h3>
            <?php
                if (isset($_SESSION['succes'])){
                    echo "<p class='succes'>".$_SESSION['succes']."</p>";
                    unset($_SESSION['succes']);
                }
                include("connex.inc.php");
                $idco = connex("myparam", "zoo");

                $id = $_SESSION['id'];
                $requete = "SELECT * FROM Personnel WHERE numero_personnel = $id";
                $result = mysqli_query($idco, $requete);
                $row = mysqli_fetch_array($result);

                $prenom = $row['prenom_personnel'];
                $nom = $row['nom_personnel'];
                $date = $row['date_entree_personnel'];
                $identifiant = $row['identifiant_personnel'];
            ?>

            <form method="post">
                <label>Prénom</label> <input type="textarea" value="<?php echo $prenom ?>" name="prenom"> <br><br>
                <label>Nom</label> <input type="textarea" value="<?php echo $nom ?>" name="nom"> <br><br>
                <label>Date d'arrivée</label> <input type="date" value="<?php echo $date ?>" name="date"> <br><br>
                <label>Identifiant</label> <input type="textarea" value="<?php echo $identifiant ?>" name="identifiant"> <br><br>
                <input type="submit" value="Mettre à jour">
            </form>

            <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST"){
                    if (!empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['date']) && !empty($_POST['identifiant'])){
                        $prenom = $_POST['prenom'];
                        $nom = $_POST['nom'];
                        $date = $_POST['date'];
                        $identifiant = $_POST['identifiant'];

                        $requete="UPDATE Personnel SET prenom_personnel = '$prenom', nom_personnel ='$nom', date_entree_personnel = '$date', identifiant_personnel = '$identifiant' WHERE numero_personnel = $id";
                        $result = mysqli_query($idco, $requete);
                        $_SESSION['succes'] = "Informations mises à jour !";
                        header("Location: modification.php");
                        exit();
                        
                    } else {
                        echo "<p class='erreur'>Il faut remplir tous les champs</p>";
                    }
                }
                
            ?>

            <div class="actions">
                <a href="profil.php">
                    <button>Profil</button>
                </a>
            </div>
        </div>
    </body>
</html>