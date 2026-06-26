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
      background-color: #4a0e4e;
    }

    .text-esp-purple {
      color: #4a0e4e;
    }

    .bg-esp-gold {
      background-color: #d4af37;
    }

    .bg-right-pink {
      background-color: #eae0eb;
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
          École Supérieure<br />
          Polytechnique<br />
          de Dakar
        </h1>

        <p class="text-purple-200/70 mt-4 text-sm max-w-sm font-medium">
          Plateforme de planification et de gestion des activités annuelles de
          l'ESP.
        </p>
      </div>
    </div>

    <div class="p-12 md:w-1/2 flex flex-col justify-center bg-right-pink">
      <div class="space-y-6">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Connexion</h2>
          <p class="text-sm text-gray-500 mt-1">
            Planification des activités — Accès sécurisé
          </p>
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

              <button type="button" onclick="togglePasswordVisibility()" class="absolute right-4 top-3 text-lg">
                <span id="eye-icon">👁</span>
              </button>
            </div>
          </div>

          <button type="submit" class="w-full bg-esp-gold text-gray-900 font-bold py-3.5 rounded-xl">
            Se connecter
          </button>
          <p id="message"></p>
        </form>
      </div>
    </div>
  </div>

  <div id="forgot-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
      <div class="flex justify-between items-center border-b pb-2">
        <h3 class="text-lg font-bold">Récupération de compte</h3>

        <button type="button" onclick="closeForgotPasswordModal()">✖</button>
      </div>

      <p class="text-sm text-gray-500 mt-4">
        Saisissez votre adresse email académique.
      </p>

      <form class="space-y-4 mt-4">
        <input type="email" placeholder="nom.prenom@esp.sn" class="w-full px-4 py-3 bg-gray-50 rounded-xl" />

        <div class="flex justify-end gap-3">
          <button type="button" onclick="closeForgotPasswordModal()">
            Annuler
          </button>

          <button type="submit" class="px-4 py-2 text-white bg-esp-purple rounded-xl">
            Envoyer
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function togglePasswordVisibility() {
      const input = document.getElementById("login-password");
      const eye = document.getElementById("eye-icon");
      if (input.type === "password") {
        input.type = "text";
        eye.textContent = "🙈";
      } else {
        input.type = "password";
        eye.textContent = "👁";
      }
    }

    function openForgotPasswordModal() {
      document.getElementById("forgot-modal").classList.remove("hidden");
    }

    function closeForgotPasswordModal() {
      document.getElementById("forgot-modal").classList.add("hidden");
    }
    let currentProfile = "ETUDIANT";

    document
      .getElementById("loginform")
      .addEventListener("submit", function(e) {
        e.preventDefault();

        let email = document.getElementById("email").value;
        let password = document.getElementById("login-password").value;

        fetch("./Gestionnaire/controllers/login.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "email=" +
              encodeURIComponent(email) +
              "&password=" +
              encodeURIComponent(password),
          })
          .then((response) => response.text())
          .then((data) => {
            switch (data) {
              case '1':
                window.location.href = "./Etudiant_Personnel/dashboard_etd.php";
                break;
              case '2':
                window.location.href = "./Gestionnaire/DashboardGestionnaire.php"
                break;

              case '3':
                window.location.href = "./Admin/admin/dashboard.php";
                break;

            }

            if (data != "succes") {

              document.getElementById("message").innerText = data;
              // window.location.href = "./Gestionnaire/DashboardGestionnaire.php"

            }
          });
      });
    fetch("../controllers/login.php", {
        method: "POST",
        body: formData,
      })
      .then((response) => response.text())
      .then((data) => {
        if (data.trim() === "success") {
          window.location.href = "../DashboardGestionnaire.php";
        }

        if (data.trim() === "invalid") {
          alert("Email ou mot de passe incorrect");
        }
      });
  </script>
</body>

</html>