CREATE TABLE Personnel(
    id_personnel INT PRIMARY KEY AUTO_INCREMENT,
    prenom_personnel VARCHAR(25) NOT NULL,
    nom_personnel VARCHAR(25) NOT NULL,
    date_entree_personnel DATE NOT NULL,
    salaire_personnel FLOAT NOT NULL,
    mdp_personnel VARCHAR(255) NOT NULL,
    identifiant_personnel VARCHAR(50),
    id_fonction INT NOT NULL,
    FOREIGN KEY (id_fonction) REFERENCES Fonction(id_fonction)
);

CREATE TABLE Fonction(
    id_fonction INT PRIMARY KEY AUTO_INCREMENT,
    nom_fonction VARCHAR(50) NOT NULL
);

INSERT INTO Fonction VALUES(1, 'Directeur');
INSERT INTO Fonction VALUES(2, 'Vétérinaire');
INSERT INTO Fonction VALUES(3, 'Caissier');

INSERT INTO Personnel VALUES(1, 'Anthony', 'Vauchel', '2026/02/07', 5000, 'jaimelezoo7', 'anthony.vauchel', 1);
INSERT INTO Personnel VALUES(2, 'Alexandre', 'Delloue', '2026/02/07', 3000, 'jesuisfou5', 'alexandre.delloue', 2);
INSERT INTO Personnel VALUES(3, 'Selma', 'Belabbas', '2026/02/07', 1600, 'liberezmoi4', 'selma.belabbas', 3);