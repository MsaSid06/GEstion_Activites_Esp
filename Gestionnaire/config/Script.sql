create database if not exists gestion_activites_esp;
use gestion_activites_esp;

create table UTILISATEUR(
    matricule_user char(5) not null,
    nom varchar(50) not null,
    prenom varchar(50) not null,
    email varchar(100) not null unique check (email like '%@%.%'),
    tel varchar(20) unique,
    mot_de_passe varchar(255) not null,
    profil varchar(15) not null check (profil in ('ADMIN', 'GESTIONNAIRE', 'ETUDIANT', 'PERSONNEL')),
    niveau_acces int  not null  default 0 ,

    constraint pk_utilisateur primary key (matricule_user)
);


create table STRUCTURE(
    id_struct char(5) not null,
    nom_struct varchar(100) not null,
    desc_struct text default null,
    email varchar(100) not null unique check (email like '%@%.%'),
    tel varchar(20) unique not null,
    type_struct varchar(20) not null check (type_struct in ('DEPARTEMENT', 'SERVICE', 'AMICALE', 'ASSOCIATION')),

    constraint pk_structure primary key (id_struct)
);

create table APPARTENIR(
    id_appartenir int auto_increment,
    id_struct char(5) not null,
    matricule_user char(5) not null,

    constraint pk_appartenir primary key (id_appartenir),
    unique (id_appartenir,matricule_user,id_struct),
    constraint fk_appartenir_utilisateur_matricule_user foreign key (matricule_user) references UTILISATEUR(matricule_user) on delete cascade,
    constraint fk_appartenir_structure_id_struct foreign key (id_struct) references STRUCTURE(id_struct) on delete cascade
);

create table ETUDIANT(
    id_etd int auto_increment, 
    matricule_user char(5) not null,
    filiere varchar(50) not null,
    niveau varchar(20) not null,

    constraint pk_etudiant primary key (id_etd),
    unique (matricule_user),
    constraint fk_etudiant_utilisateur_matricule_user foreign key (matricule_user) references UTILISATEUR(matricule_user) on delete cascade 
);


create table PERSONNEL(
    id_personnel int auto_increment, 
    matricule_user char(5) not null,
    poste varchar(50) not null,
    specialite varchar(50),

    unique (matricule_user),
    constraint pk_personnel primary key (id_personnel),
    constraint fk_personnel_utilisateur_matricule_user foreign key (matricule_user) references UTILISATEUR(matricule_user) on delete cascade
);


create table GESTIONNAIRE(
    id_gestionnaire int auto_increment, 
    matricule_user char(5) not null,
    id_struct char(5) not null,

    constraint pk_gestionnaire primary key (id_gestionnaire),
    constraint fk_gestionnaire_utilisateur_matricule_user foreign key (matricule_user) references UTILISATEUR(matricule_user) on delete cascade,
    constraint fk_gestionnaire_structure_id_struct foreign key (id_struct) references STRUCTURE(id_struct)
);


create table ACTIVITE(
    id_act int auto_increment,
    matricule_user char(5) not null,
    titre varchar(100) not null,
    description text not null,
    type_act VARCHAR(30) NOT NULL CHECK ( type_act IN ('COURS','EXAMEN','SOUTENANCE','REUNION','FORMATION','SEMINAIRE','CONFERENCE','ATELIER','COLLOQUE','CEREMONIE','JOURNEE_PORTES_OUVERTES','ACCUEIL_NOUVEAUX','ASSEMBLEE_GENERALE','ELECTION','SORTIE_PEDAGOGIQUE','VISITE','COMPETITION','ACTIVITE_CULTURELLE','ACTIVITE_SPORTIVE','CAMPAGNE_SENSIBILISATION','ACTION_SOCIALE','FETE','PROJET','AUTRE')),
    date_debut datetime not null, 
    date_fin datetime not null,
    lieu varchar(100) not null ,

    -- constraint check_date_debut check( date_debut > NOW()),
    constraint check_dateDebut_dateFin check (date_fin > date_debut),
    constraint pk_activite primary key (id_act),
    constraint fk_activite_utilisateur_matricule_user foreign key (matricule_user) references UTILISATEUR(matricule_user)
);

DELIMITER //

CREATE TRIGGER trg_check_date_debut
BEFORE INSERT ON ACTIVITE
FOR EACH ROW
BEGIN
    IF NEW.date_debut <= NOW() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La date de début doit être dans le futur';
    END IF;
END//

DELIMITER ;

create table NOTIFICATION(
    id_not int auto_increment,
    id_emetteur char(5) not null,
    id_act int,
    message text not null,
    date_envoi datetime not null default current_timestamp,

    constraint pk_notification primary key (id_not),
    constraint fk_notification_utilisateur_id_emetteur foreign key (id_emetteur) references UTILISATEUR(matricule_user),
    constraint fk_notification_activite_id_act foreign key (id_act) references ACTIVITE(id_act) on delete set null
);




--voici les insertion et tt

