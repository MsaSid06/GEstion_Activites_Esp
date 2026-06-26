-- =====================================================================
-- gestion_activites_esp : SCRIPT COMPLET (schema + donnees)
-- Mots de passe en clair = passNNN (ex : U001 -> pass001), stockes hashes (bcrypt).
-- Compte administrateur : U001 / pass001
-- NB : les activites ont des dates futures ; le trigger refuse une date de
--      debut anterieure a NOW(), donc importe avant que ces dates soient passees.
-- =====================================================================

DROP DATABASE IF EXISTS gestion_activites_esp;
CREATE DATABASE gestion_activites_esp;
USE gestion_activites_esp;

-- ---------------------------------------------------------------------
-- TABLES
-- ---------------------------------------------------------------------

CREATE TABLE UTILISATEUR(
    matricule_user CHAR(5) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE CHECK (email LIKE '%@%.%'),
    tel VARCHAR(20) UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    profil VARCHAR(15) NOT NULL CHECK (profil IN ('ADMIN', 'GESTIONNAIRE', 'ETUDIANT', 'PERSONNEL')),
    niveau_acces INT NOT NULL DEFAULT 0,

    CONSTRAINT pk_utilisateur PRIMARY KEY (matricule_user)
);

CREATE TABLE STRUCTURE(
    id_struct CHAR(5) NOT NULL,
    nom_struct VARCHAR(100) NOT NULL,
    desc_struct TEXT DEFAULT NULL,
    mail VARCHAR(100) NOT NULL UNIQUE CHECK (mail LIKE '%@%.%'),
    tel VARCHAR(20) UNIQUE NOT NULL,
    type_struct VARCHAR(20) NOT NULL CHECK (type_struct IN ('DEPARTEMENT', 'SERVICE', 'AMICALE', 'ASSOCIATION')),

    CONSTRAINT pk_structure PRIMARY KEY (id_struct)
);

CREATE TABLE APPARTENIR(
    id_appartenir INT AUTO_INCREMENT,
    id_struct CHAR(5) NOT NULL,
    matricule_user CHAR(5) NOT NULL,

    CONSTRAINT pk_appartenir PRIMARY KEY (id_appartenir),
    UNIQUE (matricule_user, id_struct),
    CONSTRAINT fk_appartenir_utilisateur_matricule_user FOREIGN KEY (matricule_user) REFERENCES UTILISATEUR(matricule_user) ON DELETE CASCADE,
    CONSTRAINT fk_appartenir_structure_id_struct FOREIGN KEY (id_struct) REFERENCES STRUCTURE(id_struct) ON DELETE CASCADE
);

CREATE TABLE ADMINISTRATEUR(
    id_admin INT AUTO_INCREMENT,
    matricule_user CHAR(5) NOT NULL,

    CONSTRAINT pk_administrateur PRIMARY KEY (id_admin),
    UNIQUE (matricule_user),
    CONSTRAINT fk_administrateur_utilisateur_matricule_user FOREIGN KEY (matricule_user) REFERENCES UTILISATEUR(matricule_user) ON DELETE CASCADE
);

CREATE TABLE ETUDIANT(
    id_etd INT AUTO_INCREMENT,
    matricule_user CHAR(5) NOT NULL,
    filiere VARCHAR(50) NOT NULL,
    niveau VARCHAR(20) NOT NULL,

    CONSTRAINT pk_etudiant PRIMARY KEY (id_etd),
    UNIQUE (matricule_user),
    CONSTRAINT fk_etudiant_utilisateur_matricule_user FOREIGN KEY (matricule_user) REFERENCES UTILISATEUR(matricule_user) ON DELETE CASCADE
);

CREATE TABLE PERSONNEL(
    id_personnel INT AUTO_INCREMENT,
    matricule_user CHAR(5) NOT NULL,
    poste VARCHAR(50) NOT NULL,
    specialite VARCHAR(50),

    CONSTRAINT pk_personnel PRIMARY KEY (id_personnel),
    UNIQUE (matricule_user),
    CONSTRAINT fk_personnel_utilisateur_matricule_user FOREIGN KEY (matricule_user) REFERENCES UTILISATEUR(matricule_user) ON DELETE CASCADE
);

CREATE TABLE GESTIONNAIRE(
    id_gestionnaire INT AUTO_INCREMENT,
    matricule_user CHAR(5) NOT NULL,
    id_struct CHAR(5) NOT NULL,

    CONSTRAINT pk_gestionnaire PRIMARY KEY (id_gestionnaire),
    CONSTRAINT fk_gestionnaire_utilisateur_matricule_user FOREIGN KEY (matricule_user) REFERENCES UTILISATEUR(matricule_user) ON DELETE CASCADE,
    CONSTRAINT fk_gestionnaire_structure_id_struct FOREIGN KEY (id_struct) REFERENCES STRUCTURE(id_struct)
);

