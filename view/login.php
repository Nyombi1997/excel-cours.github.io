
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

<!-- login -->
<script src="<?php echo ASSET; ?>js/login.js?<?= filemtime(ROOT."asset/js/login.js") ?>"></script>