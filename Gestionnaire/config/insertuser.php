<?php
require_once 'connexion.php';
require_once '../models/utilisateur.php';
$pdo = connexionBD();
$users = [
    ['U001','Sidime','Moussa','moussa.sidime@esp.sn','770000001','123456','ADMIN',3],
    ['U002','Dia','Assane','assane.dia@esp.sn','770000002','123456','GESTIONNAIRE',2],
    ['U003','Cissokho','Maimouna','maimouna.cissokho@esp.sn','770000003','123457','GESTIONNAIRE',2],
    ['U004','Mbaye','Aissata','aissata.mbaye@esp.sn','770000004','123458','GESTIONNAIRE',2],
    ['U005','Diop','Awa','awa.diop@esp.sn','770000005','123459','ETUDIANT',0],
    ['U006','Ndiaye','Cheikh','cheikh.ndiaye@esp.sn','770000006','123460','ETUDIANT',0],
    ['U007','Sow','Fatou','fatou.sow@esp.sn','770000007','123461','ETUDIANT',0],
    ['U008','Ba','Ousmane','ousmane.ba@esp.sn','770000008','123462','PERSONNEL',1],
    ['U009','Gueye','Ramatoulaye','rama.gueye@esp.sn','770000009','123463','PERSONNEL',1],
    ['U010','Sarr','Modou','modou.sarr@esp.sn','770000010','123464','GESTIONNAIRE',2],
];

foreach ($users as $u) {

    $hash = password_hash($u[5], PASSWORD_DEFAULT);

    creerUtilisateur($pdo, $u[0], $u[1], $u[2], $u[3], $u[4], $hash, $u[6], $u[7]);
}