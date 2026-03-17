<?php 
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();
?>

<html>
    <head>
        <title>Modification</title>
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

                $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur depuis la session

                //Requête préparée, execution et récupération des données
                $row = fetchOne($conn,
                    "SELECT prenom_personnel, nom_personnel, id_connexion, TO_CHAR(date_debut,'YYYY-MM-DD') AS date_debut
                    FROM Personnel p, Contrat c
                    WHERE p.id_personnel = c.id_personnel
                    AND p.id_personnel = :identifiant",
                    [":identifiant" => $id_session]
                );

                $prenom = $row['PRENOM_PERSONNEL'];
                $nom = $row['NOM_PERSONNEL'];
                $identifiant = $row['ID_CONNEXION'];
                $date = $row['DATE_DEBUT'];
            ?>

            <form method="post">
                <label>Prénom</label> <input type="text" value="<?php echo $prenom ?>" name="prenom"> <br><br>
                <label>Nom</label> <input type="text" value="<?php echo $nom ?>" name="nom"> <br><br>
                <label>Début du contrat</label> <input type="date" value="<?php echo $date ?>" name="date"> <br><br>
                <label>Identifiant</label> <input type="text" value="<?php echo $identifiant ?>" name="identifiant"> <br><br>
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

                        //Préparation de la requête (mise à jour des données dans Personnel) et execution
                        execQuery($conn,
                            "UPDATE Personnel
                             SET prenom_personnel = :prenom, nom_personnel = :nom, id_connexion = :identifiant_connex
                             WHERE id_personnel = :identifiant",
                             [":prenom" => $prenom, ":nom" => $nom, ":identifiant_connex" => $identifiant, ":identifiant" => $id_session]
                        );

                        //Préparation de la requête (mise à jour des données dans Contrat) et execution
                        execQuery($conn,
                            "UPDATE Contrat
                             SET date_debut = TO_DATE(:date_debut, 'YYYY-MM-DD')
                             WHERE id_personnel = :identifiant",
                             [":date_debut" => $date, ":identifiant" => $id_session]
                        );
 
                        oci_commit($conn); //Mise à jour

                        $_SESSION['succes'] = "Informations mises à jour !";
                        redirectTo("modification.php"); //On redirige l'utilisateur vers la même page à jour
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