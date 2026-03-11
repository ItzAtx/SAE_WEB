/* Création des tables */

CREATE TABLE Espece (
    nom_latin VARCHAR(50) PRIMARY KEY,
    nom_usuel VARCHAR(50),
    menace CHAR(1) 
    CONSTRAINT menace_check CHECK (menace IN ('O', 'N'))
);

CREATE TABLE Animal (
    RFID INT PRIMARY KEY NOT NULL,
    nom_animal VARCHAR(50),
    date_naissance DATE,
    poids DECIMAL(4,2),
    RFID_a_pour_pere INT,
    RFID_a_pour_mere INT,
    id_enclos INT,
    nom_latin VARCHAR(50)
);

CREATE TABLE Enclos (
    id_enclos INT,
    latitude FLOAT,
    longitude FLOAT,
    surface FLOAT,
    id_zone INT,
    CONSTRAINT pk_id_enclos PRIMARY KEY (id_enclos)
);

CREATE TABLE Possede (
    id_enclos INT,
    libelle_particularite VARCHAR(50) NOT NULL,
    CONSTRAINT pk_possede PRIMARY KEY (id_enclos, libelle_particularite)
);

CREATE TABLE Cohabiter (
    nom_latin_est_cohabiter_par VARCHAR(50),
    nom_latin_cohabite_avec VARCHAR(50),
    CONSTRAINT pk_cohabite PRIMARY KEY (nom_latin_est_cohabiter_par, nom_latin_cohabite_avec)
);

CREATE SEQUENCE id_nourriture_seq START WITH 1;
CREATE TABLE Nourriture (
    id_nourriture INT DEFAULT id_nourriture_seq.nextval,
    nom VARCHAR(50),
    CONSTRAINT pk_id_nourriture PRIMARY KEY (id_nourriture)
);

CREATE SEQUENCE id_visiteur_seq START WITH 1;
CREATE TABLE Visiteurs (
    id_visiteur INT DEFAULT id_visiteur_seq.nextval,
    nom_visiteur VARCHAR(50),
    prenom_visiteur VARCHAR(50),
    numero_telephone NUMBER(10),
    CONSTRAINT pk_id_visiteurs PRIMARY KEY (id_visiteur)
);

CREATE SEQUENCE id_prestation_seq START WITH 1;
CREATE TABLE Prestations (
    id_prestation int DEFAULT id_prestation_seq.nextval,
    libelle VARCHAR(50),
    niveau_contribution VARCHAR(6) 
        CONSTRAINT contribution_check CHECK (niveau_contribution IN ('Bronze', 'Argent', 'Or')),
    CONSTRAINT pk_id_prestation PRIMARY KEY (id_prestation)
);

CREATE TABLE Particularite (
    libelle_particularite VARCHAR(50) PRIMARY KEY NOT NULL
);

CREATE SEQUENCE id_prestataire_seq START WITH 1;
CREATE TABLE Prestataire (
    id_prestataire INT DEFAULT id_prestataire_seq.nextval,
    adresse VARCHAR(50),
    nom_societte VARCHAR(50),
    telephone_prestataire NUMBER(10),
    CONSTRAINT pk_id_prestataire PRIMARY KEY (id_prestataire)
);

CREATE TABLE Specialiser (
    nom_latin VARCHAR(50),
    id_personnel INT,
    CONSTRAINT pk_specialiter PRIMARY KEY (nom_latin, id_personnel)
);

CREATE SEQUENCE id_personnel_seq START WITH 1;
CREATE TABLE Personnel (
    id_personnel INT DEFAULT id_personnel_seq.nextval,
    nom_personnel VARCHAR(50),
    prenom_personnel VARCHAR(50),
    mot_de_passe VARCHAR(255),
    id_connexion VARCHAR(100),
    id_zone INT,
    CONSTRAINT pk_id_personnel PRIMARY KEY (id_personnel)
);

CREATE TABLE Chef (
    id_personnel_manager_de INT,
    id_personnel_est_manager_par INT,
    CONSTRAINT pk_chef PRIMARY KEY (id_personnel_manager_de, id_personnel_est_manager_par)
);

CREATE SEQUENCE id_contrat_seq START WITH 1;
CREATE TABLE Contrat (
    id_contrat INT DEFAULT id_contrat_seq.nextval,
    salaire DECIMAL(10, 2),
    date_debut DATE,
    date_fin DATE,
    id_fonction INT,
    id_personnel INT,
    CONSTRAINT pk_id_contrat PRIMARY KEY (id_contrat)
);

CREATE SEQUENCE id_fonction_seq START WITH 1;
CREATE TABLE Fonction (
    id_fonction INT DEFAULT id_fonction_seq.nextval,
    fonction VARCHAR(100),
    CONSTRAINT pk_id_fonction PRIMARY KEY (id_fonction)
);

