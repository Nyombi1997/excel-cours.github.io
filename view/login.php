<section class="auth-page-shell">
    <div class="auth-page-grid">
        <div class="auth-brand-panel">
            <span class="auth-kicker">Espace sécurisé AbsoluHub</span>
            <h1>Accédez à une plateforme claire, premium et orientée progression.</h1>
            <p>
                Connectez-vous pour suivre vos cours, retrouver votre espace personnel
                et interagir avec l'équipe AbsoluHub depuis une interface cohérente et professionnelle.
            </p>

            <div class="auth-brand-points">
                <div class="auth-brand-point">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Accès sécurisé et session claire</span>
                </div>
                <div class="auth-brand-point">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Navigation fluide entre cours, compte et messages</span>
                </div>
                <div class="auth-brand-point">
                    <i class="fa-solid fa-wave-square"></i>
                    <span>Univers visuel aligné sur la charte AbsoluHub</span>
                </div>
            </div>
        </div>

        <div class="parent_container_connexion auth-form-shell">
            <div class="container auth-card">
                <div class="tabs">
                    <div class="tab active" onclick="switchTab('login')">Connexion</div>
                    <div class="tab" onclick="switchTab('register')">Inscription</div>
                </div>

                <form id="loginForm" autocomplete="off">
                    <div class="form-group">
                        <label for="loginEmail">Adresse e-mail</label>
                        <input type="email" id="loginEmail" name="email" placeholder="votre@email.com" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="loginMdp">Mot de passe</label>
                        <input type="password" id="loginMdp" class="pwd-input" name="password" required autocomplete="new-password">
                        <span class="toggle-password" onclick="toggleVisibility(this)">Afficher</span>
                    </div>
                    <button type="submit">Se connecter</button>
                </form>

                <form id="registerForm" class="hidden" autocomplete="off">
                    <div class="form-group">
                        <label for="regName">Nom d'utilisateur</label>
                        <input type="text" id="regName" placeholder="Ex. Jean Dupont" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="regEmail">Adresse e-mail</label>
                        <input type="email" id="regEmail" placeholder="votre@email.com" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="regPwd">Mot de passe</label>
                        <input type="password" id="regPwd" class="pwd-input" required autocomplete="new-password">
                        <span class="toggle-password" onclick="toggleVisibility(this)">Afficher</span>
                    </div>
                    <div class="form-group">
                        <label for="regConfirmPwd">Confirmer le mot de passe</label>
                        <input type="password" id="regConfirmPwd" class="pwd-input" required autocomplete="new-password">
                        <div id="pwdError" class="error-msg">Les mots de passe ne correspondent pas.</div>
                    </div>
                    <button type="submit">Créer un compte</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo ASSET; ?>js/login.js?<?= filemtime(ROOT . "asset/js/login.js") ?>"></script>
