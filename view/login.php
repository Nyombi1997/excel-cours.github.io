
<div class="parent_container_connexion">
        <div class="container">
            <div class="tabs">
                <div class="tab active" onclick="switchTab('login')">Connexion</div>
                <div class="tab" onclick="switchTab('register')">Inscription</div>
            </div>

            <form id="loginForm" autocomplete="off">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="loginEmail" name="email" placeholder="votre@email.com" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" id="loginMdp" class="pwd-input" name="password" required autocomplete="new-password">
                    <span class="toggle-password" onclick="toggleVisibility(this)">Afficher</span>
                </div>
                <button type="submit">Se connecter</button>
            </form>

            <form id="registerForm" class="hidden" autocomplete="off">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" id="regName" placeholder="Ex: Jean" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="regEmail" placeholder="votre@email.com" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" id="regPwd" class="pwd-input" required autocomplete="new-password">
                    <span class="toggle-password" onclick="toggleVisibility(this)">Afficher</span>
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" id="regConfirmPwd" class="pwd-input" required autocomplete="new-password">
                    <div id="pwdError" class="error-msg">Les mots de passe ne correspondent pas.</div>
                </div>
                <button type="submit">Créer un compte</button>
            </form>
        </div>
    </div>

<script>
    // Basculer entre Connexion et Inscription
    function switchTab(type) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const tabs = document.querySelectorAll('.tab');

        if (type === 'login') {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            tabs[0].classList.add('active');
            tabs[1].classList.remove('active');
        } else {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            tabs[1].classList.add('active');
            tabs[0].classList.remove('active');
        }
    }

    // Afficher/Masquer le mot de passe
    function toggleVisibility(btn) {
        const input = btn.parentElement.querySelector('.pwd-input');
        if (input.type === "password") {
            input.type = "text";
            btn.innerText = "Masquer";
        } else {
            input.type = "password";
            btn.innerText = "Afficher";
        }
    }

    // Contrôle JS de l'inscription
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('regName').value;
        const email = document.getElementById('regEmail').value;
        const pwd = document.getElementById('regPwd').value;
        const confirmPwd = document.getElementById('regConfirmPwd').value;
        const errorMsg = document.getElementById('pwdError');

        // Validation simple
        if (pwd !== confirmPwd) {
            errorMsg.style.display = 'block';
            return;
        } else {
            errorMsg.style.display = 'none';
        }

        if (pwd.length < 6) {
            alert("Le mot de passe doit contenir au moins 6 caractères.");
            return;
        }

        // Si tout est correct
        $.post("/fonctions/signin.php",
            {
                name: name,
                email: email,
                mdp: pwd
            },
            function(data)
            {
                if(data.result == "ok")
                {
                        Swal.fire({
                        icon: "success",
                        title: "Connexion réussie !",
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: '#4caf50',
                        timer: 1500
                    }).then(() => {
                        window.location = '/compte';
                    });
                }
                else
                {
                        Swal.fire({
                        icon: "error",
                        title: data.msg,
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: '#4caf50'
                    })
                }
            }
        )
    });

    // Contrôle JS de l'inscription
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const pwd = document.getElementById('loginMdp').value;

        // Si tout est correct
        $.post("/fonctions/login.php",
            {
                email: email,
                mdp: pwd
            },
            function(data)
            {
                if(data.result == "admin")
                {
                        Swal.fire({
                        icon: "success",
                        title: "Connexion réussie !",
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: '#4caf50',
                        timer: 1500
                    }).then(() => {
                        window.location = '/admin';
                    });
                }
                else if(data.result == "ok")
                {
                        Swal.fire({
                        icon: "success",
                        title: "Connexion réussie !",
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: '#4caf50',
                        timer: 1500
                    }).then(() => {
                        window.location = '/compte';
                    });
                }
                else
                {
                        Swal.fire({
                        icon: "error",
                        title: data.msg,
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: '#4caf50'
                    })
                }
            }
        )
    });
</script>