
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
                Swal.fire({
                icon: "error",
                title: "Le mot de passe doit contenir au moins 6 caractères.",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: site_color
            })
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
                            confirmButtonColor: site_color,
                            timer: 1500,
                            iconColor: site_color
                        }).then(() => {
                            window.location = '/compte';
                        });      }
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
                        confirmButtonColor: site_color,
                        timer: 1500,
                        iconColor: site_color
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
                        confirmButtonColor: site_color,
                        timer: 1500,
                        iconColor: site_color
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