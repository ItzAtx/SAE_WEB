/*Fonction*/
INSERT INTO Fonction (id_fonction, fonction) VALUES (1, 'Directeur');
INSERT INTO Fonction (id_fonction, fonction) VALUES (2, 'Technicien');
INSERT INTO Fonction (id_fonction, fonction) VALUES (3, 'Soigneur');
INSERT INTO Fonction (id_fonction, fonction) VALUES (4, 'Employe de magasin');
INSERT INTO Fonction (id_fonction, fonction) VALUES (5, 'Directeur de magasin');
INSERT INTO Fonction (id_fonction, fonction) VALUES (6, 'Comptable');
INSERT INTO Fonction (id_fonction, fonction) VALUES (7, 'Veterinaire');

/*Personnel (sans zone dans un premier temps, contrainte circulaire Zone_zoo <-> Personnel)*/
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (1, 'Belabbas',   'Selma',   '$2y$10$oXGmg9kqeBRJ2qO8HSb29elO1bbtIWEai5Q3PGg8Di8R8Wci1rIAq', 'selma.belabbas', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (2, 'Delloue',   'Alexandre',   '$2y$10$EdakURvwR4XsBiQUvY5/5OfzgRBN3tFJqKgQzB4KKIDp5j4BAIYb.', 'alexandre.delloue', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (3, 'LeBricoleur',   'Bob',   '$2y$10$kKYHlrojw2Wei4uWdBiiTuiZ5L2T.3INmonIZQpUAmNZAun3oRk36', 'bob.lebricoleur', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (4, 'Vauchel',  'Anthony',  '$2y$10$sjAYcs3fr8xOL9spJjLGq.Awg8fUfSR6SE7OJk/uktU1QtGzdF4BK', 'anthony.vauchel', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (5, 'Katr', 'Jhin',   '$2y$10$hdidfjcuq4wIqVOrTxfiYOqZE/DDOxKP2jelj8Jw.zT1cvNunKJ/i', 'jhin.katr', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (6, 'Midoriya', 'Deku',   '$2y$10$I3WLThJlrOJhz00RbPfM5OpSDVG1RyQJjbLOgAs/I5Dw4fGkEqtqq', 'deku.midoriya', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (7, 'Mamamia',   'Mario', '$2y$10$IGEvSSj3cVGQTxmp6L7xJ.RwxoDDglfzMoRFO5wZYz0lExWANpy96', 'mario.mamamia', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (8, 'Sorton', 'Virginie', '$2y$10$9J7stNITpwkSrKuEqMs4IOFZqsUoPfvAaATQ7nO1oGTH1UnHX/fyq', 'virginie.sorton', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (9, 'Pasteur', 'Louis', '$2y$10$dos6sXLgZ/lOpV/lXeTQuOonpIl.I3LTM3Yv7.PlFvU9RcTYbBQ9u', 'louis.pasteur', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (10, 'Raoult', 'Didier', '$2y$10$WYwPK62V7vLIb84lYAQpYO0u/T82KfQkWfMDC1L29TaBiKGxuIC4y', 'didier.raoult', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (11, 'Explosion', 'Macron', '$2y$10$VJreFyi39kDi68XcezyFieTk1FCkiEN6LDIKTp2qoYOxXaZeuX2ei', 'macron.explosion', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (12, 'Targon', 'Soraka', '$2y$10$JwaRxRO4P7dqqLroVahUYufmeupKoJeVI49r5QQel/GwnYcGVhnCC', 'soraka.targon', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (13, 'Delcroix', 'Lucas', '$2y$10$SzT0xY8JqLJm0FjFxqq/xu2sL2K2BOl1NcDhp0aDppDc0F3JUfe7.', 'lucas.delcroix', NULL, 'N');
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone, archiver_personnel) VALUES (14, 'Bonjour', 'Aurevoir', '$2y$10$O4NJDwRvIiIzR5FH.v3Zxus98833J8xt4NzgoaBUNvae9QZ3Pw06e', 'aurevoir.bonjour', NULL, 'N');

/*Zones (5 zones, responsable = Soigneurs id=1)*/
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (1, 'Zone Afrique', 4);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (2, 'Zone Asie', 8);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (3, 'Zone France', 9);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (4, 'Zone Dinosaure', 10);
INSERT INTO Zone_zoo (id_zone, libelle_zone, id_personnel) VALUES (5, 'Zone Aquatique', 11);

-- Rattachement des personnels a leur zone
UPDATE Personnel SET id_zone = 1 WHERE id_personnel IN (1, 2, 3);
UPDATE Personnel SET id_zone = 2 WHERE id_personnel IN (4, 5, 6);
UPDATE Personnel SET id_zone = 3 WHERE id_personnel IN (7, 8, 9);
UPDATE Personnel SET id_zone = 4 WHERE id_personnel IN (10, 11, 12);
UPDATE Personnel SET id_zone = 5 WHERE id_personnel IN (13, 14, 15);

/*Contrats (1 par personnel)*/
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (1, 9000.99, DATE '2022-01-01', NULL, 1, 1);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (2, 1200.00, DATE '2023-03-15', NULL, 2, 2);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (3, 1200.00, DATE '2023-06-01', NULL, 2, 3);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (4, 3500.00, DATE '2024-04-04', NULL, 3, 4);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (5, 4444.00, DATE '2023-09-01', NULL, 4, 5);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (6, 1100.00, DATE '2023-08-01', NULL, 4, 6);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (7, 8999.99, DATE '2024-01-10', NULL, 5, 7);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (8, 2550.00, DATE '2024-03-01', NULL, 3, 9);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (9, 2480.00, DATE '2024-06-15', NULL, 3, 10);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (10, 2620.00, DATE '2025-01-05', NULL, 3, 11);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (11, 2600.00, DATE '2025-01-05', NULL, 3, 12);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (12, 1200.00, DATE '2025-01-05', NULL, 3, 8);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (13, 1100.00, DATE '2002-01-01', DATE '2003-01-01', 3, 8);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (14, 180.00, DATE '2002-01-01', NULL, 6, 13);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (15, 8998.99, DATE '2002-01-01', NULL, 7, 14); 

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
    VALUES (1001, 'Syndra',  DATE '2019-05-12', 80.50, NULL, NULL, 1, 'Panthera leo');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1002, 'Nidalee',   DATE '2020-03-08', 30.20, NULL, NULL, 1, 'Panthera leo');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1003, 'Megumi',  DATE '2018-11-25', 99.99,  NULL, NULL, 2, 'Loxodonta africana');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1004, 'Babar',  DATE '2015-07-14', 99.99,  NULL, NULL, 2, 'Loxodonta africana');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1005, 'Girafarig', DATE '2021-01-30', 99.99,  NULL, NULL, 3, 'Giraffa camelopardalis');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1006, 'Sabrina', DATE '2021-05-10', 1.6,  NULL, NULL, 4, 'Rattus norvegicus');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1007, 'Kled', DATE '2026-03-06', 1.75,  NULL, NULL, 4, 'Rattus norvegicus');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1008, 'Babouche', DATE '2005-09-11', 6.7,  NULL, NULL, 4, 'Columba livia');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1009, 'Polymorphe', DATE '1975-05-07', 36.7,  NULL, NULL, 5, 'Velociraptor mongoliensis');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1010, 'Skaarl', DATE '1974-01-08', 2.3,  NULL, NULL, 5, 'Velociraptor mongoliensis');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1011, 'Chogathe', DATE '1971-01-31', 2.3,  NULL, NULL, 6, 'Tyrannosaurus rex');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1012, 'Rammus', DATE '2018-12-07', 2.3,  NULL, NULL, 7, 'Testudo graeca');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1013, 'Fizz', DATE '2024-06-15', 2.3,  NULL, NULL, 7, 'Carcharodon carcharias');

