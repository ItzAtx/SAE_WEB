CREATE TABLE Fonction(
    id_fonction NUMBER PRIMARY KEY,
    nom_fonction VARCHAR2(50) NOT NULL
);

CREATE TABLE Personnel(
    numero_personnel NUMBER PRIMARY KEY,
    prenom_personnel VARCHAR2(25) NOT NULL,
    nom_personnel VARCHAR2(25) NOT NULL,
    date_entree_personnel DATE NOT NULL,
    salaire_personnel DECIMAL(10, 2) NOT NULL,
    mdp_personnel VARCHAR2(255) NOT NULL,
    identifiant_personnel VARCHAR2(50),
    id_fonction NUMBER NOT NULL,
    FOREIGN KEY (id_fonction) REFERENCES Fonction(id_fonction)
);

INSERT INTO Fonction(id_fonction, nom_fonction) VALUES(1, 'Directeur');
INSERT INTO Fonction(id_fonction, nom_fonction) VALUES(2, 'Vétérinaire');
INSERT INTO Fonction(id_fonction, nom_fonction) VALUES(3, 'Caissier');

INSERT INTO Personnel(numero_personnel, prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction)
VALUES(1, 'Anthony', 'Vauchel', TO_DATE('2026-02-07', 'YYYY-MM-DD'), 5000, '$2a$12$l1zjs/.zjcZ/arvNqB3L0utNFJZxR2Q2A3QjWKcZtJ8nj9oyPLnIW', 'anthony.vauchel', 1);
INSERT INTO Personnel(numero_personnel, prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction)
VALUES(2, 'Alexandre', 'Delloue', TO_DATE('2026-02-07', 'YYYY-MM-DD'), 3000, '$2a$12$86fYcd5g5H61Xmk24vQrA.HR7amEbL6NZTWF95anu/agOJKoXfYje', 'alexandre.delloue', 2);
INSERT INTO Personnel(numero_personnel, prenom_personnel, nom_personnel, date_entree_personnel, salaire_personnel, mdp_personnel, identifiant_personnel, id_fonction)
VALUES(3, 'Selma', 'Belabbas', TO_DATE('2026-02-07', 'YYYY-MM-DD'), 1600, '$2a$12$3AXQtaKe5CZC5NKgeENWquGKYnczJL9NxJGqnIBCc26HaPfYaJVP6', 'selma.belabbas', 3);