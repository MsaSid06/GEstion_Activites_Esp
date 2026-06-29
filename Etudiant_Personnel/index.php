<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP Dakar - Authentification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #F3F4F6; font-family: sans-serif; }
        .bg-esp-purple { background-color: #650665 !important; }
        .text-esp-purple { color: #650665 !important; }
        .bg-esp-gold { background-color: #D4AF37 !important; }
        .bg-right-maquette { background-color: #EAE3EB !important; }
        .input-maquette-white { background-color: #FFFFFF !important; color: #1F2937; border: none; }
        .input-maquette-blue { background-color: #EBF2FA !important; color: #1F2937; border: none; }
        
        .circle-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-[#F3F4F6] font-sans min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-[1050px] rounded-[32px] shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[620px]">
        
        <div class="md:col-span-5 bg-esp-purple p-12 flex flex-col justify-between relative overflow-hidden min-h-[300px] md:min-h-full">
            <div class="circle-bg w-[280px] h-[280px]"></div>
            <div class="circle-bg w-[420px] h-[420px]"></div>
            <div class="circle-bg w-[560px] h-[560px]"></div>

            <div class="z-10">
                <span class="bg-esp-gold text-gray-900 font-extrabold text-xs px-3.5 py-1.5 rounded-lg tracking-wider uppercase inline-block mb-6 shadow-sm">ESP</span>
                <h2 class="text-3xl font-black text-white tracking-tight leading-tight max-w-xs">
                    École Supérieure Polytechnique de Dakar
                </h2>
            </div>
            <div class="z-10 text-white/40 text-[11px] font-medium uppercase tracking-widest hidden md:block">Service Scolarité</div>
        </div>

        <div class="md:col-span-7 bg-right-maquette p-8 md:p-12 flex flex-col justify-center relative">
            
            <div id="login-form" class="space-y-6 max-w-sm mx-auto w-full block">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight mb-6">Connexion</h2>
                </div>
                <form onsubmit="handleLogin(event)" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-600 block mb-1.5">Email</label>
                        <input id="login-email" type="email" required placeholder="aminatafaye4@esp.sn" class="w-full px-4 py-3 input-maquette-blue rounded-xl text-sm outline-none font-medium transition focus:ring-1 focus:ring-purple-400">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-600 block mb-1.5">Mot de passe</label>
                        <div class="relative flex items-center">
                            <input id="login-password" type="password" required class="w-full px-4 py-3 input-maquette-blue rounded-xl text-sm outline-none font-medium transition focus:ring-1 focus:ring-purple-400 pr-10">
                            <button type="button" onclick="togglePasswordVisibility('login-password')" class="absolute right-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-esp-gold hover:bg-[#C59B27] text-gray-900 font-bold text-sm py-3.5 rounded-xl transition shadow-md mt-2">
                        Se connecter
                    </button>
                </form>
                
                <p class="text-center text-xs text-gray-600 font-medium pt-2">
                    Nouveau ? <button onclick="toggleForm(true)" class="text-esp-purple font-bold hover:underline">S'inscrire ici</button>
                </p>
            </div>

            <div id="register-form" class="space-y-4 max-w-sm mx-auto w-full hidden">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight mb-4">Inscription</h2>
                </div>

                <form onsubmit="handleRegister(event)" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <input id="reg-nom" type="text" required placeholder="Nom" class="w-full px-4 py-3 input-maquette-white rounded-xl text-sm outline-none font-medium shadow-sm">
                        <input id="reg-prenom" type="text" required placeholder="Prénom" class="w-full px-4 py-3 input-maquette-white rounded-xl text-sm outline-none font-medium shadow-sm">
                    </div>
                    
                    <input id="reg-tel" type="tel" required placeholder="Téléphone" class="w-full px-4 py-3 input-maquette-white rounded-xl text-sm outline-none font-medium shadow-sm">
                    <input id="reg-email" type="email" required placeholder="aminatambaye4@esp.sn" class="w-full px-4 py-3 input-maquette-blue rounded-xl text-sm outline-none font-medium transition focus:ring-1 focus:ring-purple-400">
                    
                    <div class="relative flex items-center">
                        <input id="reg-password" type="password" required class="w-full px-4 py-3 input-maquette-blue rounded-xl text-sm outline-none font-medium transition focus:ring-1 focus:ring-purple-400 pr-10">
                        <button type="button" onclick="togglePasswordVisibility('reg-password')" class="absolute right-3 text-gray-400 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 bg-gray-200/50 p-1 rounded-xl gap-1">
                        <button onclick="selectRole('ETUDIANT')" id="role-etu" type="button" class="py-2.5 rounded-lg text-xs font-bold transition bg-white text-esp-purple shadow-sm">Étudiant</button>
                        <button onclick="selectRole('PERSONNEL')" id="role-per" type="button" class="py-2.5 rounded-lg text-xs font-bold text-gray-600 transition hover:bg-white/40">Personnel</button>
                    </div>

                    <div id="fields-etudiant" class="bg-white p-4 rounded-2xl space-y-2.5 shadow-sm block">
                        <input id="reg-filiere" type="text" placeholder="Filière (Ex: Informatique)" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
                        <input id="reg-niveau" type="text" placeholder="Niveau (Ex: DUT2)" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
                    </div>

                    <div id="fields-personnel" class="bg-white p-4 rounded-2xl space-y-2.5 shadow-sm hidden">
                        <input id="reg-poste" type="text" placeholder="Poste / Rôle (Ex: Enseignant)" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
                        <input id="reg-specialite" type="text" placeholder="Spécialité (Ex: Algorithmique)" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl text-sm outline-none border border-gray-100 font-medium">
                    </div>

                    <button type="submit" class="w-full bg-esp-purple hover:bg-[#3b0b3e] text-white font-bold text-xs py-3.5 rounded-xl uppercase tracking-wider transition shadow-md mt-2">
                        Créer mon compte
                    </button>
                </form>
                
                <p class="text-center text-xs text-gray-600 font-medium">
                    Déjà inscrit ? <button onclick="toggleForm(false)" class="text-esp-purple font-bold hover:underline">Se connecter</button>
                </p>
            </div>

        </div>
    </div>
<script>
        let currentRole = 'ETUDIANT';

        // AJOUT : Fonction manquante pour basculer entre Connexion et Inscription
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

        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            const button = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                `;
            } else {
                input.type = 'password';
                button.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                `;
            }
        }

        function selectRole(role) {
            currentRole = role;
            const isEtu = role === 'ETUDIANT';
            
            document.getElementById('role-etu').className = isEtu ? "py-2.5 rounded-lg text-xs font-bold transition bg-white text-esp-purple shadow-sm" : "py-2.5 rounded-lg text-xs font-bold text-gray-600 transition hover:bg-white/40";
            document.getElementById('role-per').className = !isEtu ? "py-2.5 rounded-lg text-xs font-bold transition bg-white text-esp-purple shadow-sm" : "py-2.5 rounded-lg text-xs font-bold text-gray-600 transition hover:bg-white/40";
            
            const fieldEtu = document.getElementById('fields-etudiant');
            const fieldPer = document.getElementById('fields-personnel');
            if (isEtu) {
                fieldEtu.classList.replace('hidden', 'block');
                fieldPer.classList.replace('block', 'hidden');
            } else {
                fieldEtu.classList.replace('block', 'hidden');
                fieldPer.classList.replace('hidden', 'block');
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            const data = {
                email: document.getElementById('login-email').value,
                mot_de_passe: document.getElementById('login-password').value
            };

            const res = await fetch('api_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();

            if (res.ok && result.success) {
                localStorage.setItem('user_name', result.prenom + ' ' + result.nom);
                localStorage.setItem('user_profil', result.profil);
                window.location.href = 'dashboard_etd.php';
            } else {
                alert(result.error || "Identifiants incorrects");
            }
        }

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

            const res = await fetch('api_register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            alert(result.message || result.error);
            if (res.ok && !result.error) toggleForm(false);
        }
        
    </script>