/*Soigneur attitré aux 5 animaux (Camille Bernard, id_personnel=3)*/
INSERT INTO Attitre (RFID, id_personnel) VALUES (1001, 8);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1002, 8);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1003, 9);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1004, 10);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1005, 9);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1006, 9);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1007, 4);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1008, 4);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1009, 11);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1010, 12);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1011, 12);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1012, 12);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1013, 12);

-- Specialisations du soigneur sur les 3 especes
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Panthera leo', 4);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Loxodonta africana', 8);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Giraffa camelopardalis', 7);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Rattus norvegicus', 8);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Columba livia', 9);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Velociraptor mongoliensis', 10);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Tyrannosaurus rex', 11);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Testudo graeca', 12);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Carcharodon carcharias', 7);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Panthera leo', 8);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Loxodonta africana', 11);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Velociraptor mongoliensis', 8);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Tyrannosaurus rex', 4);

/*Soins (1 soin par animal, tous prodigues par le soigneur id=3)*/
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (1, DATE '2025-02-01', 'Simple', 4, 1001);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (2, DATE '2025-02-05', 'Simple', 8,  1002);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (3, DATE '2025-02-10', 'Simple', 10, 1003);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (4, DATE '2025-02-15', 'Simple', 11, 1004);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (5, DATE '2025-02-20', 'Complexe', 14, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (6, DATE '2025-02-22', 'Simple', 12, 1006);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (7, DATE '2025-02-23', 'Simple', 9, 1007);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (8, DATE '2025-02-24', 'Simple', 8, 1008);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (9, DATE '2025-02-25', 'Simple', 4, 1009);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (10, DATE '2025-02-26', 'Simple', 11, 1010);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (11, DATE '2025-02-27', 'Simple', 8, 1011);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (12, DATE '2025-02-28', 'Simple', 4, 1012);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (13, DATE '2025-03-01', 'Simple', 10, 1013);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (14, DATE '2025-03-05', 'Simple', 12, 1001);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (15, DATE '2025-03-06', 'Complexe', 14, 1003);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (16, DATE '2025-03-07', 'Simple', 4, 1006);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (17, DATE '2025-03-08', 'Simple', 8, 1011);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (18, DATE '2025-03-09', 'Simple', 11, 1012);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (19, DATE '2025-03-02', 'Simple', 12, 1001);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (20, DATE '2025-03-02', 'Simple', 4, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (21, DATE '2025-03-03', 'Simple', 10, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (22, DATE '2025-03-04', 'Simple', 8, 1005);
INSERT INTO Soins (id_soin, date_soin, complexite, id_personnel, RFID) VALUES (23, DATE '2025-03-05', 'Complexe', 14, 1001);

/*Nourriture (2 types partages par tous les animaux)*/
INSERT INTO Nourriture (id_nourriture, nom_nourriture) VALUES (1, 'Viande fraiche');
INSERT INTO Nourriture (id_nourriture, nom_nourriture) VALUES (2, 'Fruits et legumes');
INSERT INTO Nourriture (id_nourriture, nom_nourriture) VALUES (3, 'Tasty Crousty');
INSERT INTO Nourriture (id_nourriture, nom_nourriture) VALUES (4, 'Bonbons');
INSERT INTO Nourriture (id_nourriture, nom_nourriture) VALUES (5, 'Poro Snack');

/*Repas (1 par animal) + CONTIENT + CONSOMME + PREPARE*/
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (1, 'Repas Simba',  DATE '2025-02-01', 1001, 4);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (2, 'Repas Nala',   DATE '2025-02-01', 1002, 8);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (3, 'Repas Dumbo',  DATE '2025-02-01', 1003, 10);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (4, 'Repas Babar',  DATE '2025-02-01', 1004, 11);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (5, 'Repas Melman', DATE '2025-02-01', 1005, 12);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (6, 'Repas Ratatouille', DATE '2025-02-02', 1006, 12);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (7, 'Repas Kled', DATE '2025-02-02', 1007, 8);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (8, 'Repas Babouche', DATE '2025-02-02', 1008, 4);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (9, 'Repas Trucmuche', DATE '2025-02-02', 1009, 12);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (10, 'Repas Skaarl', DATE '2025-02-02', 1010, 10);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (11, 'Repas Chogathe', DATE '2025-02-02', 1011, 8);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (12, 'Repas Franklin', DATE '2025-02-02', 1012, 9);
INSERT INTO Repas (id_repas, nom_repas, date_repas, RFID, id_personnel) VALUES (13, 'Repas Fizz', DATE '2025-02-02', 1013, 9);


-- Chaque repas contient les 2 nourritures
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (1, 1, 3);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (1, 2, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (2, 3, 3);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (2, 2, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (3, 4, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (3, 2, 5);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (4, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (4, 5, 5);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (5, 1, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (5, 2, 8);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (6, 2, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (7, 4, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (9, 1, 4);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (11, 1, 10);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (12, 2, 3);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (13, 3, 6);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (6, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (7, 1, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (8, 2, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (9, 2, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (10, 5, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (11, 2, 2);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (12, 5, 1);
INSERT INTO Contient (id_repas, id_nourriture, quantite) VALUES (13, 2, 2);

-- Prestations (4)
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (1, 'Jouer avec animal',   'Argent');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (2, 'Nourrir animal', 'Argent');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (3, 'Photo avec animal', 'Bronze');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (4, 'Journée avec animal', 'Or');
INSERT INTO Prestations (id_prestation, libelle_prestation, niveau_contribution) VALUES (5, 'Manger l''animal', 'Or');

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
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1012, 3, 5);
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1003, 4, 1);
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1004, 1, 4);

/*Boutique (1 boutique en zone 2, geree par la Directrice de magasin)*/
INSERT INTO Boutique (id_boutique, nom_boutique, type_boutique, id_personnel, id_zone) VALUES (1, 'La Savane Shop', 'Souvenirs', 7, 2);
INSERT INTO Boutique (id_boutique, nom_boutique, type_boutique, id_personnel, id_zone) VALUES (2, 'Jurassic Snacks', 'Restauration', 7, 4);

INSERT INTO Travaille (id_personnel, id_boutique) VALUES (5, 1);
INSERT INTO Travaille (id_personnel, id_boutique) VALUES (6, 2);

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
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (2, 'Plomberie', 'Reparation du systeme d''arrosage automatique', 2);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (3, 'Nettoyage', 'Nettoyage complet et desinfection de l''enclos 4', 4);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (4, 'Securite', 'Renforcement des barrieres de l''enclos 5', 5);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (5, 'Bassin', 'Verification du systeme de filtration du bassin de l''enclos 7', 7);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle_reparation, id_enclos) VALUES (6, 'Peinture', 'Mise en peinture de l''enclos sud', 1);

-- Chaque prestation est liee a une reparation differente
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (1, 1);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (2, 2);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (1, 3);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (1, 4);
INSERT INTO Participe (id_prestataire, id_reparation) VALUES (2, 5);

-- Le technicien (id=2) supervise les 2 reparations
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 1);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (3, 2);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 3);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 4);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 5);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (3, 6);

INSERT INTO Chef (id_personnel_manager_de, id_personnel_est_manager_par) VALUES (4, 12);

COMMIT;
