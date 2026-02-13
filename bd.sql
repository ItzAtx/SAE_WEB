CREATE TABLE Fonction(
    id_fonction INT PRIMARY KEY AUTO_INCREMENT,
    nom_fonction VARCHAR(50) NOT NULL
);

CREATE TABLE Personnel(
    numero_personnel INT PRIMARY KEY AUTO_INCREMENT,
    prenom_personnel VARCHAR(25) NOT NULL,
    nom_personnel VARCHAR(25) NOT NULL,
    date_entree_personnel DATE NOT NULL,
    salaire_personnel DECIMAL(10, 2) NOT NULL,
    mdp_personnel VARCHAR(255) NOT NULL,
    identifiant_personnel VARCHAR(50),
    id_fonction INT NOT NULL,
    FOREIGN KEY (id_fonction) REFERENCES Fonction(id_fonction)
);

INSERT INTO Fonction(nom_fonction) VALUES('Directeur');
INSERT INTO Fonction(nom_fonction) VALUES('Vétérinaire');
INSERT INTO Fonction(nom_fonction) VALUES('Caissier');

INSERT INTO Personnel(prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction) 
VALUES('Anthony', 'Vauchel', '2026-02-07', 5000, MD5('1'), 'anthony.vauchel', 1);
INSERT INTO Personnel(prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction) 
VALUES(2, 'Alexandre', 'Delloue', '2026-02-07', 3000, MD5('jesuisfou5'), 'alexandre.delloue', 2);
INSERT INTO Personnel(prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction) 
VALUES(3, 'Selma', 'Belabbas', '2026-02-07', 1600, MD5('liberezmoi4'), 'selma.belabbas', 3);