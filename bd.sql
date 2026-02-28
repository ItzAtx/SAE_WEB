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
VALUES('Anthony', 'Vauchel', '2026-02-07', 5000, '$2a$12$lobkyF/C7pDLhHXUPgQeh.o25J0XS4/VuiLrDQE4y3.dVMcrcN7gG', 'anthony.vauchel', 1);
INSERT INTO Personnel(prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction) 
VALUES('Alexandre', 'Delloue', '2026-02-07', 3000, '$2a$12$86fYcd5g5H61Xmk24vQrA.HR7amEbL6NZTWF95anu/agOJKoXfYje', 'alexandre.delloue', 2);
INSERT INTO Personnel(prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction) 
VALUES('Selma', 'Belabbas', '2026-02-07', 1600, '$2a$12$3AXQtaKe5CZC5NKgeENWquGKYnczJL9NxJGqnIBCc26HaPfYaJVP6', 'selma.belabbas', 3);