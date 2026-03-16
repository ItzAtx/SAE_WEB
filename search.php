<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
?>

<form method="get" action="gestion.php">
    <label><input type="checkbox" name="tablePersonnel" value="1"> Personnel</label>
    <label><input type="checkbox" name="tableEnclos" value="1"> Enclos</label>
    <label><input type="checkbox" name="tableBoutiques" value="1"> Boutiques</label>
    <label><input type="checkbox" name="tableAnimaux" value="1"> Animaux</label>
    <label><input type="checkbox" name="tableEspeces" value="1"> Espèces</label>
    <input type="submit" value="Gérer">
</form>

<?php
    include_once("myparam.inc.php");
    $conn = oci_connect(MYUSER, MYPASS, MYHOST); //Connexion à la BDD

    function fetchAllRows($conn, $req, $binds = []) {
        //Préparation de la requête
        $requeteP = oci_parse($conn, $req);

        //On remplace chaque paramètres par leurs valeurs
        foreach ($binds as $key => $value) {
            oci_bind_by_name($requeteP, $key, $binds[$key]);
        }
        oci_execute($requeteP);//Execution

        //On remplis le tableau par les lignes du résultat
        $rows = [];
        while ($row = oci_fetch_assoc($requeteP)) {
            $rows[] = $row;
        }

        return $rows;
    }

    $searchVal = isset($_GET["search"]) ? trim($_GET["search"]) : ""; //Prend la valeur ou chaîne vide si champs vide
    $results = [];

    if ($searchVal !== "") {
        $pattern = "%".strtolower($searchVal)."%";

        /* ========= PERSONNEL ========= */
        $rows = fetchAllRows($conn,
            "SELECT P.id_personnel, P.prenom_personnel, P.nom_personnel, P.id_connexion, Z.libelle_zone AS zone_libelle, F.fonction
            FROM Personnel P, Zone_zoo Z, Contrat C, Fonction F
            WHERE P.id_zone = Z.id_zone -- Jointures
            AND P.id_personnel = C.id_personnel
            AND C.id_fonction = F.id_fonction
            AND P.archiver_personnel = 'N'
            AND (
                LOWER(P.prenom_personnel) LIKE :pattern -- Si l'une de ces caractéristiques correspond au pattern, on prend ses infos
                OR LOWER(P.nom_personnel) LIKE :pattern
                OR LOWER(P.id_connexion) LIKE :pattern
                OR TO_CHAR(P.id_personnel) LIKE :pattern
                OR LOWER(F.fonction) LIKE :pattern
            )
            ORDER BY P.id_personnel",
            [":pattern" => $pattern]
        );

        //Stockage des résultats concernant le personnel
        foreach ($rows as $row) {
            $results[] = [
                "type" => "Personnel",
                "titre" => $row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
                "ligne1" => "ID : ".$row["ID_PERSONNEL"],
                "ligne2" => "Connexion : ".$row["ID_CONNEXION"],
                "ligne3" => "Fonction : ".$row["FONCTION"],
                "ligne4" => "Zone : ".$row["ZONE_LIBELLE"],
            ];
        }

        /* ========= ANIMAUX ========= */
        $rows = fetchAllRows($conn,
            "SELECT A.RFID, A.nom_animal, E.nom_usuel, E.nom_latin, EN.id_enclos, Z.libelle_zone AS zone_libelle, P.prenom_personnel AS prenom_soigneur, P.nom_personnel AS nom_soigneur
            FROM Animal A, Espece E, Enclos EN, Zone_zoo Z, Attitre AT, Personnel P
            WHERE A.nom_latin = E.nom_latin -- Jointures
            AND A.id_enclos = EN.id_enclos
            AND EN.id_zone = Z.id_zone
            AND A.RFID = AT.RFID
            AND AT.id_personnel = P.id_personnel
            AND P.archiver_personnel = 'N'
            AND (
                LOWER(A.nom_animal) LIKE :pattern -- Si l'une de ces caractéristiques correspond au pattern, on prend ses infos
                OR TO_CHAR(A.RFID) LIKE :pattern
                OR LOWER(E.nom_usuel) LIKE :pattern
                OR LOWER(E.nom_latin) LIKE :pattern
                OR LOWER(Z.libelle_zone) LIKE :pattern
                OR LOWER(P.prenom_personnel) LIKE :pattern
                OR LOWER(P.nom_personnel) LIKE :pattern
            )
            ORDER BY A.RFID",
            [":pattern" => $pattern]
        );

        //Stockage des résultats concernant les animaux
        foreach ($rows as $row) {
            $results[] = [
                "type" => "Animal",
                "titre" => $row["NOM_ANIMAL"],
                "ligne1" => "RFID : ".$row["RFID"],
                "ligne2" => "Espèce : ".$row["NOM_USUEL"]." (".$row["NOM_LATIN"].")",
                "ligne3" => "Enclos : ".$row["ID_ENCLOS"]." | Zone : ".$row["ZONE_LIBELLE"],
                "ligne4" => "Soigneur : ".$row["PRENOM_SOIGNEUR"]." ".$row["NOM_SOIGNEUR"],
            ];
        }

        /* ========= ESPECES ========= */
        $rows = fetchAllRows($conn,
            "SELECT E.nom_usuel, E.nom_latin, E.menace
            FROM Espece E
            WHERE LOWER(E.nom_usuel) LIKE :pattern -- Si l'une de ces caractéristiques correspond au pattern, on prend ses infos
            OR LOWER(E.nom_latin) LIKE :pattern
            ORDER BY E.nom_usuel",
            [":pattern" => $pattern]
        );

        //Stockage des résultats concernant les espèces
        foreach ($rows as $row) {
            $results[] = [
                "type" => "Espèce",
                "titre" => $row["NOM_USUEL"],
                "ligne1" => "Nom latin : ".$row["NOM_LATIN"],
                "ligne2" => "Menacée : ".($row["MENACE"] === "O" ? "Oui" : "Non"),
                "ligne3" => "",
                "ligne4" => "",
            ];
        }

        /* ========= ENCLOS ========= */
        $rows = fetchAllRows($conn,
            "SELECT E.id_enclos, E.surface, Z.libelle_zone AS zone_libelle
            FROM Enclos E, Zone_zoo Z
            WHERE E.id_zone = Z.id_zone -- Jointure
            AND (
                TO_CHAR(E.id_enclos) LIKE :pattern -- Comparaison entre les ids des enclos et le pattern
                OR LOWER(Z.libelle_zone) LIKE :pattern -- Comparaison entre les noms des enclos et le pattern
                OR EXISTS ( -- Comparaison entre les animaux dans l'enclos et le pattern
                    SELECT nom_animal
                    FROM Animal A2
                    WHERE A2.id_enclos = E.id_enclos
                    AND LOWER(A2.nom_animal) LIKE :pattern
                )
                OR EXISTS ( -- Comparaison entre les particularités des enclos et le pattern
                    SELECT id_enclos
                    FROM Possede PO, Particularite PA
                    WHERE PO.id_enclos = E.id_enclos
                    AND PO.id_particularite = PA.id_particularite
                    AND LOWER(PA.libelle_particularite) LIKE :pattern
                )
            )
            ORDER BY E.id_enclos",
            [":pattern" => $pattern]
        );

        //Stockage des résultats concernant les enclos
        foreach ($rows as $row) {
            $results[] = [
                "type" => "Enclos",
                "titre" => "Enclos ".$row["ID_ENCLOS"],
                "ligne1" => "Zone : ".$row["ZONE_LIBELLE"],
                "ligne2" => "Surface : ".$row["SURFACE"],
                "ligne3" => "",
                "ligne4" => "",
            ];
        }

        /* ========= BOUTIQUES ========= */
        $rows = fetchAllRows($conn,
            "SELECT B.id_boutique, B.nom_boutique, B.type_boutique, Z.libelle_zone AS zone_libelle, P.prenom_personnel, P.nom_personnel
            FROM Boutique B, Zone_zoo Z, Personnel P
            WHERE B.id_zone = Z.id_zone -- Jointures
            AND B.id_personnel = P.id_personnel
            AND (
                LOWER(B.nom_boutique) LIKE :pattern -- Si l'une de ces caractéristiques correspond au pattern, on prend ses infos
                OR LOWER(B.type_boutique) LIKE :pattern
                OR TO_CHAR(B.id_boutique) LIKE :pattern
                OR LOWER(Z.libelle_zone) LIKE :pattern
                OR LOWER(P.prenom_personnel) LIKE :pattern
                OR LOWER(P.nom_personnel) LIKE :pattern
            )
            ORDER BY B.id_boutique",
            [":pattern" => $pattern]
        );

        //Stockage des résultats concernant les boutiques
        foreach ($rows as $row) {
            $results[] = [
                "type" => "Boutique",
                "titre" => $row["NOM_BOUTIQUE"],
                "ligne1" => "ID : ".$row["ID_BOUTIQUE"],
                "ligne2" => "Type : ".$row["TYPE_BOUTIQUE"],
                "ligne3" => "Zone : ".$row["ZONE_LIBELLE"],
                "ligne4" => "Responsable : ".$row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
            ];
        }

        /* ========= ZONES ========= */
        $rows = fetchAllRows($conn,
            "SELECT Z.id_zone, Z.libelle_zone, P.prenom_personnel, P.nom_personnel
            FROM Zone_zoo Z, Personnel P
            WHERE Z.id_personnel = P.id_personnel -- Jointure
            AND P.archiver_personnel = 'N'
            AND (
                LOWER(Z.libelle_zone) LIKE :pattern -- Si l'une de ces caractéristiques correspond au pattern, on prend ses infos
                OR TO_CHAR(Z.id_zone) LIKE :pattern
                OR LOWER(P.prenom_personnel) LIKE :pattern
                OR LOWER(P.nom_personnel) LIKE :pattern
            )
            ORDER BY Z.id_zone",
            [":pattern" => $pattern]
        );

        //Stockage des résultats concernant les zones
        foreach ($rows as $row) {
            $results[] = [
                "type" => "Zone",
                "titre" => $row["LIBELLE_ZONE"],
                "ligne1" => "ID : " . $row["ID_ZONE"],
                "ligne2" => "Responsable : " . $row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
                "ligne3" => "",
                "ligne4" => "",
            ];
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Recherche zoo</title>
    <link rel="stylesheet" href="css/search.css">
</head>
<body>

<div class="header">
    <div class="profil">
        <a href="profil.php"><img src="images/user.png" class="user" alt="Profil"></a>
    </div>

    <div class="search">
        <form method="get">
            <input class="searchbar" name="search" placeholder="Rechercher" value="<?php echo $searchVal; ?>">
            <input type="submit" value="Chercher">
        </form>
    </div>
</div>

<div id="results">
    <?php if (count($results) === 0 && $searchVal !== ""): ?>
        <h2>Résultats</h2>
        <p>Aucun résultat trouvé.</p>
    <?php else: ?>
        <h2>Résultats</h2>
        <?php foreach ($results as $item): ?>
        <!--  Affichage de chaque éléments  -->
            <div class="item">
                <h3><?php echo $item["type"]; ?> : <?php echo $item["titre"]; ?></h3>

                <?php if ($item["ligne1"] !== ""): ?>
                    <p><?php echo $item["ligne1"]; ?></p>
                <?php endif; ?>

                <?php if ($item["ligne2"] !== ""): ?>
                    <p><?php echo $item["ligne2"]; ?></p>
                <?php endif; ?>

                <?php if ($item["ligne3"] !== ""): ?>
                    <p><?php echo $item["ligne3"]; ?></p>
                <?php endif; ?>

                <?php if ($item["ligne4"] !== ""): ?>
                    <p><?php echo $item["ligne4"]; ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>