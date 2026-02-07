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
            $requete = "SELECT * FROM Personnel WHERE numero_personnel = $id";
            $result = mysqli_query($idco, $requete);
            $row = mysqli_fetch_array($result);

            $num = $row['numero_personnel'];
            $prenom = $row['prenom_personnel'];
            $nom = $row['nom_personnel'];
            $date = $row['date_entree_personnel'];
            $salaire = $row['salaire_personnel'];
            $identifiant = $row['identifiant_personnel'];
            $id_fonction = $row['id_fonction'];

            $requete = "SELECT nom_fonction FROM Fonction WHERE id_fonction = $id_fonction";
            $result = mysqli_query($idco, $requete);
            $row = mysqli_fetch_array($result);
            $fonction = $row['nom_fonction'];

            echo "Numéro de personnel : $num <br/>";
            echo "Prénom : $prenom <br/>";
            echo "Nom : $nom <br/>";
            echo "Date d'entrée : $date <br/>";
            echo "Salaire mensuel : $salaire <br/>";
            echo "Identifiant de connexion : $identifiant <br/>";
            echo "Poste : $fonction <br/>";
        ?>

        <form method="post">
            Nouveau mot de passe : <input type="text" name="nvmdp">
            <input type="submit" value="Confirmer">
        </form>

        <?php
            if (isset($_POST['nvmdp'])){
                $nouveau = md5($_POST['nvmdp']);

                $requete = "UPDATE Personnel SET mdp_personnel = '$nouveau' WHERE numero_personnel = $id";
                mysqli_query($idco, $requete);
            }
        ?>
    </body>
</html>