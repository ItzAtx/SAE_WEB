<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: index.php");
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

                include_once("myparam.inc.php");
                $conn = oci_connect(MYUSER, MYPASS, MYHOST); //Connexion à la BDD

                $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur depuis la session

                //Requête préparée et execution 
                $requeteP = oci_parse($conn,
                            "SELECT prenom_personnel, nom_personnel, id_connexion, TO_CHAR(date_debut,'YYYY-MM-DD') AS date_debut
                             FROM Personnel p, Contrat c
                             WHERE p.id_personnel = c.id_personnel
                             AND p.id_personnel = :identifiant"
                        );
                oci_bind_by_name($requeteP, ":identifiant", $id_session);
                oci_execute($requeteP);

                //Récupération des données depuis la BDD
                $row = oci_fetch_array($requeteP, OCI_ASSOC);
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
                        $requeteP = oci_parse($conn,
                            "UPDATE Personnel
                             SET prenom_personnel = :prenom, nom_personnel = :nom, id_connexion = :identifiant_connex
                             WHERE id_personnel = :identifiant"
                        );
                        oci_bind_by_name($requeteP, ":prenom", $prenom);
                        oci_bind_by_name($requeteP, ":nom", $nom);
                        oci_bind_by_name($requeteP, ":identifiant_connex", $identifiant);
                        oci_bind_by_name($requeteP, ":identifiant", $id_session);
                        oci_execute($requeteP);

                        //Préparation de la requête (mise à jour des données dans Contrat) et execution
                        $requeteP = oci_parse($conn,
                            "UPDATE Contrat
                             SET date_debut = TO_DATE(:date_debut, 'YYYY-MM-DD')
                             WHERE id_personnel = :identifiant"
                        );
                        oci_bind_by_name($requeteP, ":date_debut", $date);
                        oci_bind_by_name($requeteP, ":identifiant", $id_session);
                        oci_execute($requeteP);
                        
                        oci_commit($conn); //Mise à jour

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