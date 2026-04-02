<?php
    include_once("fonctions.php");
    requireLogin();
    $conn = getConnection();

    $message = "";

    /* =========================
    AJOUT D'UN SOIN
    ========================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_soin'])) {
        $fields = ['date_soin', 'complexite', 'rfid_soin', 'id_personnel_soin'];
        if (!postFieldsFilled($fields)) {
            $message = "Veuillez remplir tous les champs du soin.";
        } else {
            $rowFonction = fetchOne($conn,
                "SELECT fonction FROM Vue_Personnel WHERE id_personnel = :id",
                [':id' => $_POST['id_personnel_soin']]
            );
            $fonction = $rowFonction ? $rowFonction['FONCTION'] : null;

            if ($_POST['complexite'] === 'Complexe' && $fonction !== 'Veterinaire') {
                $message = "Erreur : seul un vétérinaire peut réaliser un soin complexe.";
            } else {
                $nextIdSoin = getNextId($conn, "Soins", "id_soin");
                execQuery($conn,
                    "INSERT INTO Soins VALUES (:id_soin, TO_DATE(:date_soin, 'YYYY-MM-DD'), :complexite, :id_personnel, :rfid)",
                    [':id_soin' => $nextIdSoin, ':date_soin' => $_POST['date_soin'], ':complexite' => $_POST['complexite'], ':id_personnel' => $_POST['id_personnel_soin'], ':rfid' => $_POST['rfid_soin']]
                );
                oci_commit($conn);
                $message = "Soin ajouté avec succès.";
            }
        }
    }

    /* ============
    ATTRIBUTION
    =============== */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_attribution']) && postFieldsFilled(['rfid', 'id_personnel'])) {
        execQuery($conn,
            "INSERT INTO Attitre VALUES (:rfid, :id_personnel)",
            [":rfid" => $_POST['rfid'], ":id_personnel" => $_POST['id_personnel']]
        );
        oci_commit($conn);
        $message = "Soigneur attitré ajouté avec succès.";
    }

    /* ==============================
    AJOUT D'UN SOIGNEUR À UNE ÉQUIPE
    ================================= */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_equipe']) && postFieldsFilled(['id_soigneur_equipe', 'id_chef_equipe'])) {
        execQuery($conn,
            "INSERT INTO Chef VALUES (:id_chef, :id_soigneur)",
            [":id_chef" => $_POST['id_chef_equipe'], ":id_soigneur" => $_POST['id_soigneur_equipe']]
        );
        oci_commit($conn);
        $message = "Soigneur ajouté à l'équipe avec succès.";
    }

    /* ======================
    RECUPERATION DES SOINS
    ========================= */
    $soins = fetchAllRows($conn,
        "SELECT id_soin, TO_CHAR(date_soin,'YYYY-MM-DD') AS date_soin, complexite, RFID, nom_animal, nom_latin, id_personnel, nom_personnel, prenom_personnel
        FROM Vue_Soin
        ORDER BY id_soin ASC"
    );

    /* =============================
    ANIMAUX SANS SOIGNEUR ATTITRE
    ================================ */
    $animaux = fetchAllRows($conn,
        "SELECT VA.RFID, VA.nom_animal, VA.nom_latin
        FROM Vue_Animal VA
        WHERE NOT EXISTS (
            SELECT * FROM Attitre AT WHERE AT.RFID = VA.RFID
        )
        ORDER BY VA.nom_animal"
    );

    /* ===================
    LISTE DES SOIGNEURS 
    ====================== */
    $soigneurs = fetchAllRows($conn,
        "SELECT id_personnel, nom_personnel, prenom_personnel
        FROM Vue_Personnel
        WHERE fonction = 'Soigneur'
        AND archiver_personnel = 'N'
        ORDER BY nom_personnel"
    );

    /* =========================
    HIÉRARCHIE CHEFS SOIGNEURS
    ========================= */
    $rowsChefs = fetchAllRows($conn,
        "SELECT
            c.id_personnel_manager_de AS id_chef,
            VZ.nom_personnel AS nom_chef,
            VZ.prenom_personnel AS prenom_chef,
            VZ.libelle_zone AS libelle_zone,
            c.id_personnel_est_manager_par AS id_sub,
            p_eq.nom_personnel AS nom_eq,
            p_eq.prenom_personnel AS prenom_eq
        FROM Chef c, Vue_Zone VZ, Personnel p_eq
        WHERE c.id_personnel_manager_de = VZ.id_personnel
        AND c.id_personnel_est_manager_par = p_eq.id_personnel
        ORDER BY VZ.nom_personnel"
    );

    $chefs = [];
    foreach ($rowsChefs as $row) {
        $idChef = $row['ID_CHEF'];
        if (!isset($chefs[$idChef])) {
            $chefs[$idChef] = [
                'nom' => $row['PRENOM_CHEF'].' '.$row['NOM_CHEF'],
                'zone' => $row['LIBELLE_ZONE'],
                'equipe' => []
            ];
        }
        $chefs[$idChef]['equipe'][] = ['nom' => $row['PRENOM_EQ'].' '.$row['NOM_EQ']];
    }

    $chefsSeuls = fetchAllRows($conn,
        "SELECT DISTINCT VZ.id_personnel AS id_chef, VZ.nom_personnel AS nom_chef, VZ.prenom_personnel AS prenom_chef, VZ.libelle_zone
        FROM Vue_Zone VZ
        WHERE VZ.archiver_personnel = 'N'
        AND NOT EXISTS (
            SELECT * FROM Chef ch WHERE ch.id_personnel_manager_de = VZ.id_personnel
        )"
    );
    foreach ($chefsSeuls as $row) {
        $idChef = $row['ID_CHEF'];
        if (!isset($chefs[$idChef])) {
            $chefs[$idChef] = ['nom' => $row['PRENOM_CHEF'].' '.$row['NOM_CHEF'], 'zone' => $row['LIBELLE_ZONE'], 'equipe' => []];
        }
    }

    /* =========================
    SOIGNEURS NON MANAGÉS
    ========================= */
    $soigneursNonManages = fetchAllRows($conn,
        "SELECT DISTINCT id_personnel, nom_personnel, prenom_personnel
        FROM Vue_Personnel VP
        WHERE fonction = 'Soigneur'
        AND archiver_personnel = 'N'
        AND NOT EXISTS (SELECT * FROM Chef ch WHERE ch.id_personnel_est_manager_par = VP.id_personnel)
        AND NOT EXISTS (SELECT * FROM Chef ch WHERE ch.id_personnel_manager_de = VP.id_personnel)
        AND NOT EXISTS (SELECT * FROM Vue_Zone VZ WHERE VZ.id_personnel = VP.id_personnel)
        ORDER BY nom_personnel"
    );

    /* ================
    CHEFS SOIGNEURS
    ================== */
    $chefsSoigneurs = fetchAllRows($conn,
        "SELECT DISTINCT id_personnel, nom_personnel, prenom_personnel, libelle_zone
        FROM Vue_Zone
        WHERE archiver_personnel = 'N'
        ORDER BY nom_personnel"
    );

    /* =========================
    SPÉCIALITÉS DES SOIGNEURS
    ========================= */
    $rowsSpecialites = fetchAllRows($conn,
        "SELECT VP.id_personnel, VP.nom_personnel, VP.prenom_personnel, e.nom_latin, e.nom_usuel, e.menace
        FROM Vue_Personnel VP, Specialiser s, Espece e
        WHERE s.id_personnel = VP.id_personnel
        AND s.nom_latin = e.nom_latin
        AND VP.archiver_personnel = 'N'
        ORDER BY VP.nom_personnel, VP.prenom_personnel, e.nom_usuel"
    );

    $specialites = [];
    foreach ($rowsSpecialites as $row) {
        $id = $row['ID_PERSONNEL'];
        if (!isset($specialites[$id])) {
            $specialites[$id] = ['nom' => $row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL'], 'especes' => []];
        }
        $specialites[$id]['especes'][] = [
            'nom_usuel' => $row['NOM_USUEL'],
            'nom_latin' => $row['NOM_LATIN'],
            'menace' => $row['MENACE']
        ];
    }

    /* =========================
    ANIMAUX ET LEURS SOIGNEURS ATTITRÉS
    ========================= */
    $animauxSoigneurs = fetchAllRows($conn,
        "SELECT VA.RFID, VA.nom_animal, VA.nom_latin, VA.nom_usuel, P.id_personnel, P.nom_personnel, P.prenom_personnel
        FROM Vue_Animal VA, Attitre AT, Personnel P
        WHERE AT.RFID = VA.RFID
        AND P.id_personnel = AT.id_personnel
        ORDER BY VA.nom_animal"
    );

    $animauxSansSoigneurAttitre = fetchAllRows($conn,
        "SELECT VA.RFID, VA.nom_animal, VA.nom_latin, VA.nom_usuel
        FROM Vue_Animal VA
        WHERE NOT EXISTS (SELECT * FROM Attitre AT WHERE AT.RFID = VA.RFID)
        ORDER BY VA.nom_animal"
    );

    /* =========================
    VÉTÉRINAIRES
    ========================= */
    $veterinaires = fetchAllRows($conn,
        "SELECT id_personnel, nom_personnel, prenom_personnel
        FROM Vue_Personnel
        WHERE fonction = 'Veterinaire'
        AND archiver_personnel = 'N'
        ORDER BY nom_personnel"
    );

    /* =========================
    DONNÉES POUR FORMULAIRE SOIN
    ========================= */
    $nextIdSoin = getNextId($conn, "Soins", "id_soin");

    $tousAnimaux = fetchAllRows($conn,
        "SELECT RFID, nom_animal, nom_usuel FROM Vue_Animal ORDER BY nom_animal"
    );

    // Soigneurs et vétérinaires séparés pour le formulaire
    $soigneursForm = fetchAllRows($conn,
        "SELECT id_personnel, nom_personnel, prenom_personnel, fonction
        FROM Vue_Personnel
        WHERE fonction IN ('Soigneur', 'Veterinaire')
        AND archiver_personnel = 'N'
        ORDER BY fonction DESC, nom_personnel"
    );

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soins</title>
</head>
    <body>

        <a href="search.php"><button type="button">Retour à l'accueil</button></a>

        <?php if ($message !== ""): ?>
            <p><strong><?php echo htmlspecialchars($message) ?></strong></p>
        <?php endif; ?>

        <!-- ===================== TABLEAU DES SOINS ===================== -->
        <h2>Liste des soins</h2>
        <table border="1">
            <tr>
                <th>ID soin</th>
                <th>Date</th>
                <th>Complexité</th>
                <th>Animal</th>
                <th>RFID</th>
                <th>Espèce</th>
                <th>Personnel</th>
            </tr>
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

        <!-- ===================== HIÉRARCHIE CHEFS SOIGNEURS ===================== -->
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
                    <?php if ($nb === 0): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($chef['nom']) ?></td>
                            <td><?php echo htmlspecialchars($chef['zone']) ?></td>
                            <td><em>Aucun équipier</em></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($chef['equipe'] as $i => $membre): ?>
                            <tr>
                                <?php if ($i === 0): ?>
                                    <td rowspan="<?php echo $nb ?>"><?php echo htmlspecialchars($chef['nom']) ?></td>
                                    <td rowspan="<?php echo $nb ?>"><?php echo htmlspecialchars($chef['zone']) ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($membre['nom']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- ===================== VÉTÉRINAIRES ===================== -->
        <h2>Vétérinaires</h2>
        <?php if (empty($veterinaires)): ?>
            <p>Aucun vétérinaire enregistré.</p>
        <?php else: ?>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                </tr>
                <?php foreach ($veterinaires as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_PERSONNEL']) ?></td>
                        <td><?php echo htmlspecialchars($row['PRENOM_PERSONNEL']) ?></td>
                        <td><?php echo htmlspecialchars($row['NOM_PERSONNEL']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- ===================== SPÉCIALITÉS DES SOIGNEURS ===================== -->
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
                            <?php if ($i === 0): ?>
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

        <!-- ===================== SOIGNEURS ATTITRÉS PAR ANIMAL ===================== -->
        <h2>Soigneur attitré par animal</h2>
        <table border="1">
            <tr>
                <th>Animal</th>
                <th>RFID</th>
                <th>Espèce</th>
                <th>Nom latin</th>
                <th>Soigneur attitré</th>
            </tr>
            <?php foreach ($animauxSoigneurs as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['NOM_ANIMAL']) ?></td>
                    <td><?php echo htmlspecialchars($row['RFID']) ?></td>
                    <td><?php echo htmlspecialchars($row['NOM_USUEL']) ?></td>
                    <td><em><?php echo htmlspecialchars($row['NOM_LATIN']) ?></em></td>
                    <td><?php echo htmlspecialchars($row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL']) ?></td>
                </tr>
            <?php endforeach; ?>
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

        <!-- ===================== AJOUTER UN SOIGNEUR À UNE ÉQUIPE ===================== -->
        <h2>Ajouter un soigneur à une équipe</h2>
        <?php if (empty($soigneursNonManages)): ?>
            <p>Tous les soigneurs sont déjà dans une équipe.</p>
        <?php elseif (empty($chefsSoigneurs)): ?>
            <p>Aucun chef soigneur défini, impossible d'affecter un soigneur.</p>
        <?php else: ?>
            <form method="post">
                <label>Soigneur à affecter :
                    <select name="id_soigneur_equipe" required>
                        <?php foreach ($soigneursNonManages as $s): ?>
                            <option value="<?php echo htmlspecialchars($s['ID_PERSONNEL']) ?>">
                                <?php echo htmlspecialchars($s['PRENOM_PERSONNEL'].' '.$s['NOM_PERSONNEL']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Équipe du chef :
                    <select name="id_chef_equipe" required>
                        <?php foreach ($chefsSoigneurs as $chef): ?>
                            <option value="<?php echo htmlspecialchars($chef['ID_PERSONNEL']) ?>">
                                <?php echo htmlspecialchars($chef['PRENOM_PERSONNEL'].' '.$chef['NOM_PERSONNEL'].($chef['LIBELLE_ZONE'] ? ' - '.$chef['LIBELLE_ZONE'] : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <input type="submit" name="ajouter_equipe" value="Ajouter à l'équipe">
            </form>
        <?php endif; ?>

        <!-- ===================== ATTRIBUER SOIGNEUR ===================== -->
        <h2>Attribuer un soigneur à un animal</h2>
        <?php if (empty($animaux)): ?>
            <p>Tous les animaux ont déjà un soigneur attitré.</p>
        <?php elseif (empty($soigneurs)): ?>
            <p>Aucun soigneur disponible.</p>
        <?php else: ?>
            <form method="post">
                <label>Animal :
                    <select name="rfid" required>
                        <?php foreach ($animaux as $animal): ?>
                            <option value="<?php echo htmlspecialchars($animal['RFID']) ?>">
                                <?php echo htmlspecialchars($animal['NOM_ANIMAL'].' (RFID '.$animal['RFID'].' - '.$animal['NOM_LATIN'].')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Soigneur :
                    <select name="id_personnel" required>
                        <?php foreach ($soigneurs as $row): ?>
                            <option value="<?php echo htmlspecialchars($row['ID_PERSONNEL']) ?>">
                                <?php echo htmlspecialchars($row['PRENOM_PERSONNEL'].' '.$row['NOM_PERSONNEL']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <input type="submit" name="ajouter_attribution" value="Attribuer">
            </form>
        <?php endif; ?>

        <!-- ===================== AJOUTER UN SOIN ===================== -->
        <h2>Ajouter un soin</h2>
        <form method="post">
            <table border="1">
                <tr>
                    <th>ID soin</th>
                    <th>Date</th>
                    <th>Complexité</th>
                    <th>Animal</th>
                    <th>Soigneur / Vétérinaire</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <td>
                        <input type="text" value="<?php echo $nextIdSoin ?>" readonly>
                    </td>
                    <td>
                        <input type="date" name="date_soin" required>
                    </td>
                    <td>
                        <select name="complexite" required>
                            <option value="Simple">Simple</option>
                            <option value="Complexe">Complexe</option>
                        </select>
                    </td>
                    <td>
                        <select name="rfid_soin" required>
                            <?php foreach ($tousAnimaux as $a): ?>
                                <option value="<?php echo htmlspecialchars($a['RFID']) ?>">
                                    <?php echo htmlspecialchars($a['NOM_ANIMAL'].' ('.$a['NOM_USUEL'].')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="id_personnel_soin" required>
                            <optgroup label="Vétérinaires">
                                <?php foreach ($soigneursForm as $p): ?>
                                    <?php if ($p['FONCTION'] === 'Veterinaire'): ?>
                                        <option value="<?php echo htmlspecialchars($p['ID_PERSONNEL']) ?>">
                                            <?php echo htmlspecialchars($p['PRENOM_PERSONNEL'].' '.$p['NOM_PERSONNEL']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Soigneurs (Simple uniquement)">
                                <?php foreach ($soigneursForm as $p): ?>
                                    <?php if ($p['FONCTION'] === 'Soigneur'): ?>
                                        <option value="<?php echo htmlspecialchars($p['ID_PERSONNEL']) ?>">
                                            <?php echo htmlspecialchars($p['PRENOM_PERSONNEL'].' '.$p['NOM_PERSONNEL']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </td>
                    <td>
                        <input type="submit" name="ajouter_soin" value="Ajouter">
                    </td>
                </tr>
            </table>
        </form>

    </body>
</html>