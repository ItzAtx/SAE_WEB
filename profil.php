<?php 
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();
?>

<html>
    <head>
        <title>Profil</title>
        <link rel="stylesheet" href="css/profil.css">
    </head>
    <body>
        <div class="container">
            <div class="left">
                <a href="search.php"><button>Accueil</button></a>
                <h2>Informations</h2>
                <?php

                    $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur depuis la session

                    //Préparation de la requête, execution et récupération des données
                    $row = fetchOne($conn,
                            "SELECT prenom_personnel, nom_personnel, id_connexion, salaire, date_debut, fonction
                             FROM Vue_Personnel
                             WHERE id_personnel = :identifiant",
                             [":identifiant" => $id_session]
                    );

                    $prenom = $row['PRENOM_PERSONNEL'];
                    $nom = $row['NOM_PERSONNEL'];
                    $identifiant = $row['ID_CONNEXION'];
                    $salaire = $row['SALAIRE'];
                    $dateD = $row['DATE_DEBUT'];
                    $fonction = $row['FONCTION'];
                ?>

                <div class="info">Numéro de personnel : <?php echo $id_session; ?></div>
                <div class="info">Prénom : <?php echo $prenom; ?></div>
                <div class="info">Nom : <?php echo $nom; ?></div>
                <div class="info">Début du contrat : <?php echo $dateD; ?></div>
                <div class="info">Salaire : <?php echo $salaire."€"; ?></div>
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
                                    $requeteP = execQuery($conn,
                                            "UPDATE Personnel
                                            SET mot_de_passe = :nouveau
                                            WHERE id_personnel = :id_session",
                                            [":nouveau" => $nouveau, ":id_session" => $id_session]
                                    );
                                    oci_commit($conn);
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

                <a href="logout.php"><button>Deconnexion</button></a>
            </div>
        </div>
    </body>
</html>