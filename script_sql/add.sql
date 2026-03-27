/*Fonction*/
INSERT INTO Fonction (id_fonction, fonction) VALUES (1, 'Directeur');
INSERT INTO Fonction (id_fonction, fonction) VALUES (2, 'Technicien');
INSERT INTO Fonction (id_fonction, fonction) VALUES (3, 'Soigneur');
INSERT INTO Fonction (id_fonction, fonction) VALUES (4, 'Employe de magasin');
INSERT INTO Fonction (id_fonction, fonction) VALUES (5, 'Directeur de magasin');
INSERT INTO Fonction (id_fonction, fonction) VALUES (6, 'Comptable');
INSERT INTO Fonction (id_fonction, fonction) VALUES (7, 'Vétérinaire');

/*Personnel (sans zone dans un premier temps, contrainte circulaire Zone_zoo <-> Personnel)*/
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (1, 'Belabbas',   'Selma',   '$2y$10$PmIuAdjLikc.uuueH0YL6eA0w2z5NV2yY0JZeW9B9xND/4So.WLe6', 'selma.belabbas', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (2, 'Delloue',   'Alexandre',   '$2y$10$1kEX7cwJbLO9d4VipsAxMO7mHF.W8tgIqxfIimxHUpaoEuHxyHMry', 'alexandre.delloue', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (3, 'Vauchel',  'Anthony',  '$2y$10$3xXH2SEfTG7y0uSK52NVWeFIXyn.Nx20008ikm6AoX5uAwME.CaOO', 'anthony.vauchel', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (4, 'Katr', 'Jhin',   '$2y$10$m5i88BDPM8UFFu90sAFLieTQhfArbbAOHiBDW1wVGMlL5LLWBMdiG', 'jhin.katr', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (5, 'Mamamia',   'Mario', '$2y$10$sVvv6tJBa912aNd.5JBx.erYcDIHsirKaST7vHt5ptx/X7.hc3EFa', 'mario.mamamia', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (6, 'Sorton', 'Virginie', '$2y$10$1kEX7cwJbLO9d4VipsAxMO7mHF.W8tgIqxfIimxHUpaoEuHxyHMry', 'virginie.sorton', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (7, 'Fontaine', 'Lucas', '$2y$10$PmIuAdjLikc.uuueH0YL6eA0w2z5NV2yY0JZeW9B9xND/4So.WLe6', 'lucas.fontaine', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (8, 'Renard', 'Sophie', '$2y$10$PmIuAdjLikc.uuueH0YL6eA0w2z5NV2yY0JZeW9B9xND/4So.WLe6', 'sophie.renard', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (9, 'Moreau', 'Théo', '$2y$10$PmIuAdjLikc.uuueH0YL6eA0w2z5NV2yY0JZeW9B9xND/4So.WLe6', 'theo.moreau', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (10, 'Laurent', 'Camille', '$2y$10$PmIuAdjLikc.uuueH0YL6eA0w2z5NV2yY0JZeW9B9xND/4So.WLe6', 'camille.laurent', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (11, 'Lucas', 'Delcroix', '$2y$10$PmIuAdjLikc.uuueH0YL6eA0w2z5NV2yY0JZeW9B9xND/4So.WLe6', 'lucas.delcroix', NULL, 'N');

/*Zones (5 zones, responsable = Soigneurs id=1)*/
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (1, 'Zone Afrique', 3);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (2, 'Zone Asie', 6);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (3, 'Zone France', 7);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (4, 'Zone Dinosaure', 8);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (5, 'Zone Aquatique', 9);

-- Rattachement des personnels a leur zone
UPDATE Personnel SET id_zone = 1 WHERE id_personnel IN (1, 2, 3);
UPDATE Personnel SET id_zone = 2 WHERE id_personnel IN (4, 5, 6);
UPDATE Personnel SET id_zone = 3 WHERE id_personnel IN (7, 8, 9);
UPDATE Personnel SET id_zone = 4 WHERE id_personnel IN (10);

/*Contrats (1 par personnel)*/
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (1, 4500.00, DATE '2022-01-01', NULL, 1, 1);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (2, 2800.00, DATE '2023-03-15', NULL, 2, 2);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (3, 2600.00, DATE '2023-06-01', NULL, 7, 3);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (4, 4444.00, DATE '2024-04-04', NULL, 4, 4);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (5, 3200.00, DATE '2023-09-01', NULL, 5, 5);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (6, 3400.00, DATE '2023-08-01', NULL, 3, 6);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (7, 2500.00, DATE '2024-01-10', NULL, 3, 7);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (8, 2550.00, DATE '2024-03-01', NULL, 3, 8);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (9, 2480.00, DATE '2024-06-15', NULL, 3, 9);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (10, 2620.00, DATE '2025-01-05', NULL, 3, 10);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (11, 2620.00, DATE '2025-01-05', NULL, 6, 11);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (12, 5896.00, DATE '2002-01-01', DATE '2003-01-01', 3, 6); 

/*Especes (9 especes)*/
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Panthera leo', 'Lion', 'N');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Loxodonta africana', 'Elephant', 'O');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Giraffa camelopardalis', 'Girafe', 'N');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Tyrannosaurus rex', 'Trex', 'O');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Velociraptor mongoliensis', 'Velociraptor', 'O');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Columba livia', 'Pigeon', 'O');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Rattus norvegicus', 'Rat', 'N');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Testudo graeca', 'Tortue', 'N');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Carcharodon carcharias', 'Requin blanc', 'O');

/*Enclos (7 enclos : 2 en zone 1 - Afrique, 1 en zone 2 - Asie, 1 en zone 3 - France, 2 en zone 4 - Dinosaure, 1 en zone 5 - Aquatique)*/
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (1, 48.8566, 2.3522, 5000.00, 1);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (2, 48.8570, 2.3530, 3500.00, 1);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (3, 48.8580, 2.3545, 4200.00, 2);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (4, 48.8480, 2.3593, 250.00, 3);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (5, 48.8550, 2.3430, 2000.00, 4);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (6, 48.8450, 2.3830, 5265.00, 4);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (7, 48.8560, 2.3585, 6800.00, 5);

/*Particularité et Possession (1 particularite par enclos)*/
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (1, 'Mare artificielle');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (2, 'Vegetation dense');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (3, 'Rochers grimpables');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (4, 'Sol sableux');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (5, 'Bassin profond');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (6, 'Nexus');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (7, 'Gros bout de viande');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (8, 'Zone d''ombre');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (9, 'Paroi renforcee');
INSERT INTO Particularite (id_particularite, libelle_particularite) VALUES (10, 'Plateforme en hauteur');

INSERT INTO Possede (id_enclos, id_particularite) VALUES (1, 1);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (2, 2);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (3, 3);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (4, 4);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (5, 6);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (6, 7);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (7, 5);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (1, 8);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (5, 9);
INSERT INTO Possede (id_enclos, id_particularite) VALUES (6, 10);

/*Animal
      Enclos 1 (Zone Afrique) : 2 Lions
      Enclos 2 (Zone Afrique) : 2 Elephants
      Enclos 3 (Zone Asie)    : 1 Girafe
      Enclos 4 (Zone France)    : 2 Rats et 1 Pigeon
*/
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1001, 'Simba',  DATE '2019-05-12', 80.50, NULL, NULL, 1, 'Panthera leo');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1002, 'Nala',   DATE '2020-03-08', 30.20, NULL, NULL, 1, 'Panthera leo');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1003, 'Dumbo',  DATE '2018-11-25', 99.99,  NULL, NULL, 2, 'Loxodonta africana');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1004, 'Babar',  DATE '2015-07-14', 99.99,  NULL, NULL, 2, 'Loxodonta africana');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1005, 'Melman', DATE '2021-01-30', 99.99,  NULL, NULL, 3, 'Giraffa camelopardalis');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1006, 'Ratatouille', DATE '2021-05-10', 1.6,  NULL, NULL, 4, 'Rattus norvegicus');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1007, 'Kled', DATE '2026-03-06', 1.75,  NULL, NULL, 4, 'Rattus norvegicus');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1008, 'Babouche', DATE '2005-09-11', 6.7,  NULL, NULL, 4, 'Columba livia');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1009, 'Trucmuche', DATE '1975-05-07', 36.7,  NULL, NULL, 5, 'Velociraptor mongoliensis');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1010, 'Skaarl', DATE '1974-01-08', 2.3,  NULL, NULL, 5, 'Velociraptor mongoliensis');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1011, 'Chogathe', DATE '1971-01-31', 2.3,  NULL, NULL, 6, 'Tyrannosaurus rex');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1012, 'Franklin', DATE '2018-12-07', 2.3,  NULL, NULL, 7, 'Testudo graeca');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1013, 'Fizz', DATE '2024-06-15', 2.3,  NULL, NULL, 7, 'Carcharodon carcharias');

