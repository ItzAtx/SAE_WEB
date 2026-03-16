/* Création des tables */

/*===================================*/
/* TABLE INDEPENDANTE */
/* Ce tables n'ont pas d'attributs dépendants d'une autre table*/
/*===================================*/
CREATE TABLE Espece (
    nom_latin VARCHAR(50),
    nom_usuel VARCHAR(50),
    menace CHAR(1) 
    CONSTRAINT menace_check CHECK (menace IN ('O', 'N')),
    CONSTRAINT pk_espece PRIMARY KEY (nom_latin)
);

CREATE TABLE Particularite (
    id_particularite INT,
    libelle_particularite VARCHAR(50),
    CONSTRAINT pk_particularite PRIMARY KEY (id_particularite)
);

CREATE TABLE Prestataire (
    id_prestataire INT,
    adresse_societe VARCHAR(50),
    nom_societe VARCHAR(50),
    telephone_societe NUMBER(10),
    CONSTRAINT pk_id_prestataire PRIMARY KEY (id_prestataire)
);

CREATE TABLE Visiteurs (
    id_visiteur INT,
    nom_visiteur VARCHAR(50),
    prenom_visiteur VARCHAR(50),
    numero_telephone NUMBER(10),
    CONSTRAINT pk_id_visiteurs PRIMARY KEY (id_visiteur)
);

CREATE TABLE Prestations (
    id_prestation int,
    libelle_prestation VARCHAR(50),
    niveau_contribution VARCHAR(6),
    CONSTRAINT contribution_check CHECK (niveau_contribution IN ('Bronze', 'Argent', 'Or')),
    CONSTRAINT pk_id_prestation PRIMARY KEY (id_prestation)
);

CREATE TABLE Fonction (
    id_fonction INT,
    fonction VARCHAR(100),
    CONSTRAINT pk_id_fonction PRIMARY KEY (id_fonction)
);

CREATE TABLE Nourriture (
    id_nourriture INT,
    nom_nourriture VARCHAR(50),
    CONSTRAINT pk_id_nourriture PRIMARY KEY (id_nourriture)
);

/*===================================*/
/* TABLES AVEC CLEFS ETRANGERES*/
/* Les clefs étrangères de ces tables provoquent des références circulaires.*/
/* Les contraintes sont ajoutés en fin de script par nécessité et facilité*/
/*===================================*/

CREATE TABLE Animal (
    RFID INT,
    nom_animal VARCHAR(50),
    date_naissance DATE,
    poids DECIMAL(4,2),
    RFID_a_pour_pere INT,
    RFID_a_pour_mere INT,
    id_enclos INT,
    nom_latin VARCHAR(50),
    archiver_animal CHAR(1),
    CONSTRAINT archiver_animal_check CHECK (archiver_animal IN ('O', 'N')),
    CONSTRAINT pk_animal PRIMARY KEY (RFID),
    CONSTRAINT fk_animal_rfid_pere FOREIGN KEY (RFID_a_pour_pere) REFERENCES Animal(RFID),
    CONSTRAINT fk_animal_rfid_mere FOREIGN KEY (RFID_a_pour_mere) REFERENCES Animal(RFID),
    CONSTRAINT fk_animal_nom_latin FOREIGN KEY (nom_latin) REFERENCES Espece(nom_latin)
);

CREATE TABLE Enclos (
    id_enclos INT,
    latitude FLOAT,
    longitude FLOAT,
    surface FLOAT,
    id_zone INT,
    CONSTRAINT pk_id_enclos PRIMARY KEY (id_enclos)
);

CREATE TABLE Zone_zoo (
    id_zone INT,
    libelle_zone VARCHAR(50),
    id_personnel INT,
    CONSTRAINT pk_id_zone PRIMARY KEY (id_zone)
);

CREATE TABLE Personnel (
    id_personnel INT,
    nom_personnel VARCHAR(50) NOT NULL,
    prenom_personnel VARCHAR(50),
    mot_de_passe VARCHAR(255) NOT NULL,
    id_connexion VARCHAR(100),
    id_zone INT,
    archiver_personnel CHAR(1),
    CONSTRAINT archiver_personnel_check CHECK (archiver_personnel IN ('O', 'N')),
    CONSTRAINT pk_id_personnel PRIMARY KEY (id_personnel)
);

CREATE TABLE Boutique (
    id_boutique INT,
    nom_boutique VARCHAR(50),
    type_boutique VARCHAR(50),
    id_personnel INT,
    id_zone INT,
    CONSTRAINT pk_id_boutique PRIMARY KEY (id_boutique)
);