CREATE TABLE Entretient (
    id_personnel INT,
    id_reparation INT,
    CONSTRAINT pk_entretient PRIMARY KEY (id_personnel, id_reparation)
);

CREATE SEQUENCE id_reparation_seq START WITH 1;
CREATE TABLE Reparation (
    id_reparation INT DEFAULT id_reparation_seq.nextval,
    nature_reparation VARCHAR(50),
    libelle VARCHAR(100),
    id_enclos INT,
    CONSTRAINT pk_id_reparation PRIMARY KEY (id_reparation)
);

CREATE TABLE Participe (
    id_prestation INT,
    id_reparation INT,
    CONSTRAINT pk_participe PRIMARY KEY (id_prestation, id_reparation)
);

CREATE TABLE Parrainer (
    RFID INT,
    id_visiteur INT,
    id_prestation INT,
    CONSTRAINT pk_parrainer PRIMARY KEY (RFID, id_visiteur, id_prestation)
);

CREATE SEQUENCE id_repas_seq START WITH 1;
CREATE TABLE Repas (
    id_repas INT DEFAULT id_repas_seq.nextval,
    nom_repas VARCHAR(50),
    date_repas DATE,
    RFID INT,
    id_personnel INT,
    CONSTRAINT pk_id_repas PRIMARY KEY (id_repas)
);

CREATE TABLE Attitre (
    RFID INT,
    id_personnel INT,
    CONSTRAINT pk_attire PRIMARY KEY (RFID, id_personnel)
);

CREATE SEQUENCE id_soin_seq START WITH 1;
CREATE TABLE Soins (
    id_soin INT DEFAULT id_soin_seq.nextval,
    date_soin DATE,
    complexite VARCHAR(20),
    id_personnel INT,
    RFID INT,
    CONSTRAINT pk_soins PRIMARY KEY (id_soin)
);

CREATE SEQUENCE id_zone_seq START WITH 1;
CREATE TABLE Zone_zoo (
    id_zone INT DEFAULT id_zone_seq.nextval,
    libelle VARCHAR(50),
    id_personnel INT,
    CONSTRAINT pk_id_zone PRIMARY KEY (id_zone)
);

CREATE TABLE Travaille (
    id_personnel INT,
    id_boutique INT,
    CONSTRAINT pk_travaille PRIMARY KEY (id_personnel, id_boutique)
);

CREATE SEQUENCE id_boutique_seq START WITH 1;
CREATE TABLE Boutique (
    id_boutique INT DEFAULT id_boutique_seq.nextval,
    nom_boutique VARCHAR(50),
    type_boutique VARCHAR(50),
    id_personnel INT,
    id_zone INT,
    CONSTRAINT pk_id_boutique PRIMARY KEY (id_boutique)
);

CREATE SEQUENCE id_ca_seq START WITH 1;
CREATE TABLE Chiffre_affaire (
    id_ca INT DEFAULT id_ca_seq.nextval,
    date_ca DATE,
    montant FLOAT,
    id_boutique INT,
    CONSTRAINT pk_ca PRIMARY KEY (id_ca, id_boutique)
);

CREATE TABLE Contient (
    id_repas INT,
    id_nourriture INT,
    quantite INT,
    CONSTRAINT pk_contient PRIMARY KEY (id_repas, id_nourriture)
);

-- /* Mise en place des clefs etrangeres*/

/*Animal*/
ALTER TABLE Animal
ADD CONSTRAINT fk_rfid_pere
FOREIGN KEY (RFID_a_pour_pere) REFERENCES Animal(RFID);

ALTER TABLE Animal 
ADD CONSTRAINT fk_rfid_mere
FOREIGN KEY (RFID_a_pour_mere) REFERENCES Animal(RFID);

ALTER TABLE Animal
ADD CONSTRAINT fk_id_enclos
FOREIGN KEY (id_enclos) REFERENCES Enclos(id_enclos);

ALTER TABLE Animal
ADD CONSTRAINT fk_nom_latin
FOREIGN KEY (nom_latin) REFERENCES Espece(nom_latin);

/*Cohabite*/
ALTER TABLE Cohabiter
ADD CONSTRAINT fk_est_cohabiter
FOREIGN KEY (nom_latin_est_cohabiter_par) REFERENCES Espece(nom_latin);

ALTER TABLE Cohabiter
ADD CONSTRAINT fk_cohabiter_avec
FOREIGN KEY (nom_latin_cohabite_avec) REFERENCES Espece(nom_latin);

/*Parrainer*/
ALTER TABLE Parrainer
ADD CONSTRAINT fk_rfid
FOREIGN KEY (RFID) REFERENCES Animal(RFID);

