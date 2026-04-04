<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    //Récupération du poste de l'utilisateur
    $rowPoste = fetchOne($conn,
        "SELECT fonction
        FROM Vue_Personnel
        WHERE id_personnel = :id",
        [":id" => $_SESSION['id']]
    );
    $poste = $rowPoste['FONCTION'];

    //Récupération de la valeur tapée dans la barre de recherche
    $searchVal = isset($_GET["search"]) ? trim($_GET["search"]) : "";

    //Tableau qui contiendra tous les résultats à afficher, regroupés par type
    $resultats = [];

    //Si l'utilisateur arrive sur la page pour la première fois (bouton "Chercher" non cliqué => !isset), on affiche tout
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
    $archive = isset($_GET["archive"]) ? $_GET["archive"] : "actifs"; //Prend la valeur sélectionnée et actifs par défaut

    //Filtre espèce menacée
    $menace = isset($_GET["menace"]); //Prend un booléen selon si l'utilisateur a coché la case ou non

    //Filtre surface des enclos
    $surfaceMin = isset($_GET["surface_min"]) && $_GET["surface_min"] !== "" ? (float)$_GET["surface_min"] : null; //S'il y a une valeur on la prend sinon on met null
    $surfaceMax = isset($_GET["surface_max"]) && $_GET["surface_max"] !== "" ? (float)$_GET["surface_max"] : null;

    $aCherche = $searchVal !== ""; //Prend un booléen selon si l'utilisateur à mit quelque chose dans la barre de recherche
    $pattern = "%".strtolower($searchVal)."%"; //Crée le pattern pour les LIKE

    /* ====================================================================================================================================================== */
    /* =================================================== CONSTRUCTION DES REQUETES / RESULTATS ============================================================ */
    /* ====================================================================================================================================================== */

    /* ========= PERSONNEL ========= */
    if ($show["filtrePersonnel"]) { //Si filtre Personnel actif

        //Filtre d'archivage
        if ($archive === "actifs") {
            $whereArchive = "AND archiver_personnel = 'N'"; //Seulement les actifs
        } elseif ($archive === "archives") {
            $whereArchive = "AND archiver_personnel = 'O'"; //Seulement les archivés
        } else {
            $whereArchive = ""; //Actifs + archivés
        }

        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors on ajoute les LIKE, sinon non
        $whereSearch = $aCherche ? 
            "AND (LOWER(prenom_personnel) LIKE :pattern
            OR LOWER(nom_personnel) LIKE :pattern
            OR LOWER(id_connexion) LIKE :pattern
            OR TO_CHAR(id_personnel) LIKE :pattern
            OR LOWER(fonction) LIKE :pattern)" 
            : "";

        //Résultats de Personnel
        $rows = fetchAllRows($conn,
            "SELECT DISTINCT id_personnel, prenom_personnel, nom_personnel, id_connexion, archiver_personnel, libelle_zone, fonction
            FROM Vue_Personnel
            WHERE 1 = 1 -- Toujours vrai, pour forcément utiliser AND après
            $whereArchive -- Filtre d'archivage
            $whereSearch -- Barre de recherche
            ORDER BY id_personnel",
            $aCherche ? [":pattern" => $pattern] : [] //Si qlq chose dans la barre de recherche, on met le pattern en paramètre (car requête préparée), sinon on ne met rien
        );

        foreach ($rows as $row) {
            //Si le personnel actuel est archivé, on affecte [Archivé] à la variable, sinon rien
            $estArchive = $row["ARCHIVER_PERSONNEL"] === "O" ? " [Archivé]" : "";
            //On ajoute un nouvel élément à $resultats["Personnel"]
            $resultats["Personnel"][] = [
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

        //Si filtre d'espèce menacée cochée, alors on ajoute cette contrainte
        $whereMenace = $menace ? "AND VA.menace = 'O'" : "";

        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors on ajoute les LIKE, sinon non
        $whereSearch = $aCherche ? 
            "AND (LOWER(VA.nom_animal) LIKE :pattern
            OR TO_CHAR(VA.RFID) LIKE :pattern
            OR LOWER(VA.nom_usuel) LIKE :pattern
            OR LOWER(VA.nom_latin) LIKE :pattern
            OR LOWER(VA.libelle_zone) LIKE :pattern
            OR LOWER(P.prenom_personnel) LIKE :pattern
            OR LOWER(P.nom_personnel) LIKE :pattern)" 
            : "";

        $rows = fetchAllRows($conn,
            "SELECT VA.RFID, VA.nom_animal, VA.nom_usuel, VA.nom_latin, VA.id_enclos, VA.libelle_zone, P.prenom_personnel AS prenom_soigneur, P.nom_personnel AS nom_soigneur
            FROM Vue_Animal VA, Attitre AT, Personnel P
            WHERE VA.RFID = AT.RFID
            AND AT.id_personnel = P.id_personnel
            AND P.archiver_personnel = 'N'
            $whereMenace -- Filtre d'espèce menacée
            $whereSearch -- Barre de recherche
            ORDER BY VA.RFID",
            $aCherche ? [":pattern" => $pattern] : [] //Si qlq chose dans la barre de recherche, on met le pattern en paramètre (car requête préparée), sinon on ne met rien
        );

        foreach ($rows as $row) {
            //On ajoute un nouvel élément à $resultats["Animal"]
            $resultats["Animal"][] = [
                "titre" => $row["NOM_ANIMAL"],
                "ligne1" => "RFID : ".$row["RFID"],
                "ligne2" => "Espèce : ".$row["NOM_USUEL"]." (".$row["NOM_LATIN"].")",
                "ligne3" => "Enclos : ".$row["ID_ENCLOS"]." | Zone : ".$row["LIBELLE_ZONE"],
                "ligne4" => "Soigneur : ".$row["PRENOM_SOIGNEUR"]." ".$row["NOM_SOIGNEUR"],
            ];
        }
    }

    /* ========= ESPECES ========= */
    if ($show["filtreEspeces"]) { //Si filtre Espece actif

        //Si filtre d'espèce menacée cochée, alors on ajoute cette contrainte
        $whereMenace = $menace ? "AND menace = 'O'" : "";

        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors on ajoute les LIKE, sinon non
        $whereSearch = $aCherche ? 
            "AND (LOWER(nom_usuel) LIKE :pattern OR LOWER(nom_latin) LIKE :pattern)"
            : "";

        $rows = fetchAllRows($conn,
            "SELECT nom_usuel, nom_latin, menace
            FROM Espece
            WHERE 1 = 1 
            $whereMenace -- Filtre d'espèce menacée
            $whereSearch -- Barre de recherche
            ORDER BY nom_usuel",
            $aCherche ? [":pattern" => $pattern] : [] //Si qlq chose dans la barre de recherche, on met le pattern en paramètre (car requête préparée), sinon on ne met rien
        );

        foreach ($rows as $row) {
            //On ajoute un nouvel élément à $resultats["Espèce"]
            $resultats["Espèce"][] = [
                "titre" => $row["NOM_USUEL"],
                "ligne1" => "Nom latin : ".$row["NOM_LATIN"],
                "ligne2" => "Menacée : ".($row["MENACE"] === "O" ? "Oui" : "Non"),
                "ligne3" => "",
                "ligne4" => "",
            ];
        }
    }

    /* ========= ENCLOS ========= */
    if ($show["filtreEnclos"]) { //Si filtre Enclos actif

        //Filtre sur la surface des enclos
        $whereSurface = ""; //Contiendra la requête de filtre finale
        $paramsEnclos = []; //Contiendra les paramètres de requête

        //Si l'utilisateur a envoyé une surface minimum, on crée la requête et on ajoute le paramètre
        if ($surfaceMin !== null) {
            $whereSurface = " AND VE.surface >= :surface_min"; 
            $paramsEnclos[":surface_min"] = $surfaceMin;
        }
        //Si l'utilisateur a envoyé une surface maximum, on crée la requête et on ajoute le paramètre
        if ($surfaceMax !== null) {
            $whereSurface .= " AND VE.surface <= :surface_max"; //On concatène au cas où s'il y a une surface min
            $paramsEnclos[":surface_max"] = $surfaceMax;
        }

        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors on ajoute les LIKE, sinon non
        $whereSearch = $aCherche ? 
            "AND (id_enclos LIKE :pattern -- Identifiant de l'enclos
            OR LOWER(libelle_zone) LIKE :pattern -- Zone dans laquelle est l'enclos
            OR EXISTS ( -- Animal qui est dans l'enclos
                SELECT nom_animal FROM Vue_Animal VA
                WHERE VA.id_enclos = VE.id_enclos
                AND LOWER(VA.nom_animal) LIKE :pattern
            )
            OR EXISTS ( -- Particularité dans l'enclos
                SELECT id_enclos FROM Possede PO, Particularite PA
                WHERE PO.id_enclos = VE.id_enclos
                AND PO.id_particularite = PA.id_particularite
                AND LOWER(PA.libelle_particularite) LIKE :pattern
            ))"
            : "";

        //On ajoute :pattern aux paramètres seulement si la recherche texte est active
        if ($aCherche) {
            $paramsEnclos[":pattern"] = $pattern;
        }

        $rows = fetchAllRows($conn,
            "SELECT id_enclos, surface, VE.libelle_zone
            FROM Vue_Enclos VE
            WHERE 1 = 1
            $whereSurface -- Filtre de surface
            $whereSearch -- Barre de recherche
            ORDER BY id_enclos",
            $paramsEnclos //Tableau de paramètres
        );

        foreach ($rows as $row) {
            //On ajoute un nouvel élément à $resultats["Enclos"]
            $resultats["Enclos"][] = [
                "titre" => "Enclos ".$row["ID_ENCLOS"],
                "ligne1" => "Zone : ".$row["LIBELLE_ZONE"],
                "ligne2" => "Surface : ".$row["SURFACE"]." m²",
                "ligne3" => "",
                "ligne4" => "",
            ];
        }
    }

    /* ========= BOUTIQUES ========= */
    if ($show["filtreBoutiques"]) { //Si filtre Boutique actif


        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors on ajoute les LIKE, sinon non
        $whereSearch = $aCherche ? 
            "AND (LOWER(nom_boutique) LIKE :pattern
            OR LOWER(type_boutique) LIKE :pattern
            OR TO_CHAR(id_boutique) LIKE :pattern
            OR LOWER(libelle_zone) LIKE :pattern)"
            : "";

        $rows = fetchAllRows($conn,
            "SELECT id_boutique, nom_boutique, type_boutique, libelle_zone, prenom_personnel, nom_personnel
            FROM Vue_Boutique
            WHERE 1 = 1
            $whereSearch -- Barre de recherche
            ORDER BY id_boutique",
            $aCherche ? [":pattern" => $pattern] : [] //Si qlq chose dans la barre de recherche, on met le pattern en paramètre (car requête préparée), sinon on ne met rien
        );

        foreach ($rows as $row) {
            //On ajoute un nouvel élément à $resultats["Boutique"]
            $resultats["Boutique"][] = [
                "titre" => $row["NOM_BOUTIQUE"],
                "ligne1" => "ID : ".$row["ID_BOUTIQUE"],
                "ligne2" => "Type : ".$row["TYPE_BOUTIQUE"],
                "ligne3" => "Zone : ".$row["LIBELLE_ZONE"],
                "ligne4" => "Responsable : ".$row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
            ];
        }
    }

    /* ========= ZONES ========= */
    if ($show["filtreZones"]) {

        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors on ajoute les LIKE, sinon non
        $whereSearch = $aCherche ? 
            "AND (LOWER(VZ.libelle_zone) LIKE :pattern
            OR TO_CHAR(VZ.id_zone) LIKE :pattern)"
            : "";

        $rows = fetchAllRows($conn,
            "SELECT VZ.id_zone, VZ.libelle_zone, VZ.prenom_personnel, VZ.nom_personnel
            FROM Vue_Zone VZ
            WHERE VZ.archiver_personnel = 'N'
            $whereSearch -- Barre de recherche
            ORDER BY VZ.id_zone",
            $aCherche ? [":pattern" => $pattern] : [] //Si qlq chose dans la barre de recherche, on met le pattern en paramètre (car requête préparée), sinon on ne met rien
        );

        foreach ($rows as $row) {
            //On ajoute un nouvel élément à $resultats["Zone"]
            $resultats["Zone"][] = [
                "titre" => $row["LIBELLE_ZONE"],
                "ligne1" => "ID : ".$row["ID_ZONE"],
                "ligne2" => "Responsable : ".$row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
                "ligne3" => "",
                "ligne4" => "",
            ];
        }
    }

    /* ========= CONTRATS ========= */
    if ($show["filtreContrats"]) { //Si filtre Contrat actif

        //Si l'utilisateur a tapé qlq chose dans la barre de recherche alors on ajoute les LIKE, sinon non
        $whereSearch = $aCherche ? 
            "AND (id_contrat LIKE :pattern
            OR LOWER(prenom_personnel) LIKE :pattern
            OR LOWER(nom_personnel) LIKE :pattern
            OR LOWER(fonction) LIKE :pattern)"
            : "";

        $rows = fetchAllRows($conn,
            "SELECT C.id_contrat, C.salaire, TO_CHAR(C.date_debut, 'DD/MM/YYYY') AS date_debut, TO_CHAR(C.date_fin, 'DD/MM/YYYY') AS date_fin, P.prenom_personnel, P.nom_personnel, F.fonction
            FROM Contrat C, Personnel P, Fonction F
            WHERE C.id_personnel = P.id_personnel
            AND C.id_fonction = F.id_fonction
            $whereSearch -- Barre de recherche
            ORDER BY P.id_personnel",
            $aCherche ? [":pattern" => $pattern] : [] //Si qlq chose dans la barre de recherche, on met le pattern en paramètre (car requête préparée), sinon on ne met rien
        );

        foreach ($rows as $row) {
            //Si il y a une date de fin, on la prends, sinon on affiche contrat en cours
            $dateFin = $row["DATE_FIN"] ? "Date de fin : ".$row["DATE_FIN"] : "Contrat en cours";
            //On ajoute un nouvel élément à $resultats["Contrat"]
            $resultats["Contrat"][] = [
                "titre" => $row["PRENOM_PERSONNEL"]." ".$row["NOM_PERSONNEL"],
                "ligne1" => "ID contrat : ".$row["ID_CONTRAT"],
                "ligne2" => "Fonction : ".$row["FONCTION"],
                "ligne3" => "Salaire : ".$row["SALAIRE"]." €",
                "ligne4" => "Date de début : ".$row["DATE_DEBUT"],
                "ligne5" => $dateFin
            ];
        }
    }