CREATE TABLE Chiffre_affaire (
    id_ca INT,
    date_ca DATE,
    montant FLOAT,
    id_boutique INT,
    CONSTRAINT pk_ca PRIMARY KEY (id_ca, id_boutique)
);

CREATE TABLE Reparation (
    id_reparation INT,
    nature_reparation VARCHAR(50),
    libelle_reparation VARCHAR(100),
    id_enclos INT,
    CONSTRAINT pk_id_reparation PRIMARY KEY (id_reparation)
);

CREATE TABLE Soins (
    id_soin INT,
    date_soin DATE,
    complexite VARCHAR(20),
    id_personnel INT,
    RFID INT,
    CONSTRAINT pk_soins PRIMARY KEY (id_soin)
);

CREATE TABLE Repas (
    id_repas INT,
    nom_repas VARCHAR(50),
    date_repas DATE,
    RFID INT,
    id_personnel INT,
    CONSTRAINT pk_id_repas PRIMARY KEY (id_repas)
);

CREATE TABLE Contrat (
    id_contrat INT,
    salaire DECIMAL(10, 2),
    date_debut DATE,
    date_fin DATE,
    id_fonction INT,
    id_personnel INT,
    CONSTRAINT pk_id_contrat PRIMARY KEY (id_contrat)
);

/*===================================*/
/* TABLE D'ASSOCIATION*/
/* Ces tables sont issues des associations*/
/*===================================*/

CREATE TABLE Possede (
    id_enclos INT,
    id_particularite INT NOT NULL,
    CONSTRAINT pk_possede PRIMARY KEY (id_enclos, id_particularite),
    CONSTRAINT fk_possede_id_enclos FOREIGN KEY (id_enclos) REFERENCES Enclos(id_enclos),
    CONSTRAINT fk_possede_id_particularite FOREIGN KEY (id_particularite) REFERENCES Particularite(id_particularite)
);

CREATE TABLE Parrainer (
    RFID INT,
    id_visiteur INT,
    id_prestation INT,
    CONSTRAINT pk_parrainer PRIMARY KEY (RFID, id_visiteur, id_prestation),
    CONSTRAINT fk_parrainer_RFID FOREIGN KEY (RFID) REFERENCES Animal(RFID),
    CONSTRAINT fk_parrainer_id_visiteur FOREIGN KEY (id_visiteur) REFERENCES Visiteurs(id_visiteur),
    CONSTRAINT fk_parrainer_id_prestation FOREIGN KEY (id_prestation) REFERENCES Prestations(id_prestation)
);

CREATE TABLE Cohabiter (
    nom_latin_est_cohabiter_par VARCHAR(50),
    nom_latin_cohabite_avec VARCHAR(50),
    CONSTRAINT pk_cohabite PRIMARY KEY (nom_latin_est_cohabiter_par, nom_latin_cohabite_avec),
    CONSTRAINT fk_cohabite_nom_latin_est_cohabiter_par FOREIGN KEY (nom_latin_est_cohabiter_par) REFERENCES Espece(nom_latin),
    CONSTRAINT fk_cohabite_nom_latin_cohabite_avec FOREIGN KEY (nom_latin_cohabite_avec) REFERENCES Espece(nom_latin)
);

CREATE TABLE Specialiser (
    nom_latin VARCHAR(50),
    id_personnel INT,
    CONSTRAINT pk_specialiser PRIMARY KEY (nom_latin, id_personnel),
    CONSTRAINT fk_specialiser_nom_latin FOREIGN KEY (nom_latin) REFERENCES Espece(nom_latin),
    CONSTRAINT fk_specialiser_id_personnel FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel) 
);

CREATE TABLE Chef (
    id_personnel_manager_de INT,
    id_personnel_est_manager_par INT,
    CONSTRAINT pk_chef PRIMARY KEY (id_personnel_manager_de, id_personnel_est_manager_par),
    CONSTRAINT fk_chef_id_personnel_manager_de FOREIGN KEY (id_personnel_manager_de) REFERENCES Personnel(id_personnel),
    CONSTRAINT fk_chef_id_personnel_est_manager_par FOREIGN KEY (id_personnel_est_manager_par) REFERENCES Personnel(id_personnel)
);