CREATE TABLE ACTIVITE(
    id_act INT AUTO_INCREMENT,
    matricule_user CHAR(5) NOT NULL,
    titre VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    type_act VARCHAR(30) NOT NULL CHECK (type_act IN (
        'COURS','EXAMEN','SOUTENANCE','REUNION','FORMATION','SEMINAIRE',
        'CONFERENCE','ATELIER','COLLOQUE','CEREMONIE','JOURNEE_PORTES_OUVERTES',
        'ACCUEIL_NOUVEAUX','ASSEMBLEE_GENERALE','ELECTION','SORTIE_PEDAGOGIQUE',
        'VISITE','COMPETITION','ACTIVITE_CULTURELLE','ACTIVITE_SPORTIVE',
        'CAMPAGNE_SENSIBILISATION','ACTION_SOCIALE','FETE','PROJET','AUTRE'
    )),
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    lieu VARCHAR(100) NOT NULL,

    CONSTRAINT check_dateDebut_dateFin CHECK (date_fin > date_debut),
    CONSTRAINT pk_activite PRIMARY KEY (id_act),
    CONSTRAINT fk_activite_utilisateur_matricule_user FOREIGN KEY (matricule_user) REFERENCES UTILISATEUR(matricule_user)
);

CREATE TABLE NOTIFICATION(
    id_not INT AUTO_INCREMENT,
    id_emetteur CHAR(5) NOT NULL,
    id_act INT,
    message TEXT NOT NULL,
    date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_notification PRIMARY KEY (id_not),
    CONSTRAINT fk_notification_utilisateur_id_emetteur FOREIGN KEY (id_emetteur) REFERENCES UTILISATEUR(matricule_user),
    CONSTRAINT fk_notification_activite_id_act FOREIGN KEY (id_act) REFERENCES ACTIVITE(id_act) ON DELETE SET NULL
);

-- ---------------------------------------------------------------------
-- TRIGGER : date de debut non anterieure a maintenant
-- ---------------------------------------------------------------------
DELIMITER //
CREATE TRIGGER verif_date_activite_insert
BEFORE INSERT ON ACTIVITE
FOR EACH ROW
BEGIN
    IF NEW.date_debut < NOW() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La date de debut ne peut pas etre anterieure a maintenant.';
    END IF;
END//
DELIMITER ;


-- 1. UTILISATEUR (mots de passe hashes)
INSERT INTO UTILISATEUR (matricule_user, nom, prenom, email, tel, mot_de_passe, profil, niveau_acces) VALUES
('U001','Sidime','Moussa','moussa.sidime@esp.sn','770000001','$2b$12$lwbMoaaqd6FnBQ8wWc03i.YoPiY7kgCpmqFX21RV/VnMeJv7FQmzm','ADMIN',9),
('U002','Dia','Assane','assane.dia@esp.sn','770000002','$2b$12$lNn9gqKuZTieU.rfY4w2pu3TLW3OuQhgvSiifW.1PR8uy9QYValY2','GESTIONNAIRE',1),
('U003','Cissokho','Maimouna','maimouna.cissokho@esp.sn','770000003','$2b$12$lKVWwM1lZf1kvIgzctpnAO1Escx9XW1LMr66BxrcqZ7ZixKXkNqFC','GESTIONNAIRE',1),
('U004','Mbaye','Aissata','aissata.mbaye@esp.sn','770000004','$2b$12$VkHbZ5E0ejBA2ElWfgcjcuJSHrGS5QKSGXqDQmUQc.YeUCw5bJRk.','GESTIONNAIRE',2),
('U005','Diop','Awa','awa.diop@esp.sn','770000005','$2b$12$5Wm12hdNtp4vclVOKSfnbOpHgLbfsDGd0QnLANNyVLUfqdld4c9iS','ETUDIANT',0),
('U006','Ndiaye','Cheikh','cheikh.ndiaye@esp.sn','770000006','$2b$12$Z1oLdx86F46nDgyzC8nYnutuYchNxVFPYiKfTBm3rADULHhC6XO3S','ETUDIANT',0),
('U007','Sow','Fatou','fatou.sow@esp.sn','770000007','$2b$12$uXdpHeYxC04dyC0ns3/c5eJGoB1ZQjdWmNocPLU.e5CVTl7fihijC','ETUDIANT',0),
('U008','Ba','Ousmane','ousmane.ba@esp.sn','770000008','$2b$12$5juiz9gCwJxrVAdD3OxrheOp/.W8Obrx0hpaLNZYH1iikHjlv7Y2O','PERSONNEL',0),
('U009','Gueye','Ramatoulaye','rama.gueye@esp.sn','770000009','$2b$12$S52L.8BkM4DUOX.AXXHyDuNiZrZjoZ1XZmdDQh2mZ8FVMCLUo69Me','PERSONNEL',0),
('U010','Sarr','Modou','modou.sarr@esp.sn','770000010','$2b$12$Hu31JyFP9SYU7NdmXrVpDOCYfBCxMop1cj2OUxEYWsXBLsjPh4dte','GESTIONNAIRE',1),
('U011','Diagne','Aminata','aminata.diagne@esp.sn','770000011','$2b$12$voNrrPe6RB6WBJntWn7GIeOORI6B22ovWxGqaEQi883BK0K078aW2','ETUDIANT',0),
('U012','Fall','Mamadou','mamadou.fall@esp.sn','770000012','$2b$12$MbtwsHlNaMIyqpKgi8yPZe4p8DOPQd90TrQOBA4/egOaF3BK/8fg6','PERSONNEL',0);