?>

<!-- ====================================================================================================================================================== -->
<!-- ===================================================== CONSTRUCTION LIENS DE NAVIGATIONS ============================================================== -->
<!-- ====================================================================================================================================================== -->

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Accueil</title>
        <link rel="stylesheet" href="css/search.css">
    </head>
    <body>
        <div class="nav-links">
            <?php if ($poste === "Directeur"): ?>
                <!-- Si l'utilisateur est le directeur, il peut avoir accès à tout -->
                <form method="get" action="gestion.php">
                    <label><input type="checkbox" name="tablePersonnel" value="1"> Personnel</label>
                    <label><input type="checkbox" name="tableEnclos" value="1"> Enclos</label>
                    <label><input type="checkbox" name="tableBoutiques" value="1"> Boutiques</label>
                    <label><input type="checkbox" name="tableAnimaux" value="1"> Animaux</label>
                    <label><input type="checkbox" name="tableEspeces" value="1"> Espèces</label>
                    <input type="submit" value="Gérer">
                </form>
                <a href="desarchivage.php"><button>Desarchivage</button></a>
                <a href="parrainage.php"><button>Parrainages</button></a>
                <a href="soins.php"><button>Soins</button></a>
                <a href="comptes.php"><button>Comptes</button></a>
                <a href="reparations.php"><button>Reparations</button></a>
            <?php endif ?>
            <?php
                //Sinon, accès restreints
                if ($poste === "Soigneur" || $poste === "Veterinaire"){
                    echo '<a href="gestion.php?tableAnimaux=1&tableEspeces=1"><button>Gérer</button></a>';
                    echo '<a href="soins.php"><button>Soins</button></a>';
                }
                if ($poste === "Directeur de magasin"){
                    echo '<a href="gestion.php?tableBoutiques=1"><button>Gérer</button></a>';
                    echo '<a href="comptes.php"><button>Comptes</button></a>';
                }
                if ($poste === "Technicien"){
                    echo '<a href="reparations.php"><button>Reparations</button></a>';
                }
                if ($poste === "Comptable"){
                    echo '<a href="comptes.php"><button>Comptes</button></a>';
                }
            ?>
        </div>

