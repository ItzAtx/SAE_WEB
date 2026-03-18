<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";

    /* ============
    ATTRIBUTION
    =============== */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && postFieldsFilled(['rfid', 'id_personnel'])) {
        $rfid = $_POST['rfid'];
        $id_personnel = $_POST['id_personnel'];

        //Attribution d'un animal sans soigneur à un soigneur choisit
        execQuery($conn,
            "INSERT INTO Attitre
            VALUES (:rfid, :id_personnel)",
            [":rfid" => $rfid, ":id_personnel" => $id_personnel]
        );

        oci_commit($conn);
        $message = "<p>Soigneur attitré ajouté avec succès.</p>"; 
    }

    /* ==============================
    AJOUT D'UN SOIGNEUR À UNE ÉQUIPE
    ================================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && postFieldsFilled(['id_soigneur_equipe', 'id_chef_equipe'])) {
        $id_soigneur = $_POST['id_soigneur_equipe'];
        $id_chef = $_POST['id_chef_equipe'];

        //On crée le lien
        execQuery($conn,
            "INSERT INTO Chef
            VALUES (:id_chef, :id_soigneur)",
            [":id_chef" => $id_chef, ":id_soigneur" => $id_soigneur]
        );

        oci_commit($conn);
        $message = "<p>Soigneur ajouté à l'équipe avec succès.</p>";
    }

    /* ======================
    RECUPERATION DES SOINS
    ========================= */
    //Récupère tous les soins avec les infos de l'animal et du soigneur ayant réalisé le soin
    $soins = fetchAllRows(
        $conn,
        "SELECT s.id_soin, s.date_soin, s.complexite, a.RFID, a.nom_animal, a.nom_latin, p.id_personnel, p.nom_personnel, p.prenom_personnel
        FROM Soins s, Animal a, Personnel p
        WHERE s.RFID = a.RFID
        AND s.id_personnel = p.id_personnel
        ORDER BY s.date_soin DESC, s.id_soin DESC"
    );

    /* =============================
    ANIMAUX SANS SOIGNEUR ATTITRE
    ================================ */
    //Récupère les animaux qui n'ont aucune entrée dans la table Attitre
    $animaux = fetchAllRows($conn,
        "SELECT a.RFID, a.nom_animal, a.nom_latin
        FROM Animal a
        WHERE NOT EXISTS (
            SELECT RFID
            FROM Attitre at
            WHERE at.RFID = a.RFID
        )
        ORDER BY a.nom_animal"
    );

    /* ===================
    LISTE DES SOIGNEURS 
    ====================== */
    //Récupère tous les soigneurs non archivés
    $soigneurs = fetchAllRows($conn,
        "SELECT p.id_personnel, p.nom_personnel, p.prenom_personnel
        FROM Personnel p, Contrat c, Fonction f
        WHERE p.id_personnel = c.id_personnel
        AND c.id_fonction = f.id_fonction
        AND LOWER(f.fonction) = 'soigneur'
        AND p.archiver_personnel = 'N'
        ORDER BY p.nom_personnel"
    );

    /* =========================
    HIÉRARCHIE CHEFS SOIGNEURS
    ========================= */
    //Récupère les liens Chef pour chaque chef et les gens de son équpé
    //Pour chaque lien : infos du chef, sa zone responsable, infos de l'équipier
    $rowsChefs = fetchAllRows($conn,
        "SELECT
            c.id_personnel_manager_de AS id_chef,
            p_chef.nom_personnel AS nom_chef,
            p_chef.prenom_personnel AS prenom_chef,
            z.libelle_zone AS libelle_zone,
            c.id_personnel_est_manager_par AS id_sub,
            p_eq.nom_personnel AS nom_eq,
            p_eq.prenom_personnel AS prenom_eq
        FROM Chef c, Personnel p_chef, Personnel p_eq, Zone_zoo z
        WHERE c.id_personnel_manager_de = p_chef.id_personnel
        AND c.id_personnel_est_manager_par = p_eq.id_personnel
        AND z.id_personnel = p_chef.id_personnel
        ORDER BY p_chef.nom_personnel"
    );

    //Clé = id du chef, valeur = ses infos + tableau de ses équipiers
    $chefs = [];
    foreach ($rowsChefs as $row) {
        $idChef = $row['ID_CHEF'];
        //Si première fois qu'on accède à ce chef, on l'initialise
        if (!isset($chefs[$idChef])) {
            $chefs[$idChef] = [
                'nom' => $row['PRENOM_CHEF'].' '.$row['NOM_CHEF'],
                'zone' => $row['LIBELLE_ZONE'],
                'equipe' => []
            ];
        }
        //Sinon on ajoute uniquement son équipier
        $chefs[$idChef]['equipe'][] = ['nom' => $row['PRENOM_EQ'].' '.$row['NOM_EQ']];
    }

    /*=========================
    SOIGNEURS NON MANAGÉS
    ========================= */
    /*
    Récupère les soigneurs actifs qui :
    -ne sont managés par personne
    -ne managent personne non plus
    */
    $soigneursNonManages = fetchAllRows($conn,
        "SELECT DISTINCT p.id_personnel, p.nom_personnel, p.prenom_personnel
        FROM Personnel p, Contrat c, Fonction f
        WHERE p.id_personnel = c.id_personnel
        AND c.id_fonction = f.id_fonction
        AND LOWER(f.fonction) = 'soigneur'
        AND p.archiver_personnel = 'N' --Non archivé
        AND NOT EXISTS ( --Managé par personne
            SELECT * FROM Chef ch
            WHERE ch.id_personnel_est_manager_par = p.id_personnel
        )
        AND NOT EXISTS ( --Ne manage personne
            SELECT * FROM Chef ch
            WHERE ch.id_personnel_manager_de = p.id_personnel
        )
        ORDER BY p.nom_personnel"
    );

    /* ================
    CHEFS SOIGNEURS
    ================== */
    //Récupère les soigneurs non archivés qui apparaissent dans id_personnel_manager_de de Chef
    $chefsSoigneurs = fetchAllRows($conn,
        "SELECT DISTINCT p.id_personnel, p.nom_personnel, p.prenom_personnel, z.libelle_zone
        FROM Personnel p, Zone_zoo z
        WHERE z.id_personnel = p.id_personnel
        AND p.archiver_personnel = 'N' --Non archivé
        AND EXISTS ( --Est manager
            SELECT * FROM Chef ch
            WHERE ch.id_personnel_manager_de = p.id_personnel
        )
        ORDER BY p.nom_personnel"
    );

    /* =========================
    SPÉCIALITÉS DES SOIGNEURS
    ========================= */
    //Récupère les espèces de spécialisation de chaque soigneur actif
    $rowsSpecialites = fetchAllRows($conn,
        "SELECT p.id_personnel, p.nom_personnel, p.prenom_personnel, e.nom_latin, e.nom_usuel, e.menace
        FROM Personnel p, Specialiser s, Espece e
        WHERE s.id_personnel = p.id_personnel
        AND s.nom_latin = e.nom_latin
        AND p.archiver_personnel = 'N'
        ORDER BY p.nom_personnel, p.prenom_personnel, e.nom_usuel"
    );

    //Clé = id du soigneur, valeur = ses infos + tableau de ses espèces
    $specialites = [];
    foreach ($rowsSpecialites as $row) {
        $id = $row['ID_PERSONNEL'];
        //Si première fois qu'on accède à ce soigneur, on l'initialise
        if (!isset($specialites[$id])) {
            $specialites[$id] = [
                'nom' => $row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL'],
                'especes' => []
            ];
        }
        //Sinon, on ajoute seulement l'espèce a sa liste de spécialité
        $specialites[$id]['especes'][] = [
            'nom_usuel' => $row['NOM_USUEL'],
            'nom_latin' => $row['NOM_LATIN'],
            'menace' => $row['MENACE']
        ];
    }

    /* =========================
    ANIMAUX ET LEURS SOIGNEURS ATTITRÉS
    ========================= */
    //Récupère tous les animaux qui ont un soigneur attitré
    $animauxSoigneurs = fetchAllRows($conn,
        "SELECT a.RFID, a.nom_animal, a.nom_latin, e.nom_usuel, p.id_personnel, p.nom_personnel, p.prenom_personnel
        FROM Animal a, Espece e, Attitre at, Personnel p
        WHERE a.nom_latin = e.nom_latin
        AND at.RFID = a.RFID
        AND p.id_personnel = at.id_personnel
        ORDER BY a.nom_animal"
    );

    //Récupère les animaux qui n'ont pas de soigneur attitré (aucune entrée dans Attitre)
    $animauxSansSoigneurAttitre = fetchAllRows($conn,
        "SELECT a.RFID, a.nom_animal, a.nom_latin, e.nom_usuel
        FROM Animal a, Espece e
        WHERE a.nom_latin = e.nom_latin
        AND NOT EXISTS (
            SELECT * FROM Attitre at WHERE at.RFID = a.RFID
        )
        ORDER BY a.nom_animal"
    );
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Soins</title>
</head>
<body>
    <div class="container">
        <a href="search.php">Retour à l'accueil</a>

        <h2>Liste des soins</h2>

        <!-- Message de confirmation -->
        <div class="message">
            <?php echo $message; ?>
        </div>

        <!-- ===================== TABLEAU DES SOINS ===================== -->
        <div class="card">
            <table border="1">
                <tr>
                    <th>ID soin</th>
                    <th>Date</th>
                    <th>Complexité</th>
                    <th>Animal</th>
                    <th>RFID</th>
                    <th>Espèce</th>
                    <th>Personnel ayant réalisé le soin</th>
                </tr>
                
                <!-- Affichage des soins -->
                <?php foreach ($soins as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_SOIN']) ?></td>
                        <td><?php echo htmlspecialchars($row['DATE_SOIN']) ?></td>
                        <td><?php echo htmlspecialchars($row['COMPLEXITE']) ?></td>
                        <td><?php echo htmlspecialchars($row['NOM_ANIMAL']) ?></td>
                        <td><?php echo htmlspecialchars($row['RFID']) ?></td>
                        <td><?php echo htmlspecialchars($row['NOM_LATIN']) ?></td>
                        <td><?php echo htmlspecialchars($row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- ===================== HIÉRARCHIE CHEFS SOIGNEURS ===================== -->
        <div class="card">
            <h2>Chefs soigneurs et leurs équipes</h2>

            <?php if (empty($chefs)): ?>
                <p>Aucun chef soigneur défini.</p>
            <?php else: ?>
                <table border="1">
                    <tr>
                        <th>Chef soigneur</th>
                        <th>Zone responsable</th>
                        <th>Membre de l'équipe</th>
                    </tr>
                    <?php foreach ($chefs as $chef): ?>
                        <?php $nb = count($chef['equipe']); ?>
                        <?php foreach ($chef['equipe'] as $i => $membre): ?>
                            <tr>
                                <?php if ($i === 0): ?>
                                    <td rowspan="<?php echo $nb ?>"><?php echo htmlspecialchars($chef['nom']) ?></td>
                                    <td rowspan="<?php echo $nb ?>"><?php echo htmlspecialchars($chef['zone']) ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($membre['nom']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- ===================== SPÉCIALITÉS DES SOIGNEURS ===================== -->
        <div class="card">
            <h2>Spécialités des soigneurs</h2>

            <?php if (empty($specialites)): ?>
                <p>Aucune spécialité enregistrée.</p>
            <?php else: ?>
                <table border="1">
                    <tr>
                        <th>Soigneur</th>
                        <th>Espèce</th>
                        <th>Nom latin</th>
                        <th>Espèce menacée</th>
                    </tr>
                    <?php foreach ($specialites as $soigneur): ?>
                        <?php $nb = count($soigneur['especes']); ?>
                        <?php foreach ($soigneur['especes'] as $i => $espece): ?>
                            <tr>
                                <?php if ($i === 0):?>
                                    <td rowspan="<?php echo $nb ?>"><?php echo htmlspecialchars($soigneur['nom']) ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($espece['nom_usuel']) ?></td>
                                <td><em><?php echo htmlspecialchars($espece['nom_latin']) ?></em></td>
                                <td><?php echo $espece['menace'] === 'O' ? 'Oui' : 'Non' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- ===================== SOIGNEURS ATTITRÉS PAR ANIMAL ===================== -->
        <div class="card">
            <h2>Soigneur attitré par animal</h2>
            <table border="1">
                <tr>
                    <th>Animal</th>
                    <th>RFID</th>
                    <th>Espèce</th>
                    <th>Nom latin</th>
                    <th>Soigneur attitré</th>
                </tr>
                <!-- Animaux avec soigneur attitré -->
                <?php foreach ($animauxSoigneurs as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['NOM_ANIMAL']) ?></td>
                        <td><?php echo htmlspecialchars($row['RFID']) ?></td>
                        <td><?php echo htmlspecialchars($row['NOM_USUEL']) ?></td>
                        <td><em><?php echo htmlspecialchars($row['NOM_LATIN']) ?></em></td>
                        <td><?php echo htmlspecialchars($row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <!-- Animaux sans soigneur attitré -->
                <?php foreach ($animauxSansSoigneurAttitre as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['NOM_ANIMAL']) ?></td>
                        <td><?php echo htmlspecialchars($row['RFID']) ?></td>
                        <td><?php echo htmlspecialchars($row['NOM_USUEL']) ?></td>
                        <td><em><?php echo htmlspecialchars($row['NOM_LATIN']) ?></em></td>
                        <td><em>Aucun soigneur attitré</em></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- ===================== AJOUTER UN SOIGNEUR À UNE ÉQUIPE ===================== -->
        <div class="card">
            <h2>Ajouter un soigneur à une équipe</h2>

            <?php if (empty($soigneursNonManages)): ?>
                <p class="empty">Tous les soigneurs sont déjà dans une équipe.</p>
            <?php elseif (empty($chefsSoigneurs)): ?>
                <p class="empty">Aucun chef soigneur défini, impossible d'affecter un soigneur.</p>
            <?php else: ?>
                <form method="post">
                    <!-- Select 1 : soigneurs sans équipe -->
                    <label><strong>Soigneur à affecter :</strong></label>
                    <select name="id_soigneur_equipe" required>
                        <?php foreach ($soigneursNonManages as $s): ?>
                            <option value="<?php echo htmlspecialchars($s['ID_PERSONNEL']) ?>">
                                <?php echo htmlspecialchars($s['PRENOM_PERSONNEL'].' '.$s['NOM_PERSONNEL']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Select 2 : chefs soigneurs disponibles avec leur zone -->
                    <label><strong>Équipe du chef :</strong></label>
                    <select name="id_chef_equipe" required>
                        <?php foreach ($chefsSoigneurs as $chef): ?>
                            <option value="<?php echo htmlspecialchars($chef['ID_PERSONNEL']) ?>">
                                <?php echo htmlspecialchars($chef['PRENOM_PERSONNEL'].' '.$chef['NOM_PERSONNEL'].($chef['LIBELLE_ZONE'] ? ' - '.$chef['LIBELLE_ZONE'] : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Ajouter à l'équipe</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- ===================== ATTRIBUER SOIGNEUR ===================== -->
        <div class="card">
            <h2>Attribuer soigneur</h2>

            <form method="post">
                <!-- Select 1 : animaux sans soigneur attitré -->
                <label><strong>Animal :</strong></label>
                <select name="rfid" required>
                    <?php foreach ($animaux as $animal): ?>
                        <option value="<?php echo htmlspecialchars($animal['RFID']) ?>">
                            <?php echo htmlspecialchars($animal['NOM_ANIMAL'].' (RFID '.$animal['RFID'].' - '.$animal['NOM_LATIN'].')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Select 2 : tous les soigneurs actifs -->
                <label><strong>Soigneur :</strong></label>
                <select name="id_personnel" required>
                    <?php foreach ($soigneurs as $row): ?>
                        <option value="<?php echo htmlspecialchars($row['ID_PERSONNEL']) ?>">
                            <?php echo htmlspecialchars($row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Attribuer</button>
            </form>

            <!-- Message si tous les animaux ont déjà un soigneur attitré -->
            <?php if (count($animaux) === 0): ?>
                <p class="empty">Tous les animaux ont déjà un soigneur attitré.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>