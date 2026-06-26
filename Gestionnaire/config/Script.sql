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




select * from UTILISATEUR;
select * from STRUCTURE;
select * from ETUDIANT;
select * from PERSONNEL;
select * from GESTIONNAIRE;
select * from ACTIVITE;
select * from NOTIFICATION;