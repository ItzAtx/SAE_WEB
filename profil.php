<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: index.php");
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

                    //$conn = oci_connect("anthonyvauchel", "oracle", "10.1.16.56/oracle2"); //Connexion à la BD fac
                    $conn = oci_connect("SYSTEM", "oracle", "192.168.1.3/FREE"); //Connexion à la BD locale

                    $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur depuis la session

                    //Préparation de la requête (récuperer infos sur l'utilisateur) et execution
                    $requeteP = oci_parse($conn,
                            "SELECT *
                             FROM Personnel
                             WHERE numero_personnel = :identifiant"
                    );
                    oci_bind_by_name($requeteP, ":identifiant", $id_session);
                    oci_execute($requeteP);

                    //Récupération des données depuis la BDD
                    $row = oci_fetch_array($requeteP, OCI_ASSOC+OCI_RETURN_NULLS);
                    $num = $row['NUMERO_PERSONNEL'];
                    $prenom = $row['PRENOM_PERSONNEL'];
                    $nom = $row['NOM_PERSONNEL'];
                    $date = $row['DATE_ENTREE_PERSONNEL'];
                    $identifiant = $row['IDENTIFIANT_PERSONNEL'];
                    $id_fonction = $row['ID_FONCTION'];

                    //Préparation de la requête (récuperer le nom du poste de l'utilisateur) et execution
                    $requeteP = oci_parse($conn,
                            "SELECT nom_fonction
                             FROM Fonction
                             WHERE id_fonction = :id_fonction"
                    );
                    oci_bind_by_name($requeteP, ":id_fonction", $id_fonction);
                    oci_execute($requeteP);
                    

                    //Récupération du poste
                    $row = oci_fetch_array($requeteP, OCI_ASSOC+OCI_RETURN_NULLS);
                    $fonction = $row['NOM_FONCTION'];
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
                                    $requeteP = oci_parse($conn,
                                            "UPDATE Personnel
                                            SET mdp_personnel = :nouveau
                                            WHERE numero_personnel = :id_session"
                                    );
                                    oci_bind_by_name($requeteP, ":nouveau", $nouveau);
                                    oci_bind_by_name($requeteP, ":id_session", $id_session);
                                    oci_execute($requeteP);

                                    oci_commit($conn); //Mise à jour
                                    
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

                <a href="index.php"><button>Deconnexion</button></a>
            </div>
        </div>
    </body>
</html>