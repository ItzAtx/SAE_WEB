<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: login.php");
        exit();
    } 
?>

<html>
    <head>
        <link rel="stylesheet" href="css/profil.css">
    </head>
    <body>
        <div class="container">
            <div class="left">
                <h2>Informations</h2>
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
                ?>

                <div class="info">Numéro de personnel : <?php echo $num; ?></div>
                <div class="info">Prénom : <?php echo $prenom; ?></div>
                <div class="info">Nom : <?php echo $nom; ?></div>
                <div class="info">Date d'entrée : <?php echo $date; ?></div>
                <div class="info">Salaire mensuel : <?php echo $salaire; ?></div>
                <div class="info">Identifiant de connexion : <?php echo $identifiant; ?></div>
                <div class="info">Poste : <?php echo $fonction; ?></div>
            </div>

            <div class="separator"></div>

            <div class="right">
                <h2>Sécurité</h2>

                <form method="post">
                    Nouveau mot de passe : <input type="text" name="nvmdp">
                    Confirmer : <input type="text" name="nvmdp2">
                    <input type="submit" value="Confirmer">
                </form>

                <?php

                    if (isset($_POST['nvmdp']) && isset($_POST['nvmdp2'])){
                        if ($_POST['nvmdp'] == $_POST['nvmdp2']) {
                            if (preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/", $_POST['nvmdp'])) {
                                if (!empty($_POST['nvmdp'])){
                                    $nouveau = md5($_POST['nvmdp']);

                                    $requete = "UPDATE Personnel SET mdp_personnel = '$nouveau' WHERE numero_personnel = $id";
                                    //mysqli_query($idco, $requete);
                                    echo "Mot de passe modifié !";
                                } else {
                                    echo "Veuillez saisir un nouveau mot de passe";
                                }
                            }
                            else {
                                echo "Le format ne correspond pas";
                            }
                        }
                        else {
                            echo "Les mot de passe sont différents";
                        }
                    }
                    

                    
                ?>

                <a href="login.php"><button>Deconnexion</button></a>
            </div>
        </div>
    </body>
</html>