/*Soigneur attitré aux 5 animaux (Camille Bernard, id_personnel=3)*/
INSERT INTO Attitre (RFID, id_personnel) VALUES (1001, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1002, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1003, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1004, 6);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1005, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1006, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1007, 6);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1008, 6);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1009, 6);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1010, 6);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1011, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1012, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1013, 3);

-- Specialisations du soigneur sur les 3 especes
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Panthera leo', 3);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Loxodonta africana', 6);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Giraffa camelopardalis', 7);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Rattus norvegicus', 8);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Columba livia', 9);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Velociraptor mongoliensis', 10);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Tyrannosaurus rex', 3);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Testudo graeca', 6);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Carcharodon carcharias', 7);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Panthera leo', 1);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Loxodonta africana', 1);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Velociraptor mongoliensis', 2);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Tyrannosaurus rex', 2);

/*Soins (1 soin par animal, tous prodigues par le soigneur id=3)*/
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (1, DATE '2025-02-01', 'Simple', 3, 1001);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (2, DATE '2025-02-05', 'Simple', 3,  1002);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (3, DATE '2025-02-10', 'Complexe', 3, 1003);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (4, DATE '2025-02-15', 'Simple', 3, 1004);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (5, DATE '2025-02-20', 'Complexe', 3, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (6, DATE '2025-02-22', 'Simple', 3, 1006);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (7, DATE '2025-02-23', 'Simple', 3, 1007);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (8, DATE '2025-02-24', 'Simple', 3, 1008);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (9, DATE '2025-02-25', 'Complexe', 3, 1009);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (10, DATE '2025-02-26', 'Complexe', 3, 1010);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (11, DATE '2025-02-27', 'Complexe', 3, 1011);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (12, DATE '2025-02-28', 'Simple', 3, 1012);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (13, DATE '2025-03-01', 'Complexe', 3, 1013);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (14, DATE '2025-03-05', 'Simple', 3, 1001);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (15, DATE '2025-03-06', 'Complexe', 3, 1003);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (16, DATE '2025-03-07', 'Simple', 3, 1006);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (17, DATE '2025-03-08', 'Complexe', 3, 1011);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (18, DATE '2025-03-09', 'Simple', 3, 1012);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (19, DATE '2025-03-02', 'Simple', 6, 1001);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (20, DATE '2025-03-02', 'Complexe', 3, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (21, DATE '2025-03-03', 'Complexe', 3, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (22, DATE '2025-03-04', 'Simple', 6, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (23, DATE '2025-03-05', 'Complexe', 3, 1001);

/*Nourriture (2 types partages par tous les animaux)*/
INSERT INTO Nourriture (id_nourriture, nom_nourriture) VALUES (1, 'Viande fraiche');
INSERT INTO Nourriture (id_nourriture, nom_nourriture) VALUES (2, 'Fruits et legumes');

/*Repas (1 par animal) + CONTIENT + CONSOMME + PREPARE*/
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (1, 'Repas Simba',  DATE '2025-02-01', 1001, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (2, 'Repas Nala',   DATE '2025-02-01', 1002, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (3, 'Repas Dumbo',  DATE '2025-02-01', 1003, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (4, 'Repas Babar',  DATE '2025-02-01', 1004, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (5, 'Repas Melman', DATE '2025-02-01', 1005, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (6, 'Repas Ratatouille', DATE '2025-02-02', 1006, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (7, 'Repas Kled', DATE '2025-02-02', 1007, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (8, 'Repas Babouche', DATE '2025-02-02', 1008, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (9, 'Repas Trucmuche', DATE '2025-02-02', 1009, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (10, 'Repas Skaarl', DATE '2025-02-02', 1010, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (11, 'Repas Chogathe', DATE '2025-02-02', 1011, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (12, 'Repas Franklin', DATE '2025-02-02', 1012, 3);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (13, 'Repas Fizz', DATE '2025-02-02', 1013, 3);


-- Chaque repas contient les 2 nourritures
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (1, 1, 3);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (1, 2, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (2, 1, 3);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (2, 2, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (3, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (3, 2, 5);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (4, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (4, 2, 5);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (5, 1, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (5, 2, 8);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (6, 2, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (7, 2, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (8, 2, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (9, 1, 4);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (10, 1, 4);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (11, 1, 10);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (12, 2, 3);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (13, 1, 6);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (6, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (7, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (8, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (9, 2, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (10, 2, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (11, 2, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (12, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (13, 2, 2);

-- Prestations (4)
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (1, 'Jouer avec animal',   'Argent');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (2, 'Nourrir animal', 'Argent');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (3, 'Photo avec animal', 'Bronze');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (4, 'Journée avec animal', 'Or');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (8, 'Parrainage Bronze - Franklin', 'Bronze');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (9, 'Parrainage Or - Dumbo', 'Or');

/*Visiteurs et parrainage
       Visiteur 1 (Antoine Leclerc) -> contribution Or     pour Simba (1001)
       Visiteur 2 (Marie Petit)     -> contribution Argent pour Simba (1001)
*/
INSERT INTO Visiteurs (id_visiteur, nom_visiteur, prenom_visiteur, numero_telephone) VALUES (1, 'Leclerc', 'Antoine', 0601020304);
INSERT INTO Visiteurs (id_visiteur, nom_visiteur, prenom_visiteur, numero_telephone) VALUES (2, 'Petit',   'Marie',   0605060708);
INSERT INTO Visiteurs (id_visiteur, nom_visiteur, prenom_visiteur, numero_telephone) VALUES (3, 'Dubois', 'Claire', 0611223344);
INSERT INTO Visiteurs (id_visiteur, nom_visiteur, prenom_visiteur, numero_telephone) VALUES (4, 'Martin', 'Lucas', 0622334455);

INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1001, 1, 3);
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1001, 2, 4);
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1012, 3, 8);
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1003, 4, 9);
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1004, 1, 4);

/*Boutique (1 boutique en zone 2, geree par la Directrice de magasin)*/
INSERT INTO Boutique (id_boutique, nom_boutique, type_boutique, id_personnel, id_zone) VALUES (1, 'La Savane Shop', 'Souvenirs', 5, 2);
INSERT INTO Boutique (id_boutique, nom_boutique, type_boutique, id_personnel, id_zone) VALUES (2, 'Jurassic Snacks', 'Restauration', 5, 4);

INSERT INTO Travaille (id_personnel, id_boutique) VALUES (4, 1);
INSERT INTO Travaille (id_personnel, id_boutique) VALUES (4, 2);

-- 2 chiffres d'affaires differents
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (1, DATE '2025-01-31', 3450.80, 1);
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (2, DATE '2025-02-28', 4120.50, 1);
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (3, DATE '2025-01-31', 2890.40, 2);
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (4, DATE '2025-02-28', 3315.90, 2);
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (5, DATE '2025-03-31', 4980.20, 1);
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (6, DATE '2025-03-31', 3522.75, 2);

/*Prestataire + réparation
       Prestataire 1 (BatiZoo)     -> Reparation 1 dans enclos 1
       Prestataire 2 (EcoRep)      -> Reparation 2 dans enclos 2
       Note : le lien Prestataire/Reparation passe par Prestations+Participe
*/
INSERT INTO Prestataire (id_prestataire, adresse_societe, nom_societe, telephone_societe) VALUES (1, '12 rue du Marteau Paris',   'BatiZoo SARL',    0612345678);
INSERT INTO Prestataire (id_prestataire, adresse_societe, nom_societe, telephone_societe) VALUES (2, '5 avenue des Artisans Lyon','EcoRep Services', 0698765432);
INSERT INTO Prestataire (id_prestataire, adresse_societe, nom_societe, telephone_societe) VALUES (3, '18 boulevard Victor Hugo Marseille', 'AquaFix Pro', 0677889900);
INSERT INTO Prestataire (id_prestataire, adresse_societe, nom_societe, telephone_societe) VALUES (4, '44 rue des Cerisiers Lille', 'SecureFence', 0655443322);

-- Reparations dans 2 enclos differents
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (1, 'Cloture', 'Remplacement des panneaux de cloture abimes',  1);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (6, 'Peinture', 'Mise en peinture de l''enclos sud', 1);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (2, 'Plomberie', 'Reparation du systeme d''arrosage automatique', 2);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (3, 'Nettoyage', 'Nettoyage complet et desinfection de l''enclos 4', 4);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (4, 'Securite', 'Renforcement des barrieres de l''enclos 5', 5);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (5, 'Bassin', 'Verification du systeme de filtration du bassin de l''enclos 7', 7);

-- Chaque prestation est liee a une reparation differente
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (1, 1);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (2, 2);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (1, 3);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (1, 4);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (2, 5);

-- Le technicien (id=2) supervise les 2 reparations
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 1);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 2);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 3);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 4);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 5);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 6);

/* 1 = directeur du zoo */
INSERT INTO Chef (id_personnel_manager_de, id_personnel_est_manager_par) VALUES (3, 8);
INSERT INTO Chef (id_personnel_manager_de, id_personnel_est_manager_par) VALUES (6, 9);

COMMIT;