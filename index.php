<?php session_start(); ?>
<html>
    <head>
        <link rel="stylesheet" href="css/index.css">
    </head>
    <body>
        <div class="container">

        <h1>Connexion</h1>
            <?php
                $erreur = "";

                if ($_SERVER["REQUEST_METHOD"] == "POST"){
                    if (!empty($_POST['identifiant']) && !empty($_POST['mdp'])){
                        
                        //Récupération des données remplis par l'utilisateur
                        $id = $_POST['identifiant'];
                        $mdp = $_POST['mdp'];

                        include_once("myparam.inc.php");
                        $conn = oci_connect(MYUSER, MYPASS, MYHOST); //Connexion à la BDD

                        //Préparation de la requête et execution
                        $requeteP = oci_parse($conn,
                            "SELECT id_personnel, mot_de_passe
                            FROM Personnel
                            WHERE id_connexion = :identifiant"
                        );
                        oci_bind_by_name($requeteP, ":identifiant", $id);
                        oci_execute($requeteP);

                        //Récupération des données de la BDD
                        $row = oci_fetch_array($requeteP, OCI_ASSOC);

                        //Si row n'est pas vide (signifie qu'il y a des données pour l'identifiant entré) et que le mot de passe est bon
                        if ($row && password_verify($mdp, $row['MOT_DE_PASSE'])){ 
                            $_SESSION['id'] = $row['ID_PERSONNEL'];
                            header("Location: search.php"); //On émmène l'utilisateur à l'accueil
                            exit();
                        } else {
                            $_SESSION['erreur'] = "Identifiant ou mot de passe incorrect"; //Erreur si données mauvaises
                            header("Location: index.php");
                            exit();
                        }

                    } else {
                        $_SESSION['erreur'] = "Veuillez remplir les deux champs"; //Erreur si un des champs n'est pas remplis
                        header("Location: index.php");
                        exit();
                    }
                }
                    
            ?>

            <form method="post">
                <label>Identifiant</label>
                <input type="text" name="identifiant">
                <label>Mot de passe</label>
                <input type="password" name="mdp">
                <input type="submit" value="Entrer dans le zoo">
            </form>

            <?php   
                if (isset($_SESSION['erreur'])){
                    echo "<p class='erreur'>" . $_SESSION['erreur'] . "</p>"; //Affichage de l'erreur
                    unset($_SESSION['erreur']);
                } 
            ?>
        </div>
    </body>
</html>