<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $rowPoste = fetchOne($conn,
        "SELECT F.fonction
        FROM Fonction F, Personnel P, Contrat C
        WHERE P.id_personnel = C.id_personnel
        AND F.id_fonction = C.id_fonction
        AND P.id_personnel = :id",
        [":id" => $_SESSION['id']]
    );
    $poste = $rowPoste['FONCTION'];

    //Valeur tapée dans la barre de recherche
    $searchVal = isset($_GET["search"]) ? trim($_GET["search"]) : "";

    //Tableau qui contiendra tous les résultats à afficher
    $results = [];

    //Si l'utilisateur arrive sur la page pour la première fois, on affiche tout
    //Sinon on affiche ce qu'il a coché
    $show = [
        "filtrePersonnel" => !isset($_GET["search"]) || isset($_GET["filtrePersonnel"]),
        "filtreAnimaux" => !isset($_GET["search"]) || isset($_GET["filtreAnimaux"]),
        "filtreEspeces" => !isset($_GET["search"]) || isset($_GET["filtreEspeces"]),
        "filtreEnclos" => !isset($_GET["search"]) || isset($_GET["filtreEnclos"]),
        "filtreBoutiques" => !isset($_GET["search"]) || isset($_GET["filtreBoutiques"]),
        "filtreZones" => !isset($_GET["search"]) || isset($_GET["filtreZones"]),
        "filtreContrats" => !isset($_GET["search"]) || isset($_GET["filtreContrats"]),
    ];

    //Filtre archivage du personnel
    $archive = isset($_GET["archive"]) ? $_GET["archive"] : "actifs"; //Si a une valeur, on la prend, sinon on laisse en non-archivé par défaut

    //Filtre espèce menacée
    $menace = isset($_GET["menace"]);

    //Filtre surface des enclos
    $surfaceMin = isset($_GET["surface_min"]) && $_GET["surface_min"] !== "" ? (float)$_GET["surface_min"] : null; //S'il y a une valeur on la prend sinon on met null
    $surfaceMax = isset($_GET["surface_max"]) && $_GET["surface_max"] !== "" ? (float)$_GET["surface_max"] : null;

    $aCherche = $searchVal !== "";
    $pattern = "%".strtolower($searchVal)."%";

    /* ========= PERSONNEL ========= */
    if ($show["filtrePersonnel"]) { //Si filtre Personnel actif

        //Filtre d'archivage
        if ($archive === "actifs") {
            $whereArchive = "AND P.archiver_personnel = 'N'"; //Seulement les actifs
        } elseif ($archive === "archives") {
            $whereArchive = "AND P.archiver_personnel = 'O'"; //Seulement les archivés
        } else {
            $whereArchive = ""; //Actifs + archivés
        }

        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors
        $whereSearch = $aCherche ? "AND (
            LOWER(P.prenom_personnel) LIKE :pattern
            OR LOWER(P.nom_personnel) LIKE :pattern
            OR LOWER(P.id_connexion) LIKE :pattern
            OR TO_CHAR(P.id_personnel) LIKE :pattern
            OR LOWER(F.fonction) LIKE :pattern
        )" : "";

        $rows = fetchAllRows($conn,
            "SELECT P.id_personnel, P.prenom_personnel, P.nom_personnel, P.id_connexion, P.archiver_personnel, Z.libelle_zone, F.fonction
            FROM Personnel P, Zone_zoo Z, Contrat C, Fonction F
            WHERE P.id_zone = Z.id_zone
            AND P.id_personnel = C.id_personnel
            AND C.id_fonction = F.id_fonction
            $whereArchive
            $whereSearch
            ORDER BY P.id_personnel",
            $aCherche ? [":pattern" => $pattern] : []
        );

        foreach ($rows as $row) {
            //Ajoute "[Archivé]" dans le titre si le personnel est archivé
            $estArchive = $row["ARCHIVER_PERSONNEL"] === "O" ? " [Archivé]" : "";
            $results[] = [
                "type" => "Personnel",
                "titre" => $row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"].$estArchive,
                "ligne1" => "ID : ".$row["ID_PERSONNEL"],
                "ligne2" => "Connexion : ".$row["ID_CONNEXION"],
                "ligne3" => "Fonction : ".$row["FONCTION"],
                "ligne4" => "Zone : ".$row["LIBELLE_ZONE"],
            ];
        }
    }

    /* ========= ANIMAUX ========= */
    if ($show["filtreAnimaux"]) { //Si filtre Animal actif

        //Filtre d'espece menacée
        $whereMenace = $menace ? "AND E.menace = 'O'" : "";

        $whereSearch = $aCherche ? "AND (
            LOWER(A.nom_animal) LIKE :pattern
            OR TO_CHAR(A.RFID) LIKE :pattern
            OR LOWER(E.nom_usuel) LIKE :pattern
            OR LOWER(E.nom_latin) LIKE :pattern
            OR LOWER(Z.libelle_zone) LIKE :pattern
            OR LOWER(P.prenom_personnel) LIKE :pattern
            OR LOWER(P.nom_personnel) LIKE :pattern
        )" : "";

        $rows = fetchAllRows($conn,
            "SELECT A.RFID, A.nom_animal, E.nom_usuel, E.nom_latin, EN.id_enclos, Z.libelle_zone AS zone_libelle, P.prenom_personnel AS prenom_soigneur, P.nom_personnel AS nom_soigneur
            FROM Animal A, Espece E, Enclos EN, Zone_zoo Z, Attitre AT, Personnel P
            WHERE A.nom_latin = E.nom_latin
            AND A.id_enclos = EN.id_enclos
            AND EN.id_zone = Z.id_zone
            AND A.RFID = AT.RFID
            AND AT.id_personnel = P.id_personnel
            AND P.archiver_personnel = 'N'
            $whereMenace
            $whereSearch
            ORDER BY A.RFID",
            $aCherche ? [":pattern" => $pattern] : []
        );

        foreach ($rows as $row) {
            $results[] = [
                "type"   => "Animal",
                "titre"  => $row["NOM_ANIMAL"],
                "ligne1" => "RFID : ".$row["RFID"],
                "ligne2" => "Espèce : ".$row["NOM_USUEL"]." (".$row["NOM_LATIN"].")",
                "ligne3" => "Enclos : ".$row["ID_ENCLOS"]." | Zone : ".$row["ZONE_LIBELLE"],
                "ligne4" => "Soigneur : ".$row["PRENOM_SOIGNEUR"]." ".$row["NOM_SOIGNEUR"],
            ];
        }
    }

    /* ========= ESPECES ========= */
    if ($show["filtreEspeces"]) { //Si filtre Espece actif

        //Filtre d'espece menacée
        $whereMenace = $menace ? "AND E.menace = 'O'" : "";

        $whereSearch = $aCherche
            ? "AND (LOWER(E.nom_usuel) LIKE :pattern OR LOWER(E.nom_latin) LIKE :pattern)"
            : "";

        $rows = fetchAllRows($conn,
            "SELECT E.nom_usuel, E.nom_latin, E.menace
            FROM Espece E
            WHERE 1=1 --Toujours vrai, simplement pour utiliser le WHERE
            $whereMenace
            $whereSearch
            ORDER BY E.nom_usuel",
            $aCherche ? [":pattern" => $pattern] : []
        );

        foreach ($rows as $row) {
            $results[] = [
                "type"   => "Espèce",
                "titre"  => $row["NOM_USUEL"],
                "ligne1" => "Nom latin : ".$row["NOM_LATIN"],
                "ligne2" => "Menacée : ".($row["MENACE"] === "O" ? "Oui" : "Non"),
                "ligne3" => "",
                "ligne4" => "",
            ];
        }
    }

    /* ========= ENCLOS ========= */
    if ($show["filtreEnclos"]) { //Si filtre Enclos actif

        //Filtre sur la surface
        $whereSurface = "";
        $paramsEnclos = [];

        if ($surfaceMin !== null) {
            $whereSurface = " AND E.surface >= :surface_min"; 
            $paramsEnclos[":surface_min"] = $surfaceMin;
        }
        if ($surfaceMax !== null) {
            $whereSurface .= " AND E.surface <= :surface_max"; //On concatène au cas où s'il y a une surface min
            $paramsEnclos[":surface_max"] = $surfaceMax;
        }

        $whereSearch = $aCherche ? "AND (
            TO_CHAR(E.id_enclos) LIKE :pattern
            OR LOWER(Z.libelle_zone) LIKE :pattern
            OR EXISTS (
                SELECT nom_animal FROM Animal A2
                WHERE A2.id_enclos = E.id_enclos
                AND LOWER(A2.nom_animal) LIKE :pattern
            )
            OR EXISTS (
                SELECT id_enclos FROM Possede PO, Particularite PA
                WHERE PO.id_enclos = E.id_enclos
                AND PO.id_particularite = PA.id_particularite
                AND LOWER(PA.libelle_particularite) LIKE :pattern
            )
        )" : "";

        //On ajoute :pattern aux paramètres seulement si la recherche texte est active
        if ($aCherche) {
            $paramsEnclos[":pattern"] = $pattern;
        }

        $rows = fetchAllRows($conn,
            "SELECT E.id_enclos, E.surface, Z.libelle_zone AS zone_libelle
            FROM Enclos E, Zone_zoo Z
            WHERE E.id_zone = Z.id_zone
            $whereSurface
            $whereSearch
            ORDER BY E.id_enclos",
            $paramsEnclos
        );

        foreach ($rows as $row) {
            $results[] = [
                "type"   => "Enclos",
                "titre"  => "Enclos ".$row["ID_ENCLOS"],
                "ligne1" => "Zone : ".$row["ZONE_LIBELLE"],
                "ligne2" => "Surface : ".$row["SURFACE"]." m²",
                "ligne3" => "",
                "ligne4" => "",
            ];
        }
    }

    /* ========= BOUTIQUES ========= */
    if ($show["filtreBoutiques"]) { //Si filtre Boutique actif

        $whereSearch = $aCherche ? "AND (
            LOWER(B.nom_boutique) LIKE :pattern
            OR LOWER(B.type_boutique) LIKE :pattern
            OR TO_CHAR(B.id_boutique) LIKE :pattern
            OR LOWER(Z.libelle_zone) LIKE :pattern
        )" : "";

        $rows = fetchAllRows($conn,
            "SELECT B.id_boutique, B.nom_boutique, B.type_boutique, Z.libelle_zone AS zone_libelle, P.prenom_personnel, P.nom_personnel
            FROM Boutique B, Zone_zoo Z, Personnel P
            WHERE B.id_zone = Z.id_zone
            AND B.id_personnel = P.id_personnel
            $whereSearch
            ORDER BY B.id_boutique",
            $aCherche ? [":pattern" => $pattern] : []
        );

        foreach ($rows as $row) {
            $results[] = [
                "type"   => "Boutique",
                "titre"  => $row["NOM_BOUTIQUE"],
                "ligne1" => "ID : ".$row["ID_BOUTIQUE"],
                "ligne2" => "Type : ".$row["TYPE_BOUTIQUE"],
                "ligne3" => "Zone : ".$row["ZONE_LIBELLE"],
                "ligne4" => "Responsable : ".$row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
            ];
        }
    }

    /* ========= ZONES ========= */
    if ($show["filtreZones"]) { //Si filtre Zone actif

        $whereSearch = $aCherche ? "AND (
            LOWER(Z.libelle_zone) LIKE :pattern
            OR TO_CHAR(Z.id_zone) LIKE :pattern
        )" : "";

        $rows = fetchAllRows($conn,
            "SELECT Z.id_zone, Z.libelle_zone, P.prenom_personnel, P.nom_personnel
            FROM Zone_zoo Z, Personnel P
            WHERE Z.id_personnel = P.id_personnel
            AND P.archiver_personnel = 'N'
            $whereSearch
            ORDER BY Z.id_zone",
            $aCherche ? [":pattern" => $pattern] : []
        );

        foreach ($rows as $row) {
            $results[] = [
                "type"   => "Zone",
                "titre"  => $row["LIBELLE_ZONE"],
                "ligne1" => "ID : ".$row["ID_ZONE"],
                "ligne2" => "Responsable : ".$row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
                "ligne3" => "",
                "ligne4" => "",
            ];
        }
    }

    /* ========= CONTRATS ========= */
    if ($show["filtreContrats"]) { //Si filtre Contrat actif

        $whereSearch = $aCherche ? "AND (
            TO_CHAR(C.id_contrat) LIKE :pattern
            OR LOWER(P.prenom_personnel) LIKE :pattern
            OR LOWER(P.nom_personnel) LIKE :pattern
            OR LOWER(F.fonction) LIKE :pattern
        )" : "";

        $rows = fetchAllRows($conn,
            "SELECT C.id_contrat, C.salaire, TO_CHAR(C.date_debut, 'DD/MM/YYYY') AS date_debut, P.prenom_personnel, P.nom_personnel, F.fonction
            FROM Contrat C, Personnel P, Fonction F
            WHERE C.id_personnel = P.id_personnel
            AND C.id_fonction = F.id_fonction
            $whereSearch
            ORDER BY C.id_contrat",
            $aCherche ? [":pattern" => $pattern] : []
        );

        foreach ($rows as $row) {
            $results[] = [
                "type"   => "Contrat",
                "titre"  => $row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
                "ligne1" => "ID contrat : ".$row["ID_CONTRAT"],
                "ligne2" => "Fonction : ".$row["FONCTION"],
                "ligne3" => "Salaire : ".$row["SALAIRE"]." €",
                "ligne4" => "Date de début : ".$row["DATE_DEBUT"],
            ];
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Accueil</title>
        <link rel="stylesheet" href="css/search.css">
    </head>
    <body>
        <!-- Formulaire séparé pour la gestion des tables (action différente : gestion.php) -->
        <div class="nav-links">
            <?php if ($poste === "Directeur"): ?>
                <form method="get" action="gestion.php">
                    <label><input type="checkbox" name="tablePersonnel" value="1"> Personnel</label>
                    <label><input type="checkbox" name="tableEnclos"    value="1"> Enclos</label>
                    <label><input type="checkbox" name="tableBoutiques" value="1"> Boutiques</label>
                    <label><input type="checkbox" name="tableAnimaux"   value="1"> Animaux</label>
                    <label><input type="checkbox" name="tableEspeces"   value="1"> Espèces</label>
                    <input type="submit" value="Gérer">
                </form>
            <?php elseif ($poste === "Soigneur"): ?>
                <a href="gestion.php?tableAnimaux=1&tableEspeces=1"><button>Gérer</button></a>
            <?php else: ?>
                <a href="gestion.php?tableBoutiques=1"><button>Gérer</button></a>
            <?php endif; ?>

            <?php
                if ($poste === "Soigneur" || $poste === "Directeur"){
                    echo '<a href="soins.php"><button>Soins</button></a>';
                }
                if ($poste === "Comptable" || $poste === "Directeur de magasin" || $poste === "Directeur"){
                    echo '<a href="comptes.php"><button>Comptes</button></a>';
                }
            ?>

        </div>

        <div class="header">
            <div class="profil">
                <a href="profil.php"><img src="images/user.png" class="user" alt="Profil"></a>
            </div>

            <div class="search">
                <!--
                    Paramètres envoyés dans l'URL :
                    - search : valeur de la barre
                    - filtreXxx : cases cochées (présentes = cochées, absentes = décochées)
                    - archive : valeur du select archivage
                    - menace : présent si la case espèces menacées est cochée
                    - surface_min : valeur minimale de surface
                    - surface_max : valeur maximale de surface
                -->
                <form method="get">
                    <input class="searchbar" name="search" placeholder="Rechercher"
                        value="<?php echo htmlspecialchars($searchVal); ?>">
                    <input type="submit" value="Chercher">

                    <!-- Filtres tables : une checkbox par table -->
                    <div class="search-filtres">
                        <?php
                            $filtreLabels = [
                                "filtrePersonnel" => "Personnel",
                                "filtreAnimaux"   => "Animaux",
                                "filtreEspeces"   => "Espèces",
                                "filtreEnclos"    => "Enclos",
                                "filtreBoutiques" => "Boutiques",
                                "filtreZones"     => "Zones",
                                "filtreContrats"  => "Contrats",
                            ];
                            foreach ($filtreLabels as $key => $label) {
                                $checked = $show[$key] ? "checked" : "";
                                echo '<label>';
                                echo '<input type="checkbox" name="'.$key.'" value="1" '.$checked.'>';
                                echo $label;
                                echo '</label>';
                                echo '<br>';
                            }
                        ?>
                    </div>

                    <div class="search-filtres-avancés">

                        <!-- Filtre archivage -->
                        <div class="filtre-group">
                            <span class="filtre-group-label">Personnel :</span>
                            <select name="archive">
                                <option value="actifs" <?php echo $archive === "actifs" ? "selected" : ""; ?>> Actifs seulement </option>
                                <option value="archives" <?php echo $archive === "archives" ? "selected" : ""; ?>> Archivés seulement </option>
                                <option value="tous" <?php echo $archive === "tous" ? "selected" : ""; ?>> Tous</option>
                            </select>
                            <br>
                        </div>

                        <!-- Filtre espèces menacées -->
                        <div class="filtre-group">
                            <label>
                                <input type="checkbox" name="menace" value="1"
                                    <?php echo $menace ? "checked" : ""; ?>>
                                Espèces menacées uniquement
                            </label>
                            <br>
                        </div>

                        <!-- Filtre surface  -->
                        <div class="filtre-group">
                            <span class="filtre-group-label">Surface enclos :</span><br>
                            <input type="number" name="surface_min" placeholder="Min" value="<?php echo $surfaceMin !== null ? htmlspecialchars($surfaceMin) : ""; ?>">
                            <br>
                            <input type="number" name="surface_max" placeholder="Max" value="<?php echo $surfaceMax !== null ? htmlspecialchars($surfaceMax) : ""; ?>">
                            <br>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Zone d'affichage des résultats -->
        <div id="results">
            <h2>Résultats</h2>
            <?php if (count($results) === 0): ?>
                <p>Aucun résultat trouvé.</p>
            <?php else: ?>
                <?php foreach ($results as $item): ?>
                <div class="item">
                    <h3>
                        <?php echo $item["type"]; ?> :
                        <?php echo htmlspecialchars($item["titre"]); ?>
                    </h3>

                        <?php if ($item["ligne1"] !== ""): ?>
                            <p><?php echo htmlspecialchars($item["ligne1"]); ?></p>
                        <?php endif; ?>

                        <?php if ($item["ligne2"] !== ""): ?>
                            <p><?php echo htmlspecialchars($item["ligne2"]); ?></p>
                        <?php endif; ?>

                        <?php if ($item["ligne3"] !== ""): ?>
                            <p><?php echo htmlspecialchars($item["ligne3"]); ?></p>
                        <?php endif; ?>

                        <?php if ($item["ligne4"] !== ""): ?>
                            <p><?php echo htmlspecialchars($item["ligne4"]); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </body>
</html>