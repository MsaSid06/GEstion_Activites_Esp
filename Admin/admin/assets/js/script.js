/**
 * assets/js/script.js
 * Inscription : on n'affiche les champs spécifiques (filière/niveau ou poste/
 * spécialité) qu'APRÈS le choix Étudiant / Personnel, et on bascule l'attribut
 * "required" pour que seuls les champs visibles soient obligatoires.
 */
(function () {
    'use strict';

    var form = document.getElementById('register-form');
    if (!form) {
        return;
    }

    var blocEtudiant  = document.getElementById('bloc-etudiant');
    var blocPersonnel = document.getElementById('bloc-personnel');
    var radios = form.querySelectorAll('input[name="type"]');

    // Champs concernés par le passage en "required".
    var champsEtudiant  = ['filiere', 'niveau'];
    var champsPersonnel = ['poste']; // spécialité reste optionnelle

    function setRequired(ids, valeur) {
        ids.forEach(function (id) {
            var champ = document.getElementById(id);
            if (champ) {
                if (valeur) {
                    champ.setAttribute('required', 'required');
                } else {
                    champ.removeAttribute('required');
                }
            }
        });
    }

    function appliquerChoix(type) {
        var estEtudiant  = type === 'etudiant';
        var estPersonnel = type === 'personnel';

        blocEtudiant.hidden  = !estEtudiant;
        blocPersonnel.hidden = !estPersonnel;

        setRequired(champsEtudiant, estEtudiant);
        setRequired(champsPersonnel, estPersonnel);
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            appliquerChoix(this.value);
        });
    });

    // Au chargement : si un choix est déjà coché (ex. après une erreur de
    // validation côté serveur), on rétablit l'affichage correspondant.
    var coche = form.querySelector('input[name="type"]:checked');
    if (coche) {
        appliquerChoix(coche.value);
    }
})();
