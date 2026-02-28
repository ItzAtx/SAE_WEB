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
                <a href="search.php"><button>Accueil</button></a>
                <h2>Informations</h2>
                <?php
                    include("connex.inc.php");
                    $idco = connex("myparam", "zoo"); //Connexion à la BDD

                    $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur depuis la session

                    //Préparation de la requête (récuperer infos sur l'utilisateur) et execution
                    $requeteP = $idco->prepare("SELECT * FROM Personnel 
                                               WHERE numero_personnel = :identifiant"
                    );
                    $requeteP->execute(['identifiant' => $id_session]);

                    //Récupération des données depuis la BDD
                    $row = $requeteP->fetch(PDO::FETCH_ASSOC);
                    $num = $row['numero_personnel'];
                    $prenom = $row['prenom_personnel'];
                    $nom = $row['nom_personnel'];
                    $date = $row['date_entree_personnel'];
                    $identifiant = $row['identifiant_personnel'];
                    $id_fonction = $row['id_fonction'];

                    //Préparation de la requête (récuperer le nom du poste de l'utilisateur) et execution
                    $requeteP = $idco->prepare("SELECT nom_fonction FROM Fonction 
                                               WHERE id_fonction = :id_fonction"
                    );
                    $requeteP->execute(['id_fonction' => $id_fonction]);

                    //Récupération du poste
                    $row = $requeteP->fetch(PDO::FETCH_ASSOC);
                    $fonction = $row['nom_fonction'];
                ?>

                <div class="info">Numéro de personnel : <?php echo $num; ?></div>
                <div class="info">Prénom : <?php echo $prenom; ?></div>
                <div class="info">Nom : <?php echo $nom; ?></div>
                <div class="info">Date d'entrée : <?php echo $date; ?></div>
                <div class="info">Identifiant de connexion : <?php echo $identifiant; ?></div>
                <div class="info">Poste : <?php echo $fonction; ?></div>
                <a href="modification.php"><button>Modifier</button></a>
            </div>

            <div class="separator"></div>

            <div class="right">
                <h2>Sécurité</h2>

                <form method="post">
                    Format : minimum 8 caractères, dont 1 majuscule, minuscule, chiffre <br> <br>
                    Nouveau mot de passe : <input type="text" name="nvmdp">
                    Confirmer : <input type="text" name="nvmdp2">
                    <input type="submit" value="Mettre à jour">
                </form>

                <?php

                if ($_SERVER["REQUEST_METHOD"] == "POST"){
                    if (isset($_POST['nvmdp']) && isset($_POST['nvmdp2'])){ //2 champs remplis
                        if ($_POST['nvmdp'] == $_POST['nvmdp2']) { //Les deux mdp sont pareils
                            if (preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/", $_POST['nvmdp'])) { //Le nouveau mdp est soumis aux restrictions
                                    $nouveau = password_hash($_POST['nvmdp'], PASSWORD_DEFAULT); //On hash le nouveau mdp

                                    //Préparation de la requête (mise à jour du mdp) et execution
                                    $requeteP = $idco->prepare("UPDATE Personnel SET mdp_personnel = :nouveau 
                                                               WHERE numero_personnel = :id_session"
                                    );
                                    $requeteP->execute(['nouveau' => $nouveau, 'id_session' => $id_session]);

                                    echo "Mot de passe modifié !";
                            } else {
                                echo "<p class='erreur'> Le format ne correspond pas </p>";
                            }
                        } else {
                            echo "<p class='erreur'> Les mot de passe sont différents </p>";
                        }
                    } else {
                        echo "<p class='erreur'> Veuillez remplir les deux champs </p>";
                    }
                }

                    
                ?>

                <a href="login.php"><button>Deconnexion</button></a>
            </div>
        </div>
    </body>
</html>