-- INSERT INTO UTILISATEUR (matricule_user, nom, prenom, email, tel, mot_de_passe, profil, niveau_acces) VALUES
-- ('U001','Sidime','Moussa','moussa.sidime@esp.sn','770000001','pass001','ADMIN','TOTAL'),
-- ('U002','Dia','Assane','assane.dia@esp.sn','770000002','pass002','GESTIONNAIRE','STRUCTURE'),
-- ('U003','Cissokho','Maimouna','maimouna.cissokho@esp.sn','770000003','pass003','GESTIONNAIRE','STRUCTURE'),
-- ('U004','Mbaye','Aissata','aissata.mbaye@esp.sn','770000004','pass004','GESTIONNAIRE','STRUCTURE'),
-- ('U005','Diop','Awa','awa.diop@esp.sn','770000005','pass005','ETUDIANT',NULL),
-- ('U006','Ndiaye','Cheikh','cheikh.ndiaye@esp.sn','770000006','pass006','ETUDIANT',NULL),
-- ('U007','Sow','Fatou','fatou.sow@esp.sn','770000007','pass007','ETUDIANT',NULL),
-- ('U008','Ba','Ousmane','ousmane.ba@esp.sn','770000008','pass008','PERSONNEL',NULL),
-- ('U009','Gueye','Ramatoulaye','rama.gueye@esp.sn','770000009','pass009','PERSONNEL',NULL),
-- ('U010','Sarr','Modou','modou.sarr@esp.sn','770000010','pass010','GESTIONNAIRE','STRUCTURE'),
-- ('U011','Diagne','Aminata','aminata.diagne@esp.sn','770000011','pass011','ETUDIANT',NULL),
-- ('U012','Fall','Mamadou','mamadou.fall@esp.sn','770000012','pass012','PERSONNEL',NULL);


-- INSERT INTO STRUCTURE (id_struct, nom_struct, desc_struct, mail, tel, type_struct) VALUES
-- ('S001','Direction ESP','Direction generale de l ecole','direction@esp.sn','338000001','SERVICE'),
-- ('S002','Departement Genie Informatique','Departement de formation en informatique','dgi@esp.sn','338000002','DEPARTEMENT'),
-- ('S003','Departement Genie Civil','Departement de formation en genie civil','dgc@esp.sn','338000003','DEPARTEMENT'),
-- ('S004','Service Scolarite','Gestion administrative des etudiants','scolarite@esp.sn','338000004','SERVICE'),
-- ('S005','Amicale des Etudiants','Amicale generale des etudiants de l ESP','amicale@esp.sn','338000005','AMICALE'),
-- ('S006','Département Génie Electrique','Departement de formation en genie electrique','dge@esp.sn','338000006','DEPARTEMENT'),
-- ('S007','Département Génie Mécanique','Departement de formation en genie mecanique','dgm@esp.sn','338000007','DEPARTEMENT'),
-- ('S008','Département Génie Chimique','Departement de formation en genie chimique','dgc@esp.sn','338000008','DEPARTEMENT'),
-- ('S009','Departement de Gestion','Departement de formation en gestion','dg@esp.sn','338000009','DEPARTEMENT'),
-- ('S010','Association des professeurs','Association des professeurs de l ESP','professeurs@esp.sn','338000010','ASSOCIATION');


-- INSERT INTO ETUDIANT (matricule_user, filiere, niveau) VALUES
-- ('U005','Genie Logiciel','Licence 2'),
-- ('U006','Genie Logiciel','Licence 3'),
-- ('U007','Genie Civil','Licence 1'),
-- ('U011','Genie Electrique','DIC 1');


-- INSERT INTO PERSONNEL (matricule_user, poste, specialite) VALUES
-- ('U008','Secretaire','Gestion administrative'),
-- ('U009','Technicien','Reseaux et systemes'),
-- ('U012','Enseignant','Informatique');


-- INSERT INTO GESTIONNAIRE (matricule_user, id_struct) VALUES
-- ('U002','S002'),
-- ('U003','S004'),
-- ('U004','S002'),
-- ('U004','S005'),
-- ('U010','S003');


-- INSERT INTO ACTIVITE (matricule_user, titre, description, type_act, date_debut, date_fin, lieu) VALUES
-- ('U002','Examen Programmation Web','Examen final du module de programmation web pour les étudiants en licence et DUT 2','EXAMEN','2026-07-10 08:00:00','2026-07-10 11:00:00','Salle Info 1'),
-- ('U002','Soutenance Projet Tutore','Presentation des projets tutores L2 GLSI','SOUTENANCE','2026-07-15 09:00:00','2026-07-15 17:00:00','Amphi A'),
-- ('U003','Reunion de coordination','Reunion des chefs de service','REUNION','2026-07-05 10:00:00','2026-07-05 12:00:00','Salle de reunion'),
-- ('U004','Seminaire IA','Seminaire sur l intelligence artificielle','SEMINAIRE','2026-08-01 09:00:00','2026-08-02 17:00:00','Amphi B'),
-- ('U010','Journee Portes Ouvertes','Presentation des filieres aux nouveaux bacheliers','CONFERENCE','2026-09-20 08:00:00','2026-09-20 18:00:00','Grand amphi');


-- INSERT INTO NOTIFICATION (id_emetteur, id_act, message, date_envoi) VALUES
-- ('U002',1,'Rappel : examen de programmation web le 10 juillet.','2026-06-20 09:00:00'),
-- ('U002',2,'Soutenances prevues le 15 juillet, soyez ponctuels.','2026-06-22 10:00:00'),
-- ('U004',4,'Inscriptions ouvertes pour le seminaire IA.','2026-06-25 14:00:00');



select * from UTILISATEUR;
select * from STRUCTURE;
select * from ETUDIANT;
select * from PERSONNEL;
select * from GESTIONNAIRE;
select * from ACTIVITE;
select * from NOTIFICATION;