<!-- ====================================================================================================================================================== -->
<!-- ================================================= CONSTRUCTION BARRE DE RECHERCHE ET FILTRES ========================================================= -->
<!-- ====================================================================================================================================================== -->

        <div class="header">
            <div class="profil">
                <a href="profil.php"><img src="images/user.png" class="user" alt="Profil"></a> <!-- Création du lien vers le profil -->
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
                        value="<?php echo htmlspecialchars($searchVal); ?>"> <!-- Création de la barre de recherche -->
                    <input type="submit" value="Chercher"> <!-- Création du bouton Chercher -->

                    <!-- Création des filtres par tables -->
                    <div class="search-filtres">
                        <?php
                            $filtreLabels = [
                                "filtrePersonnel" => "Personnel",
                                "filtreAnimaux" => "Animaux",
                                "filtreEspeces" => "Espèces",
                                "filtreEnclos" => "Enclos",
                                "filtreBoutiques" => "Boutiques",
                                "filtreZones" => "Zones",
                                "filtreContrats" => "Contrats",
                            ];
                            foreach ($filtreLabels as $key => $label) {
                                $checked = $show[$key] ? "checked" : ""; //Si une case est cochée, alors on la laisse cochée
                                echo '<label>';
                                echo '<input type="checkbox" name="'.$key.'" value="1" '.$checked.'>'; //Affiche la case, cochée ou non
                                echo $label; //Affiche la table correspondante
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
                                <option value="actifs"> Actifs seulement </option>
                                <!-- Si une option déjà sélectionnée, alors on la résélectionne -->
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

                        <!-- Filtre surface -->
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

<!-- ====================================================================================================================================================== -->
<!-- ================================================= CONSTRUCTION DE L'AFFICHAGE DES RESULTATS ========================================================== -->
<!-- ====================================================================================================================================================== -->

        <?php if (empty($resultats)): ?>
            <!-- Si aucun résultats, on affiche un message -->
            <div class="results">
                <h2>Résultats</h2>
                <p>Aucun résultat trouvé.</p>
            </div>
        <?php else: ?>
            <?php foreach ($resultats as $type => $items): ?>
                <!-- Sinon, pour tous les résultats, on affiche d'abord le type puis tous les résultats de ce tpye -->
                <div class="results">
                    <h2><?php echo htmlspecialchars($type); ?></h2>
                    <?php foreach ($items as $item): ?>
                        <div class="item">
                            <h3><?php echo htmlspecialchars($item["titre"]); ?></h3>

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

                            <?php if (key_exists("ligne5", $item) && ($item["ligne5"] !== "")): ?>
                                <p><?php echo htmlspecialchars($item["ligne5"]); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>