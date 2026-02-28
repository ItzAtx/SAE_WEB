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
                    echo "<p class='succes'>".$_SESSION['succes']."</p>"; //Affichage du succes
                    unset($_SESSION['succes']);
                }

                include("connex.inc.php");
                $idco = connex("myparam", "zoo"); //Connexion à la BDD

                $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur depuis la session

                //Requête préparée et execution
                $requeteP = $idco->prepare("SELECT * FROM Personnel 
                                           WHERE numero_personnel = :identifiant"
                );
                $requeteP->execute(['identifiant' => $id_session]);

                //Récupération des données depuis la BDD
                $row = $requeteP->fetch(PDO::FETCH_ASSOC);
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
                        //Récupération des données depuis le formulaire
                        $prenom = $_POST['prenom'];
                        $nom = $_POST['nom'];
                        $date = $_POST['date'];
                        $identifiant = $_POST['identifiant'];

                        //Préparation de la requête (mise à jour des données) et execution
                        $requeteP = $idco->prepare("UPDATE Personnel
                                                    SET prenom_personnel = :prenom, nom_personnel = :nom, date_entree_personnel = :date_entree, identifiant_personnel = :identifiant_connex
                                                    WHERE numero_personnel = :identifiant"
                        );
                        $requeteP->execute(['prenom' => $prenom, 'nom' => $nom, 'date_entree' => $date, 'identifiant_connex' => $identifiant, 'identifiant' => $id_session]);

                        $_SESSION['succes'] = "Informations mises à jour !";
                        header("Location: modification.php"); //On redirige l'utilisateur vers la même page à jour
                        exit();
                        
                    } else {
                        echo "<p class='erreur'>Il faut remplir tous les champs</p>"; //Affichage de l'erreur si tous les champs ne sont pas remplis
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