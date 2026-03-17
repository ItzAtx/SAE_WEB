-- Tracer l’historique des fonctions, avec la date de début et de fin, 
-- de l’employé ayant comme nom John, par ordre chronologique

SELECT fonction, TO_CHAR(date_debut, 'DD/MM/YYYY'), TO_CHAR(date_fin, 'DD/MM/YYYY')
FROM Personnel p, Contrat c, Fonction f 
WHERE p.id_personnel = c.id_personnel AND c.id_fonction = f.id_fonction AND nom_personnel='Sorton'
ORDER BY date_debut;

-- Quels sont le(s) nom(s) de(s) soigneur(s) ayant soigné tous les animaux ?

-- 1ère façon : HAVING / GROUP BY
SELECT nom_personnel, prenom_personnel
FROM Personnel p, Soins s
WHERE p.id_personnel = s.id_personnel
GROUP BY p.id_personnel, p.nom_personnel, p.prenom_personnel
HAVING COUNT(DISTINCT s.RFID) = (SELECT COUNT(*) FROM Animal);

-- 2ème façon : 
SELECT DISTINCT nom_personnel, prenom_personnel
FROM Personnel p
WHERE NOT EXISTS (
    SELECT a.RFID FROM Animal a
    WHERE NOT EXISTS (
        SELECT * FROM Soins s
        WHERE s.id_personnel = p.id_personnel
        AND s.RFID = a.RFID
    )
);

-- 3ème façon : 
SELECT DISTINCT nom_personnel, prenom_personnel
FROM Personnel p
WHERE NOT EXISTS (
    SELECT a.RFID FROM Animal a
    MINUS
    SELECT s.RFID FROM Soins s
    WHERE s.id_personnel = p.id_personnel
);

-- Lister les soins (id_soin, date_soin, complexité) réalisés
-- entre le 05/02/2025 et le 02/03/2025, triés par complexité décroissante.

SELECT id_soin, date_soin, complexite
FROM Soins
WHERE date_soin >= TO_DATE('05022025', 'DDMMYYYY') AND date_soin <= TO_DATE('02032025', 'DDMMYYYY')
ORDER BY complexite DESC;

-- Pour chaque enclos, afficher son identifiant et le nombre d'animaux
-- qu'il contient.

SELECT e.id_enclos, COUNT(a.RFID) as NombreAnimal
FROM Enclos e, Animal a
WHERE e.id_enclos = a.id_enclos
GROUP BY e.id_enclos;

-- Quels sont les enclos qui contiennent en moyenne des animaux
-- de plus de 50 kg ? Afficher l'id de l'enclos et le poids moyen.

SELECT e.id_enclos, AVG(poids)
FROM Enclos e, Animal a
WHERE e.id_enclos = a.id_enclos
GROUP BY e.id_enclos
HAVING AVG(poids) > 50;

-- Quel est l'animal (nom) ayant le poids le plus élevé dans le zoo ?

SELECT nom_animal
FROM Animal
WHERE poids = (SELECT MAX(poids) FROM Animal);

-- Quels sont les personnels ayant prodigué des soins à plus de 3 animaux
-- différents ? Afficher leur nom et prénom.

SELECT nom_personnel, prenom_personnel
FROM Personnel p, Soins s 
WHERE p.id_personnel = s.id_personnel
GROUP BY p.id_personnel , nom_personnel, prenom_personnel
HAVING COUNT(DISTINCT RFID) > 3;

-- Quels sont les animaux (nom) qui ont reçu des soins 
-- ET qui ont consommé au moins un repas ?


SELECT DISTINCT a.nom_animal
FROM Animal a
WHERE a.RFID IN (
    SELECT s.RFID
    FROM Soins s
    INTERSECT
    SELECT c.RFID
    FROM Consomme c
);