ALTER TABLE Parrainer
ADD CONSTRAINT fk_visiteur
FOREIGN KEY (id_visiteur) REFERENCES Visiteurs(id_visiteur);

ALTER TABLE Parrainer
ADD CONSTRAINT fk_id_prestation
FOREIGN KEY (id_prestation) REFERENCES Prestations(id_prestation);

/*Enclos*/
ALTER TABLE Enclos
ADD CONSTRAINT fk_id_zone
FOREIGN KEY (id_zone) REFERENCES Zone_zoo(id_zone);

/*Zone*/
ALTER TABLE Zone_zoo
ADD CONSTRAINT fk_id_personnel_zone_zoo
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Possede*/
ALTER TABLE Possede
ADD CONSTRAINT fk_id_enclos_possede
FOREIGN KEY (id_enclos) REFERENCES Enclos(id_enclos);

ALTER TABLE Possede
ADD CONSTRAINT fk_libelle_particularite
FOREIGN KEY (libelle_particularite) REFERENCES Particularite(libelle_particularite);

/*Boutique*/
ALTER TABLE Boutique
ADD CONSTRAINT fk_id_zone_boutique
FOREIGN KEY (id_zone) REFERENCES Zone_zoo(id_zone);

ALTER TABLE Boutique
ADD CONSTRAINT fk_id_personnel_boutique
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Travaille*/
ALTER TABLE Travaille
ADD CONSTRAINT fk_id_personnel_travaille
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

ALTER TABLE Travaille
ADD CONSTRAINT fk_id_boutique_travaille
FOREIGN KEY (id_boutique) REFERENCES Boutique(id_boutique);

/*Chiffre d'affaire*/
ALTER TABLE Chiffre_affaire
ADD CONSTRAINT fk_id_boutique
FOREIGN KEY (id_boutique) REFERENCES Boutique(id_boutique);

/*Reparation*/
ALTER TABLE Reparation
ADD CONSTRAINT fk_id_enclos_reparation
FOREIGN KEY (id_enclos) REFERENCES Enclos(id_enclos);

/*Participe*/
ALTER TABLE Participe
ADD CONSTRAINT fk_id_prestation_participe
FOREIGN KEY (id_prestation) REFERENCES Prestations(id_prestation);

ALTER TABLE Participe
ADD CONSTRAINT fk_id_reparation
FOREIGN KEY (id_reparation) REFERENCES Reparation(id_reparation);

/*Entretient*/
ALTER TABLE Entretient
ADD CONSTRAINT fk_id_personnel_entretient
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

ALTER TABLE Entretient
ADD CONSTRAINT fk_id_reparation_entretient
FOREIGN KEY (id_reparation) REFERENCES Reparation(id_reparation);

/*Personnel*/
ALTER TABLE Personnel
ADD CONSTRAINT fk_id_zone_personnel
FOREIGN KEY (id_zone) REFERENCES Zone_zoo(id_zone);

/*Chef*/
ALTER TABLE Chef
ADD CONSTRAINT fk_manager_de
FOREIGN KEY (id_personnel_manager_de) REFERENCES Personnel(id_personnel);

ALTER TABLE Chef
ADD CONSTRAINT fk_manager_par
FOREIGN KEY (id_personnel_est_manager_par) REFERENCES Personnel(id_personnel);

/*Contrat*/
ALTER TABLE Contrat
ADD CONSTRAINT fk_id_fonction_contrat
FOREIGN KEY (id_fonction) REFERENCES Fonction(id_fonction);

ALTER TABLE Contrat
ADD CONSTRAINT fk_id_personnel_contrat
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Specialiser*/
ALTER TABLE Specialiser
ADD CONSTRAINT fk_nom_latin_specialiser
FOREIGN KEY (nom_latin) REFERENCES Espece(nom_latin);

ALTER TABLE Specialiser
ADD CONSTRAINT fk_id_personnel_specialiser
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Soins*/
ALTER TABLE Soins
ADD CONSTRAINT fk_rfid_soins
FOREIGN KEY (RFID) REFERENCES Animal(RFID);

ALTER TABLE Soins
ADD CONSTRAINT fk_id_personnel_soins
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

/*Contient*/
ALTER TABLE Contient
ADD CONSTRAINT fk_id_repas_contient
FOREIGN KEY (id_repas) REFERENCES Repas(id_repas);

ALTER TABLE Contient
ADD CONSTRAINT fk_id_nourriture_contient
FOREIGN KEY (id_nourriture) REFERENCES Nourriture(id_nourriture);

/*Repas*/
ALTER TABLE Repas
ADD CONSTRAINT fk_RFID_repas
FOREIGN KEY (RFID) REFERENCES Animal(RFID);

ALTER TABLE Repas 
ADD CONSTRAINT fk_id_personnel_repas
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

COMMIT;