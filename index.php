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

                if (isset($_POST['identifiant']) && isset($_POST['mdp'])){
                    if (!empty($_POST['identifiant']) && !empty($_POST['mdp'])){
                        
                        //Récupération des données remplis par l'utilisateur
                        $id = $_POST['identifiant'];
                        $mdp = $_POST['mdp'];

                        //$conn = oci_connect("anthonyvauchel", "oracle", "10.1.16.56/oracle2"); //Connexion à la BD fac
                        $conn = oci_connect("SYSTEM", "oracle", "192.168.1.3/FREE"); //Connexion à la BD locale

                        //Préparation de la requête et execution
                        $requeteP = oci_parse($conn,
                            "SELECT numero_personnel, mdp_personnel
                             FROM Personnel
                             WHERE identifiant_personnel = :identifiant"
                        );
                        oci_bind_by_name($requeteP, ":identifiant", $id);
                        oci_execute($requeteP);

                        //Récupération des données de la BDD
                        $row = oci_fetch_array($requeteP, OCI_ASSOC+OCI_RETURN_NULLS);

                        //Si row n'est pas vide (signifie qu'il y a des données pour l'identifiant entré) et que le mot de passe est bon
                        if ($row && password_verify($mdp, $row['MDP_PERSONNEL'])){ 
                            $_SESSION['id'] = $row['NUMERO_PERSONNEL'];
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