<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: login.php");
        exit();
    } 
?>

<html>
    <h3> Modifier les données </h3>
    <?php
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
        if (isset($_POST['prenom']) && isset($_POST['nom']) && isset($_POST['date']) && isset($_POST['identifiant'])){
            $prenom = $_POST['prenom'];
            $nom = $_POST['nom'];
            $date = $_POST['date'];
            $identifiant = $_POST['identifiant'];

            $requete="UPDATE Personnel SET prenom_personnel = '$prenom', nom_personnel ='$nom', date_entree_personnel = '$date', identifiant_personnel = '$identifiant' WHERE numero_personnel = $id";
            $result = mysqli_query($idco, $requete);
        } else {
            echo "<p>Il faut remplir tous les champs</p>";
        }
    ?>

    <a href="profil.php"><button>Retour au profil</button></a>
</html>