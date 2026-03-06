/*Fonction*/
INSERT INTO Fonction (id_fonction, fonction) VALUES (1, 'Directeur');
INSERT INTO Fonction (id_fonction, fonction) VALUES (2, 'Technicien');
INSERT INTO Fonction (id_fonction, fonction) VALUES (3, 'Soigneur');
INSERT INTO Fonction (id_fonction, fonction) VALUES (4, 'Employe de magasin');
INSERT INTO Fonction (id_fonction, fonction) VALUES (5, 'Directeur de magasin');


/*Personnel (sans zone dans un premier temps, contrainte circulaire Zone_zoo <-> Personnel)*/
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone) VALUES (1, 'Martin',   'Sophie',   '$2a$12$NodASkvJSnjP1H3FXTEYeerpni7REdmEDCIYWwvMI0Az3RP7Y5cqi', 'sophie.martin', NULL);
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone) VALUES (2, 'Dupont',   'Julien',   '$2a$12$NodASkvJSnjP1H3FXTEYeerpni7REdmEDCIYWwvMI0Az3RP7Y5cqi', 'julien.dupont', NULL);
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone) VALUES (3, 'Bernard',  'Camille',  '$2a$12$NodASkvJSnjP1H3FXTEYeerpni7REdmEDCIYWwvMI0Az3RP7Y5cqi', 'camille.bernard', NULL);
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone) VALUES (4, 'Lefebvre', 'Thomas',   '$2a$12$NodASkvJSnjP1H3FXTEYeerpni7REdmEDCIYWwvMI0Az3RP7Y5cqi', 'thomas.lefebvre', NULL);
INSERT INTO Personnel (id_personnel, nom_personnel, prenom_personnel, mot_de_passe, id_connexion, id_zone) VALUES (5, 'Moreau',   'Isabelle', '$2a$12$NodASkvJSnjP1H3FXTEYeerpni7REdmEDCIYWwvMI0Az3RP7Y5cqi', 'isabelle.moreau', NULL);

/*Zones (2 zones, responsable = Directeur id=1)*/
INSERT INTO Zone_zoo (id_zone, libelle, id_personnel) VALUES (1, 'Zone Afrique', 1);
INSERT INTO Zone_zoo (id_zone, libelle, id_personnel) VALUES (2, 'Zone Asie',    1);

-- Rattachement des personnels a leur zone
UPDATE Personnel SET id_zone = 1 WHERE id_personnel IN (1, 2, 3);
UPDATE Personnel SET id_zone = 2 WHERE id_personnel IN (4, 5);

/*Contrats (1 par personnel)*/
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (1, 4500.00, DATE '2022-01-01', NULL, 1, 1);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (2, 2800.00, DATE '2023-03-15', NULL, 2, 2);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (3, 2600.00, DATE '2023-06-01', NULL, 3, 3);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (4, 1900.00, DATE '2024-01-10', NULL, 4, 4);
INSERT INTO Contrat (id_contrat, salaire, date_debut, date_fin, id_fonction, id_personnel) VALUES (5, 3200.00, DATE '2023-09-01', NULL, 5, 5);

/*Especes (3 especes)*/
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Panthera leo',           'Lion',     'N');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Loxodonta africana',     'Elephant', 'O');
INSERT INTO Espece (nom_latin, nom_usuel, menace) VALUES ('Giraffa camelopardalis', 'Girafe',   'N');

/*Enclos (3 enclos : 2 en zone 1 - Afrique, 1 en zone 2 - Asie)*/
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (1, 48.8566, 2.3522, 5000.00, 1);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (2, 48.8570, 2.3530, 3500.00, 1);
INSERT INTO Enclos (id_enclos, latitude, longitude, surface, id_zone) VALUES (3, 48.8580, 2.3545, 4200.00, 2);

/*Particularité et Possession (1 particularite par enclos)*/
INSERT INTO Particularite (libelle_particularite) VALUES ('Mare artificielle');
INSERT INTO Particularite (libelle_particularite) VALUES ('Vegetation dense');
INSERT INTO Particularite (libelle_particularite) VALUES ('Rochers grimpables');

INSERT INTO Possede (id_enclos, libelle_particularite) VALUES (1, 'Mare artificielle');
INSERT INTO Possede (id_enclos, libelle_particularite) VALUES (2, 'Vegetation dense');
INSERT INTO Possede (id_enclos, libelle_particularite) VALUES (3, 'Rochers grimpables');

