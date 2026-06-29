<?php
session_start();
if (isset($_SESSION['matricule_user'])) {
    switch ($_SESSION['profil']) {
        case 'ADMIN':
            header("Location: ./Admin/admin/dashboard.php");
            exit;
        case 'GESTIONNAIRE':
            header("Location: ./DashboardGestionnaire.php");
            exit;
        case 'ETUDIANT':
            header("Location: ./Etudiant_Personnel/dashboard_etd.php");
            exit;
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ESP Dakar - Authentification</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .bg-esp-purple {
      background-color: #650665;
    }

    .text-esp-purple {
      color: #650665;
    }

    .bg-esp-gold {
      background-color: #d4af37;
    }

    .bg-right-pink {
      background-color: #eae0eb;
    }

    .input-maquette-white {
      background-color: #FFFFFF !important;
      color: #1F2937;
      border: none;
    }

    .radial-circles {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 600px;
      height: 600px;
      background: radial-gradient(circle,
          transparent 30%,
          rgba(255, 255, 255, 0.03) 31%,
          transparent 32%,
          transparent 50%,
          rgba(255, 255, 255, 0.03) 51%,
          transparent 52%);
      pointer-events: none;
    }

    input::-ms-reveal,
    input::-webkit-contacts-auto-fill-button {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gray-200 min-h-screen flex items-center justify-center p-4 font-sans antialiased">
  <div class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-5xl w-full flex flex-col md:flex-row min-h-[620px]">

    <div class="bg-esp-purple text-white p-12 md:w-1/2 flex flex-col justify-between relative overflow-hidden">
      <div class="radial-circles"></div>
      <div class="relative z-10">
        <span class="bg-esp-gold text-black font-bold px-3 py-1.5 rounded-lg text-sm tracking-wider">ESP</span>
        <h1 class="text-3xl font-extrabold mt-8 leading-tight tracking-wide">
          École Supérieure<br />Polytechnique<br />de Dakar
        </h1>
        <p class="text-purple-200/70 mt-4 text-sm max-w-sm font-medium">
          Plateforme de planification et de gestion des activités annuelles de l'ESP.
        </p>
      </div>
    </div>

    <div class="p-12 md:w-1/2 flex flex-col justify-center bg-right-pink">

      <!-- FORMULAIRE CONNEXION -->
      <div id="login-form" class="space-y-6 block">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Connexion</h2>
          <p class="text-sm text-gray-500 mt-1">Planification des activités — Accès sécurisé</p>
        </div>

        <form id="loginform" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1.5">Adresse email</label>
            <input id="email" type="email" placeholder="votre.email@esp.sn"
              class="w-full px-4 py-3 bg-white rounded-xl shadow-sm" />
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-sm font-semibold text-gray-800">Mot de passe</label>
              <button type="button" onclick="openForgotPasswordModal()" class="text-xs text-esp-purple font-bold">
                Mot de passe oublié ?
              </button>
            </div>
            <div class="relative">
              <input id="login-password" type="password" placeholder="••••••••"
                class="w-full px-4 py-3 bg-white rounded-xl shadow-sm pr-12" />
              <!-- OEIL SVG CONNEXION -->
              <button type="button" onclick="togglePasswordVisibility()"
                class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                <span id="eye-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </span>
              </button>
            </div>
          </div>

          <button type="submit" class="w-full bg-esp-gold text-gray-900 font-bold py-3.5 rounded-xl">
            Se connecter
          </button>
          <p id="message"></p>
        </form>

        <p class="text-center text-xs text-gray-600 font-medium pt-2">
          Nouveau ? <button onclick="toggleForm(true)" class="text-esp-purple font-bold hover:underline">S'inscrire
            ici</button>
        </p>
      </div>

      <!-- FORMULAIRE INSCRIPTION -->
      <div id="register-form" class="space-y-4 hidden">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Inscription</h2>
          <p class="text-sm text-gray-500 mt-1">Créez votre compte ESP</p>
        </div>

        <form onsubmit="handleRegister(event)" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <input id="reg-nom" type="text" required placeholder="Nom"
              class="w-full px-4 py-3 input-maquette-white rounded-xl text-sm outline-none font-medium shadow-sm">
            <input id="reg-prenom" type="text" required placeholder="Prénom"
              class="w-full px-4 py-3 input-maquette-white rounded-xl text-sm outline-none font-medium shadow-sm">
          </div>

          <input id="reg-tel" type="tel" required placeholder="Téléphone"
            class="w-full px-4 py-3 input-maquette-white rounded-xl text-sm outline-none font-medium shadow-sm">

          <input id="reg-email" type="email" required placeholder="votre.email@esp.sn"
            class="w-full px-4 py-3 bg-white rounded-xl text-sm outline-none font-medium shadow-sm">

          <div class="relative flex items-center">
            <input id="reg-password" type="password" required placeholder="••••••••"
              class="w-full px-4 py-3 bg-white rounded-xl text-sm outline-none font-medium shadow-sm pr-10">
            <!-- OEIL SVG INSCRIPTION -->
            <button type="button" onclick="toggleRegPasswordVisibility()"
              class="absolute right-3 text-gray-400 hover:text-gray-600 focus:outline-none">
              <span id="eye-icon-reg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                  stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </span>
            </button>
          </div>

          <div class="grid grid-cols-2 bg-gray-200/50 p-1 rounded-xl gap-1">
            <button onclick="selectRole('ETUDIANT')" id="role-etu" type="button"
              class="py-2.5 rounded-lg text-xs font-bold transition bg-white text-esp-purple shadow-sm">Étudiant</button>
            <button onclick="selectRole('PERSONNEL')" id="role-per" type="button"
              class="py-2.5 rounded-lg text-xs font-bold text-gray-600 transition hover:bg-white/40">Personnel</button>
          </div>

          <div id="fields-etudiant" class="bg-white p-4 rounded-2xl space-y-2.5 shadow-sm block">
            <input id="reg-filiere" type="text" placeholder="Filière (Ex: Informatique)"
              class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
            <input id="reg-niveau" type="text" placeholder="Niveau (Ex: DUT2)"
              class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
          </div>

          <div id="fields-personnel" class="bg-white p-4 rounded-2xl space-y-2.5 shadow-sm hidden">
            <input id="reg-poste" type="text" placeholder="Poste / Rôle (Ex: Enseignant)"
              class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
            <input id="reg-specialite" type="text" placeholder="Spécialité (Ex: Algorithmique)"
              class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
          </div>

          <button type="submit"
            class="w-full bg-esp-purple hover:bg-[#3b0b3e] text-white font-bold text-xs py-3.5 rounded-xl uppercase tracking-wider transition shadow-md mt-2">
            Créer mon compte
          </button>
        </form>

        <p class="text-center text-xs text-gray-600 font-medium">
          Déjà inscrit ? <button onclick="toggleForm(false)" class="text-esp-purple font-bold hover:underline">Se
            connecter</button>
        </p>
      </div>

    </div>
  </div>

  <!-- MODAL MOT DE PASSE OUBLIÉ -->
  <div id="forgot-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
      <div class="flex justify-between items-center border-b pb-2">
        <h3 class="text-lg font-bold">Récupération de compte</h3>
        <button type="button" onclick="closeForgotPasswordModal()">✖</button>
      </div>
      <p class="text-sm text-gray-500 mt-4">Saisissez votre adresse email académique.</p>
      <form class="space-y-4 mt-4">
        <input type="email" placeholder="nom.prenom@esp.sn" class="w-full px-4 py-3 bg-gray-50 rounded-xl" />
        <div class="flex justify-end gap-3">
          <button type="button" onclick="closeForgotPasswordModal()">Annuler</button>
          <button type="submit" class="px-4 py-2 text-white bg-esp-purple rounded-xl">Envoyer</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let currentRole = 'ETUDIANT';

    function toggleForm(showRegister) {
      const loginForm = document.getElementById('login-form');
      const registerForm = document.getElementById('register-form');
      if (showRegister) {
        loginForm.classList.replace('block', 'hidden');
        registerForm.classList.replace('hidden', 'block');
      } else {
        loginForm.classList.replace('hidden', 'block');
        registerForm.classList.replace('block', 'hidden');
      }
    }

    // OEIL SVG CONNEXION
    function togglePasswordVisibility() {
      const input = document.getElementById("login-password");
      const eye = document.getElementById("eye-icon");
      if (input.type === "password") {
        input.type = "text";
        eye.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
        </svg>`;
      } else {
        input.type = "password";
        eye.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>`;
      }
    }

    // OEIL SVG INSCRIPTION
    function toggleRegPasswordVisibility() {
      const input = document.getElementById("reg-password");
      const eye = document.getElementById("eye-icon-reg");
      if (input.type === "password") {
        input.type = "text";
        eye.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
        </svg>`;
      } else {
        input.type = "password";
        eye.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>`;
      }
    }

    function openForgotPasswordModal() {
      document.getElementById("forgot-modal").classList.remove("hidden");
    }

    function closeForgotPasswordModal() {
      document.getElementById("forgot-modal").classList.add("hidden");
    }

    function selectRole(role) {
      currentRole = role;
      const isEtu = role === 'ETUDIANT';
      document.getElementById('role-etu').className = isEtu ?
        "py-2.5 rounded-lg text-xs font-bold transition bg-white text-esp-purple shadow-sm" :
        "py-2.5 rounded-lg text-xs font-bold text-gray-600 transition hover:bg-white/40";
      document.getElementById('role-per').className = !isEtu ?
        "py-2.5 rounded-lg text-xs font-bold transition bg-white text-esp-purple shadow-sm" :
        "py-2.5 rounded-lg text-xs font-bold text-gray-600 transition hover:bg-white/40";

      document.getElementById('fields-etudiant').classList.toggle('hidden', !isEtu);
      document.getElementById('fields-etudiant').classList.toggle('block', isEtu);
      document.getElementById('fields-personnel').classList.toggle('hidden', isEtu);
      document.getElementById('fields-personnel').classList.toggle('block', !isEtu);
    }

    document.getElementById("loginform").addEventListener("submit", function(e) {
      e.preventDefault();
      let email = document.getElementById("email").value;
      let password = document.getElementById("login-password").value;

      fetch("./Gestionnaire/controllers/login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "email=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(password),
        })
        .then(response => response.text())
        .then(data => {
          switch (data) {
            case '0':
              window.location.href = "./Etudiant_Personnel/dashboard_etd.php";
            case '1':
              window.location.href = "./Etudiant_Personnel/dashboard_etd.php";
              break;
            case '2':
              window.location.href = "./Gestionnaire/DashboardGestionnaire.php";
              break;
            case '3':
              window.location.href = "./Admin/admin/dashboard.php";
              break;
          }
          if (data != "1" || data != "2" || data != "3") {

            document.getElementById("message").innerHTML = data;
            setTimeout(() => {
              document.getElementById("message").innerHTML = "";

            }, 1000);
          }
        });
    });

    async function handleRegister(e) {
      e.preventDefault();
      const data = {
        nom: document.getElementById('reg-nom').value || '',
        prenom: document.getElementById('reg-prenom').value || '',
        email: document.getElementById('reg-email').value || '',
        tel: document.getElementById('reg-tel').value || '',
        mot_de_passe: document.getElementById('reg-password').value || '',
        profil: currentRole,
        filiere: document.getElementById('reg-filiere').value || '',
        niveau: document.getElementById('reg-niveau').value || '',
        poste: document.getElementById('reg-poste').value || '',
        specialite: document.getElementById('reg-specialite').value || ''
      };

      const res = await fetch('./Etudiant_Personnel/api_register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });
      const result = await res.json();
      alert(result.message || result.error);
      if (res.ok && !result.error) toggleForm(false);
    }
  </script>
</body>

</html>