-- 2. ADMINISTRATEUR
INSERT INTO ADMINISTRATEUR (matricule_user) VALUES ('U001');

-- 3. STRUCTURE
INSERT INTO STRUCTURE (id_struct, nom_struct, desc_struct, mail, tel, type_struct) VALUES
('S001', 'Direction ESP',                  'Direction generale de l ecole',                'direction@esp.sn',   '338000001', 'SERVICE'),
('S002', 'Departement Genie Informatique', 'Departement de formation en informatique',      'dgi@esp.sn',         '338000002', 'DEPARTEMENT'),
('S003', 'Departement Genie Civil',        'Departement de formation en genie civil',       'dgc@esp.sn',         '338000003', 'DEPARTEMENT'),
('S004', 'Service Scolarite',              'Gestion administrative des etudiants',          'scolarite@esp.sn',   '338000004', 'SERVICE'),
('S005', 'Amicale des Etudiants',          'Amicale generale des etudiants de l ESP',       'amicale@esp.sn',     '338000005', 'AMICALE'),
('S006', 'Departement Genie Electrique',   'Departement de formation en genie electrique',  'dge@esp.sn',         '338000006', 'DEPARTEMENT'),
('S007', 'Departement Genie Mecanique',    'Departement de formation en genie mecanique',   'dgm@esp.sn',         '338000007', 'DEPARTEMENT'),
('S008', 'Departement Genie Chimique',     'Departement de formation en genie chimique',    'dgchi@esp.sn',       '338000008', 'DEPARTEMENT'),
('S009', 'Departement de Gestion',         'Departement de formation en gestion',           'dg@esp.sn',          '338000009', 'DEPARTEMENT'),
('S010', 'Association des professeurs',    'Association des professeurs de l ESP',          'professeurs@esp.sn', '338000010', 'ASSOCIATION');

-- 4. APPARTENIR (rattachement de chaque utilisateur a une structure)
INSERT INTO APPARTENIR (matricule_user, id_struct) VALUES
('U001','S001'),
('U002','S002'),
('U003','S004'),
('U004','S002'),
('U005','S002'),
('U006','S002'),
('U007','S003'),
('U008','S004'),
('U009','S002'),
('U010','S003'),
('U011','S006'),
('U012','S002');

-- 5. ETUDIANT
INSERT INTO ETUDIANT (matricule_user, filiere, niveau) VALUES
('U005', 'Genie Logiciel',   'Licence 2'),
('U006', 'Genie Logiciel',   'Licence 3'),
('U007', 'Genie Civil',      'Licence 1'),
('U011', 'Genie Electrique', 'DIC 1');

-- 6. PERSONNEL
INSERT INTO PERSONNEL (matricule_user, poste, specialite) VALUES
('U008', 'Secretaire', 'Gestion administrative'),
('U009', 'Technicien', 'Reseaux et systemes'),
('U012', 'Enseignant', 'Informatique');

-- 7. GESTIONNAIRE
INSERT INTO GESTIONNAIRE (matricule_user, id_struct) VALUES
('U002', 'S002'),
('U003', 'S004'),
('U004', 'S002'),
('U004', 'S005'),
('U010', 'S003');

-- 8. ACTIVITE (dates futures requises par le trigger)
INSERT INTO ACTIVITE (matricule_user, titre, description, type_act, date_debut, date_fin, lieu) VALUES
('U002', 'Examen Programmation Web',  'Examen final du module de programmation web pour les etudiants en licence et DUT 2', 'EXAMEN',     '2026-07-10 08:00:00', '2026-07-10 11:00:00', 'Salle Info 1'),
('U002', 'Soutenance Projet Tutore',  'Presentation des projets tutores L2 GLSI',                                           'SOUTENANCE', '2026-07-15 09:00:00', '2026-07-15 17:00:00', 'Amphi A'),
('U003', 'Reunion de coordination',   'Reunion des chefs de service',                                                       'REUNION',    '2026-07-05 10:00:00', '2026-07-05 12:00:00', 'Salle de reunion'),
('U004', 'Seminaire IA',              'Seminaire sur l intelligence artificielle',                                          'SEMINAIRE',  '2026-08-01 09:00:00', '2026-08-02 17:00:00', 'Amphi B'),
('U010', 'Journee Portes Ouvertes',   'Presentation des filieres aux nouveaux bacheliers',                                  'CONFERENCE', '2026-09-20 08:00:00', '2026-09-20 18:00:00', 'Grand amphi');

-- 9. NOTIFICATION
INSERT INTO NOTIFICATION (id_emetteur, id_act, message, date_envoi) VALUES
('U002', 1, 'Rappel : examen de programmation web le 10 juillet.', '2026-06-20 09:00:00'),
('U002', 2, 'Soutenances prevues le 15 juillet, soyez ponctuels.', '2026-06-22 10:00:00'),
('U004', 4, 'Inscriptions ouvertes pour le seminaire IA.',         '2026-06-25 14:00:00');