/*Animal
      Enclos 1 (Zone Afrique) : 2 Lions
      Enclos 2 (Zone Afrique) : 2 Elephants
      Enclos 3 (Zone Asie)    : 1 Girafe
*/
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1001, 'Simba',  DATE '2019-05-12', 180.50, NULL, NULL, 1, 'Panthera leo');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1002, 'Nala',   DATE '2020-03-08', 130.20, NULL, NULL, 1, 'Panthera leo');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1003, 'Dumbo',  DATE '2018-11-25', 99.99,  NULL, NULL, 2, 'Loxodonta africana');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1004, 'Babar',  DATE '2015-07-14', 99.99,  NULL, NULL, 2, 'Loxodonta africana');
INSERT INTO Animal (RFID, nom_animal, date_naissance, poids, RFID_a_pour_pere, RFID_a_pour_mere, id_enclos, nom_latin)
    VALUES (1005, 'Melman', DATE '2021-01-30', 99.99,  NULL, NULL, 3, 'Giraffa camelopardalis');

/*Soigneur attitré aux 5 animaux (Camille Bernard, id_personnel=3)*/
INSERT INTO Attitre (RFID, id_personnel) VALUES (1001, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1002, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1003, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1004, 3);
INSERT INTO Attitre (RFID, id_personnel) VALUES (1005, 3);

-- Specialisations du soigneur sur les 3 especes
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Panthera leo',           3);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Loxodonta africana',     3);
INSERT INTO Specialiser (nom_latin, id_personnel) VALUES ('Giraffa camelopardalis', 3);

/*Soins (1 soin par animal, tous prodigues par le soigneur id=3)*/
INSERT INTO Soins (id_soin, date_soin, complexite, RFID) VALUES (1, DATE '2025-02-01', 'Simple', 1001);
INSERT INTO Soins (id_soin, date_soin, complexite, RFID) VALUES (2, DATE '2025-02-05', 'Simple',  1002);
INSERT INTO Soins (id_soin, date_soin, complexite, RFID) VALUES (3, DATE '2025-02-10', 'Complexe',  1003);
INSERT INTO Soins (id_soin, date_soin, complexite, RFID) VALUES (4, DATE '2025-02-15', 'Simple', 1004);
INSERT INTO Soins (id_soin, date_soin, complexite, RFID) VALUES (5, DATE '2025-02-20', 'Complexe',  1005);

INSERT INTO Prodigue (id_personnel, id_soin) VALUES (3, 1);
INSERT INTO Prodigue (id_personnel, id_soin) VALUES (3, 2);
INSERT INTO Prodigue (id_personnel, id_soin) VALUES (3, 3);
INSERT INTO Prodigue (id_personnel, id_soin) VALUES (3, 4);
INSERT INTO Prodigue (id_personnel, id_soin) VALUES (3, 5);

/*Nourriture (2 types partages par tous les animaux)*/
INSERT INTO Nourriture (id_nourriture, nom) VALUES (1, 'Viande fraiche');
INSERT INTO Nourriture (id_nourriture, nom) VALUES (2, 'Fruits et legumes');

/*Repas (1 par animal) + CONTIENT + CONSOMME + PREPARE*/
INSERT INTO Repas (id_repas, nom_repas, date_repas) VALUES (1, 'Repas Simba',  DATE '2025-02-01');
INSERT INTO Repas (id_repas, nom_repas, date_repas) VALUES (2, 'Repas Nala',   DATE '2025-02-01');
INSERT INTO Repas (id_repas, nom_repas, date_repas) VALUES (3, 'Repas Dumbo',  DATE '2025-02-01');
INSERT INTO Repas (id_repas, nom_repas, date_repas) VALUES (4, 'Repas Babar',  DATE '2025-02-01');
INSERT INTO Repas (id_repas, nom_repas, date_repas) VALUES (5, 'Repas Melman', DATE '2025-02-01');

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

-- Chaque animal consomme son repas
INSERT INTO Consomme (RFID, id_repas) VALUES (1001, 1);
INSERT INTO Consomme (RFID, id_repas) VALUES (1002, 2);
INSERT INTO Consomme (RFID, id_repas) VALUES (1003, 3);
INSERT INTO Consomme (RFID, id_repas) VALUES (1004, 4);
INSERT INTO Consomme (RFID, id_repas) VALUES (1005, 5);