CREATE TABLE Travaille (
    id_personnel INT,
    id_boutique INT,
    CONSTRAINT pk_travaille PRIMARY KEY (id_personnel, id_boutique),
    CONSTRAINT fk_travaille_id_personnel FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel),
    CONSTRAINT fk_travaille_id_boutique FOREIGN KEY (id_boutique) REFERENCES Boutique(id_boutique)
);

CREATE TABLE Entretient (
    id_personnel INT,
    id_reparation INT,
    CONSTRAINT pk_entretient PRIMARY KEY (id_personnel, id_reparation),
    CONSTRAINT fk_entretient_id_personnel FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel),
    CONSTRAINT fk_entretient_id_reparation FOREIGN KEY (id_reparation) REFERENCES Reparation(id_reparation)
);

CREATE TABLE Participe (
    id_prestation INT,
    id_reparation INT,
    CONSTRAINT pk_participe PRIMARY KEY (id_prestation, id_reparation),
    CONSTRAINT fk_participe_id_prestation FOREIGN KEY (id_prestation) REFERENCES Prestations(id_prestation),
    CONSTRAINT fk_participe_id_reparation FOREIGN KEY (id_reparation) REFERENCES Reparation(id_reparation)
);

CREATE TABLE Attitre (
    RFID INT,
    id_personnel INT,
    CONSTRAINT pk_attitre PRIMARY KEY (RFID, id_personnel),
    CONSTRAINT fk_attitre_rfid FOREIGN KEY (RFID) REFERENCES Animal(RFID),
    CONSTRAINT fk_attitre_id_personnel FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel)
);

CREATE TABLE Contient (
    id_repas INT,
    id_nourriture INT,
    quantite INT,
    CONSTRAINT pk_contient PRIMARY KEY (id_repas, id_nourriture),
    CONSTRAINT fk_contient_id_repas FOREIGN KEY (id_repas) REFERENCES Repas(id_repas),
    CONSTRAINT fk_contient_id_nourriture FOREIGN KEY (id_nourriture) REFERENCES Nourriture(id_nourriture)
);

/*===================================*/
/* AJOUT DES CONTRAINTES DE CLEF ETRANGERE*/
/* On ajoute les contraites de clef étrangères pour*/
/* les tables ayant des références circulaires*/
/*===================================*/

/*Animal*/
ALTER TABLE Animal
ADD CONSTRAINT fk_id_enclos
FOREIGN KEY (id_enclos) REFERENCES Enclos(id_enclos);

/*Enclos*/
ALTER TABLE Enclos
ADD CONSTRAINT fk_id_zone
FOREIGN KEY (id_zone) REFERENCES Zone_zoo(id_zone);

/*Zone*/
ALTER TABLE Zone_zoo
ADD CONSTRAINT fk_id_personnel_zone_zoo
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Boutique*/
ALTER TABLE Boutique
ADD CONSTRAINT fk_id_zone_boutique
FOREIGN KEY (id_zone) REFERENCES Zone_zoo(id_zone);

ALTER TABLE Boutique
ADD CONSTRAINT fk_id_personnel_boutique
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Chiffre d'affaire*/
ALTER TABLE Chiffre_affaire
ADD CONSTRAINT fk_id_boutique
FOREIGN KEY (id_boutique) REFERENCES Boutique(id_boutique);

/*Reparation*/
ALTER TABLE Reparation
ADD CONSTRAINT fk_id_enclos_reparation
FOREIGN KEY (id_enclos) REFERENCES Enclos(id_enclos);

/*Personnel*/
ALTER TABLE Personnel
ADD CONSTRAINT fk_id_zone_personnel
FOREIGN KEY (id_zone) REFERENCES Zone_zoo(id_zone);

/*Contrat*/
ALTER TABLE Contrat
ADD CONSTRAINT fk_id_fonction_contrat
FOREIGN KEY (id_fonction) REFERENCES Fonction(id_fonction);

ALTER TABLE Contrat
ADD CONSTRAINT fk_id_personnel_contrat
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Soins*/
ALTER TABLE Soins
ADD CONSTRAINT fk_rfid_soins
FOREIGN KEY (RFID) REFERENCES Animal(RFID);

ALTER TABLE Soins
ADD CONSTRAINT fk_id_personnel_soins
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Repas*/
ALTER TABLE Repas
ADD CONSTRAINT fk_RFID_repas
FOREIGN KEY (RFID) REFERENCES Animal(RFID);

ALTER TABLE Repas 
ADD CONSTRAINT fk_id_personnel_repas
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

COMMIT;