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
    id_enclos INT PRIMARY KEY,
    latitude FLOAT,
    longitude FLOAT,
    surface FLOAT,
    id_zone INT
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

CREATE TABLE Nourriture (
    id_nourriture INT PRIMARY KEY NOT NULL,
    nom VARCHAR(50)
);

CREATE TABLE Visiteurs (
    id_visiteur INT PRIMARY KEY NOT NULL,
    nom_visiteur VARCHAR(50),
    prenom_visiteur VARCHAR(50),
    numero_telephone NUMBER(10)
);

CREATE TABLE Prestations (
    id_prestation int PRIMARY KEY NOT NULL,
    libelle VARCHAR(50),
    niveau_contribution VARCHAR(6) 
        CONSTRAINT contribution_check CHECK (niveau_contribution IN ('Bronze', 'Argent', 'Or'))
);

CREATE TABLE Particularite (
    libelle_particularite VARCHAR(50) PRIMARY KEY NOT NULL
);

CREATE TABLE Prestataire (
    id_prestataire INT PRIMARY KEY,
    adresse VARCHAR(50),
    nom_societte VARCHAR(50),
    telephone_prestataire NUMBER(10)
);

CREATE TABLE Specialiser (
    nom_latin VARCHAR(50),
    id_personnel INT,
    CONSTRAINT pk_specialiter PRIMARY KEY (nom_latin, id_personnel)
);

CREATE TABLE Personnel (
    id_personnel INT PRIMARY KEY,
    nom_personnel VARCHAR(50),
    prenom_personnel VARCHAR(50),
    mot_de_passe VARCHAR(255),
    id_connexion VARCHAR(100),
    id_zone INT
);

CREATE TABLE Chef (
    id_personnel_manager_de INT,
    id_personnel_est_manager_par INT,
    CONSTRAINT pk_chef PRIMARY KEY (id_personnel_manager_de, id_personnel_est_manager_par)
);

CREATE TABLE Contrat (
    id_contrat INT PRIMARY KEY,
    salaire DECIMAL(10, 2),
    date_debut DATE,
    date_fin DATE,
    id_fonction INT,
    id_personnel INT
);

CREATE TABLE Fonction (
    id_fonction INT PRIMARY KEY,
    fonction VARCHAR(100)
);

CREATE TABLE Entretient (
    id_personnel INT,
    id_reparation INT,
    CONSTRAINT pk_entretient PRIMARY KEY (id_personnel, id_reparation)
);

CREATE TABLE Reparation (
    id_reparation INT PRIMARY KEY,
    nature_reparation VARCHAR(50),
    libelle VARCHAR(100),
    id_enclos INT
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

CREATE TABLE Consomme (
    RFID INT,
    id_repas INT,
    CONSTRAINT pk_consomme PRIMARY KEY (RFID, id_repas)
);

CREATE TABLE Repas (
    id_repas INT PRIMARY KEY,
    nom_repas VARCHAR(50),
    date_repas DATE
);

CREATE TABLE Prepare (
    id_personnel INT,
    id_repas INT,
    CONSTRAINT pk_prepare PRIMARY KEY (id_personnel, id_repas)
);

CREATE TABLE Attitre (
    RFID INT,
    id_personnel INT,
    CONSTRAINT pk_attire PRIMARY KEY (RFID, id_personnel)
);

CREATE TABLE Soins (
    id_soin INT,
    date_soin DATE,
    complexite VARCHAR(20),
    RFID INT,
    CONSTRAINT pk_soins PRIMARY KEY (RFID, id_soin)
);

CREATE TABLE Prodigue (
    id_personnel INT,
    id_soin INT,
    CONSTRAINT pk_prodigue PRIMARY KEY (id_personnel, id_soin)
);

CREATE TABLE Zone_zoo (
    id_zone INT PRIMARY KEY,
    libelle VARCHAR(50),
    id_personnel INT
);

CREATE TABLE Travaille (
    id_personnel INT,
    id_boutique INT,
    CONSTRAINT pk_travaille PRIMARY KEY (id_personnel, id_boutique)
);

CREATE TABLE Boutique (
    id_boutique INT PRIMARY KEY,
    nom_boutique VARCHAR(50),
    type_boutique VARCHAR(50),
    id_personnel INT,
    id_zone INT
);

CREATE TABLE Chiffre_affaire (
    id_ca INT,
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

/*Prodigue*/
ALTER TABLE Prodigue
ADD CONSTRAINT fk_id_personnel_prodigue
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

ALTER TABLE Prodigue
ADD CONSTRAINT fk_id_soin_prodigue
FOREIGN KEY (id_soin) REFERENCES Soins(id_soin);

/*Prepare*/
ALTER TABLE Prepare
ADD CONSTRAINT fk_id_personnel_prepare
FOREIGN KEY (id_personnel) REFERENCES Personnel(id_personnel);

ALTER TABLE Prepare
ADD CONSTRAINT fk_id_repas_prepare
FOREIGN KEY (id_repas) REFERENCES Repas(id_repas);

/*Contient*/
ALTER TABLE Contient
ADD CONSTRAINT fk_id_repas_contient
FOREIGN KEY (id_repas) REFERENCES Repas(id_repas);

ALTER TABLE Contient
ADD CONSTRAINT fk_id_nourriture_contient
FOREIGN KEY (id_nourriture) REFERENCES Nourriture(id_nourriture);

/*Consomme*/
ALTER TABLE Consomme
ADD CONSTRAINT fk_rfid_consomme
FOREIGN KEY (RFID) REFERENCES Animal(RFID);

ALTER TABLE Consomme
ADD CONSTRAINT fk_id_repas_consomme
FOREIGN KEY (id_repas) REFERENCES Repas(id_repas);