-- Le soigneur prepare tous les repas
INSERT INTO Prepare (id_personnel, id_repas) VALUES (3, 1);
INSERT INTO Prepare (id_personnel, id_repas) VALUES (3, 2);
INSERT INTO Prepare (id_personnel, id_repas) VALUES (3, 3);
INSERT INTO Prepare (id_personnel, id_repas) VALUES (3, 4);
INSERT INTO Prepare (id_personnel, id_repas) VALUES (3, 5);

/*Boutique (1 boutique en zone 2, geree par la Directrice de magasin)*/
INSERT INTO Boutique (id_boutique, nom_boutique, type_boutique, id_personnel, id_zone)
    VALUES (1, 'La Savane Shop', 'Souvenirs', 5, 2);

INSERT INTO Travaille (id_personnel, id_boutique) VALUES (4, 1);
INSERT INTO Travaille (id_personnel, id_boutique) VALUES (5, 1);

-- 2 chiffres d'affaires differents
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (1, DATE '2025-01-31', 3450.80, 1);
INSERT INTO Chiffre_affaire (id_ca, date_ca, montant, id_boutique) VALUES (2, DATE '2025-02-28', 4120.50, 1);

/*Prestataire + réparation
       Prestataire 1 (BatiZoo)     -> Reparation 1 dans enclos 1
       Prestataire 2 (EcoRep)      -> Reparation 2 dans enclos 2
       Note : le lien Prestataire/Reparation passe par Prestations+Participe
*/
INSERT INTO Prestataire (id_prestataire, adresse, nom_societte, telephone_prestataire)
    VALUES (1, '12 rue du Marteau Paris',   'BatiZoo SARL',    0612345678);
INSERT INTO Prestataire (id_prestataire, adresse, nom_societte, telephone_prestataire)
    VALUES (2, '5 avenue des Artisans Lyon','EcoRep Services', 0698765432);

-- Prestations associees aux travaux (niveau Bronze)
INSERT INTO Prestations (id_prestation, libelle, niveau_contribution) VALUES (1, 'Refection cloture enclos 1',   'Bronze');
INSERT INTO Prestations (id_prestation, libelle, niveau_contribution) VALUES (2, 'Reparation arrosage enclos 2', 'Bronze');

-- Reparations dans 2 enclos differents
INSERT INTO Reparation (id_reparation, nature_reparation, libelle, id_enclos)
    VALUES (1, 'Cloture',   'Remplacement des panneaux de cloture abimes',  1);
INSERT INTO Reparation (id_reparation, nature_reparation, libelle, id_enclos)
    VALUES (2, 'Plomberie', 'Reparation du systeme d''arrosage automatique', 2);

-- Chaque prestation est liee a une reparation differente
INSERT INTO Participe (id_prestation, id_reparation) VALUES (1, 1);
INSERT INTO Participe (id_prestation, id_reparation) VALUES (2, 2);

-- Le technicien (id=2) supervise les 2 reparations
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 1);
INSERT INTO Entretient (id_personnel, id_reparation) VALUES (2, 2);

/*Visiteurs et parrainage
       Visiteur 1 (Antoine Leclerc) -> contribution Or     pour Simba (1001)
       Visiteur 2 (Marie Petit)     -> contribution Argent pour Simba (1001)
*/
INSERT INTO Visiteurs (id_visiteur, nom_visiteur, prenom_visiteur, numero_telephone)
    VALUES (1, 'Leclerc', 'Antoine', 0601020304);
INSERT INTO Visiteurs (id_visiteur, nom_visiteur, prenom_visiteur, numero_telephone)
    VALUES (2, 'Petit',   'Marie',   0605060708);

INSERT INTO Prestations (id_prestation, libelle, niveau_contribution) VALUES (3, 'Parrainage Or - Simba',     'Or');
INSERT INTO Prestations (id_prestation, libelle, niveau_contribution) VALUES (4, 'Parrainage Argent - Simba', 'Argent');

INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1001, 1, 3);
INSERT INTO Parrainer (RFID, id_visiteur, id_prestation) VALUES (1001, 2, 4);