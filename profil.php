<?php 
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";

    $id_session = $_SESSION['id']; //Récupération de l'id de l'utilisateur depuis la session

    //Récupération des informations de l'utilisateur
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

    //Vérifications des contraintes pour créer un nouveau mot de passe
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        if (!empty($_POST['nvmdp']) && !empty($_POST['nvmdp2'])) {
            if ($_POST['nvmdp'] == $_POST['nvmdp2']) {
                if (preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/", $_POST['nvmdp'])) {
                    $nouveau = password_hash($_POST['nvmdp'], PASSWORD_DEFAULT);

                    execQuery($conn,
                        "UPDATE Personnel
                        SET mot_de_passe = :nouveau
                        WHERE id_personnel = :id_session",
                        [":nouveau" => $nouveau, ":id_session" => $id_session]
                    );
                    oci_commit($conn);
                    $message = "<p> Mot de passe modifié ! </p>";
                } else {
                    $message = "<p class='erreur'>Le format ne correspond pas</p>";
                }
            } else {
                $message = "<p class='erreur'>Les mots de passe sont différents</p>";
            }
        } else {
            $message = "<p class='erreur'>Veuillez remplir les deux champs</p>";
        }
    }

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

                <!-- Affichage des informations de l'utilisateur -->
                <div class="info">
                    <p>Numéro de personnel : <?php echo $id_session; ?></p>
                    <p>Prénom : <?php echo $prenom; ?></p>
                    <p>Nom : <?php echo $nom; ?></p>
                    <p>Début du contrat : <?php echo $dateD; ?></p>
                    <p>Salaire : <?php echo $salaire."€"; ?></p>
                    <p>Identifiant de connexion : <?php echo $identifiant; ?></p>
                    <p>Poste : <?php echo $fonction; ?></p>
                </div>
                <a href="modification.php"><button>Modifier</button></a> <!-- Création du bouton Modifier -->
            </div>

            <div class="separator"></div>

            <div class="right">
                <h2>Sécurité</h2>
                <?php echo !empty($message) ? $message : ""; ?> <!-- Affichage du message -->

                <!-- Affichage du formulaire pour changer de mot de passe -->
                <form method="post">
                    Format : minimum 8 caractères, dont 1 majuscule, minuscule, chiffre <br> <br>
                    Nouveau mot de passe : <input type="text" name="nvmdp">
                    Confirmer : <input type="text" name="nvmdp2">
                    <input type="submit" value="Mettre à jour">
                </form>

                <a href="logout.php"><button>Deconnexion</button></a> <!-- Création du bouton Deconnexion -->
            </div>
        </div>
    </body>
</html>