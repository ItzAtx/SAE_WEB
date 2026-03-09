<?php 
    session_start();
    if (!isset($_SESSION['id'])){
        header("Location: index.php");
        exit();
    }

    $conn = oci_connect("SYSTEM", "oracle", "192.168.1.3/FREE"); //Connexion à la BDD

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['supprimer_id_personnel'])) {
        $id_personnel_suppr = $_POST['supprimer_id_personnel'];

        $requeteP = oci_parse($conn, //Suppression du contrat en premier car le contrat dépend de Personnel
            "DELETE FROM Contrat
            WHERE id_personnel = :id_personnel"
        );
        oci_bind_by_name($requeteP, ":id_personnel", $id_personnel_suppr);
        oci_execute($requeteP);

        $requeteP = oci_parse($conn, //Suppression de la personne dans Personnel
            "DELETE FROM Personnel
            WHERE id_personnel = :id_personnel"
        );
        oci_bind_by_name($requeteP, ":id_personnel", $id_personnel_suppr);
        oci_execute($requeteP);

        oci_commit($conn);

        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    //Requete pour avoir les données voulues
    $requeteP = oci_parse($conn,
        "SELECT P.id_personnel, P.prenom_personnel, P.nom_personnel, P.id_connexion, P.id_zone,
                C.id_contrat, C.salaire, TO_CHAR(C.date_debut,'YYYY-MM-DD') AS date_debut, F.fonction
        FROM Personnel P, Contrat C, Fonction F
        WHERE C.id_personnel = P.id_personnel
        AND C.id_fonction = F.id_fonction"
    );
    oci_execute($requeteP);
?>

<link rel="stylesheet" href="css/gestion.css">
<a href="search.php"><button>Accueil</button></a>
<form method="post">
<table border="1">
    <!-- Titres du tableau -->
    <tr>
        <th>ID_PERSONNEL</th>
        <th>PRENOM_PERSONNEL</th>
        <th>NOM_PERSONNEL</th>
        <th>ID_CONNEXION</th>
        <th>ID_ZONE</th>
        <th>ID_CONTRAT</th>
        <th>SALAIRE</th>
        <th>DEBUT_CONTRAT</th>
        <th>FONCTION</th>
        <th>MDP</th>
        <th>ACTION</th>
    </tr>

    <?php
    //Affichage des données
    while ($row = oci_fetch_assoc($requeteP)) {
        echo "<tr>";
        echo "<td>".$row['ID_PERSONNEL']."</td>";
        echo "<td>".$row['PRENOM_PERSONNEL']."</td>";
        echo "<td>".$row['NOM_PERSONNEL']."</td>";
        echo "<td>".$row['ID_CONNEXION']."</td>";
        echo "<td>".$row['ID_ZONE']."</td>";
        echo "<td>".$row['ID_CONTRAT']."</td>";
        echo "<td>".$row['SALAIRE']."</td>";
        echo "<td>".$row['DATE_DEBUT']."</td>";
        echo "<td>".$row['FONCTION']."</td>";
        echo "<td>**********************</td>";

        //On crée un formulaire par ligne, le input hidden prend en valeur l'id personnel de la ligne
        echo "<td>
            <form method='post''>
                <input type='hidden' name='supprimer_id_personnel' value='".$row['ID_PERSONNEL']."'>
                <input type='submit' value='Supprimer'>
            </form>
          </td>";
        echo "</tr>";
    }
    ?>

    <!-- Affichage du formulaire pour ajouter une personne -->
    <tr>
        <td><input type="text" name="id_personnel"></td>
        <td><input type="text" name="prenom_personnel"></td>
        <td><input type="text" name="nom_personnel"></td>
        <td><input type="text" name="id_connexion"></td>
        <td><input type="text" name="id_zone"></td>
        <td><input type="text" name="id_contrat"></td>
        <td><input type="text" name="salaire"></td>
        <td><input type="date" name="date_debut"></td>
        <td><input type="text" name="fonction"></td>
        <td><input type="text" name="mot_de_passe"></td>
        <td><input type="submit" value="Ajouter"></td>
    </tr>
</table>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == "POST"){
    if (
        !empty($_POST['id_personnel']) &&
        !empty($_POST['prenom_personnel']) &&
        !empty($_POST['nom_personnel']) &&
        !empty($_POST['mot_de_passe']) &&
        !empty($_POST['id_connexion']) &&
        !empty($_POST['id_zone']) &&
        !empty($_POST['id_contrat']) &&
        !empty($_POST['salaire']) &&
        !empty($_POST['date_debut']) &&
        !empty($_POST['fonction'])
    ) {
        $id_personnel = $_POST['id_personnel'];
        $prenom_personnel = $_POST['prenom_personnel'];
        $nom_personnel = $_POST['nom_personnel'];
        $mot_de_passe =  password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        $id_connexion = $_POST['id_connexion'];
        $id_zone = $_POST['id_zone'];
        $id_contrat = $_POST['id_contrat'];
        $salaire = $_POST['salaire'];
        $date_debut = $_POST['date_debut'];
        $fonction = $_POST['fonction'];

        //Ajout des données dans Personnel
        $requeteP = oci_parse($conn,
            "INSERT INTO Personnel 
             VALUES (:id_personnel, :nom_personnel, :prenom_personnel, :mot_de_passe, :id_connexion, :id_zone)"
        );
        oci_bind_by_name($requeteP, ":id_personnel", $id_personnel);
        oci_bind_by_name($requeteP, ":nom_personnel", $nom_personnel);
        oci_bind_by_name($requeteP, ":prenom_personnel", $prenom_personnel);
        oci_bind_by_name($requeteP, ":mot_de_passe", $mot_de_passe);
        oci_bind_by_name($requeteP, ":id_connexion", $id_connexion);
        oci_bind_by_name($requeteP, ":id_zone", $id_zone);
        oci_execute($requeteP);

        //On cherche l'id correspondant à la fonction
        $requeteP = oci_parse($conn,
            "SELECT id_fonction
             FROM Fonction
             WHERE fonction = :fonction"
        );
        oci_bind_by_name($requeteP, ":fonction", $fonction);
        oci_execute($requeteP);
        $row = oci_fetch_assoc($requeteP);
        $id_fonction = $row['ID_FONCTION'];

        //Ajout des données dans Contrat
        $requeteP = oci_parse($conn,
            "INSERT INTO Contrat 
             VALUES (:id_contrat, :salaire, TO_DATE(:date_debut, 'YYYY-MM-DD'), NULL, :id_fonction, :id_personnel)"
        );
        oci_bind_by_name($requeteP, ":id_contrat", $id_contrat);
        oci_bind_by_name($requeteP, ":salaire", $salaire);
        oci_bind_by_name($requeteP, ":date_debut", $date_debut);
        oci_bind_by_name($requeteP, ":id_fonction", $id_fonction);
        oci_bind_by_name($requeteP, ":id_personnel", $id_personnel);
        oci_execute($requeteP);

        oci_commit($conn);
        header("Location: gestion.php");
        exit();



    } else {
        echo "Veuillez remplir tous les champs";
    }
}
    