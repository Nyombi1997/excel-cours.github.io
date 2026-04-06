<?php
    include_once FONCTION . 'profile_context.php';

    profile_start_session_if_needed();

    $user = profile_find_user_by_unique_id($bdd, profile_connected_user_unique_id());
    if (!$user) {
        header("location: /connexion");
        exit;
    }

    $can_edit_profile = profile_can_edit_user($user);
    if (!$can_edit_profile) {
        header("location: /compte");
        exit;
    }

    $profile_image = profile_get_avatar_url($user);
    $member_since = !empty($user['date_ajout']) ? date_fr_short($user['date_ajout']) : 'Non renseignée';
?>

<div class="container_profile_cours_excel profile-page-reset">
    <aside class="sidebar">
        <div class="card profile-sidebar-card">
            <span class="profile-sidebar-kicker">Modifier le compte</span>
            <h2 class="profile-sidebar-title">Mes informations</h2>
            <p class="profile-sidebar-text">
                Modifiez votre photo de profil, vos informations personnelles et votre mot de passe.
            </p>
        </div>

        <div class="card profile-sidebar-card">
            <span style="font-weight: bold;">Aide rapide</span>
            <div class="profile-sidebar-list">
                <div class="profile-sidebar-item">
                    <span>Nom actuel</span>
                    <strong class="js-account-name"><?= htmlspecialchars($user['user_name']) ?></strong>
                </div>
                <div class="profile-sidebar-item">
                    <span>Adresse e-mail</span>
                    <strong class="js-account-email"><?= htmlspecialchars($user['email']) ?></strong>
                </div>
                <div class="profile-sidebar-item">
                    <span>Membre depuis</span>
                    <strong><?= htmlspecialchars($member_since) ?></strong>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="card profile-intro-card">
            <div class="profile-edit-header">
                <div class="profile-pic-container profile-pic-container-large">
                    <img src="<?= $profile_image ?>" id="avatar-preview" class="profile-pic js-account-avatar" alt="Photo de profil">
                    <label for="file-input" class="upload-btn" title="Modifier la photo de profil">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                    <input type="file" id="file-input" style="display:none" accept="image/png, image/jpeg, image/webp">
                </div>

                <div class="profile-edit-header__content">
                    <span class="profile-inline-badge">Édition du compte</span>
                    <h1 class="profile-intro-title">Mettre à jour mon profil</h1>
                    <p class="profile-intro-text">
                        La photo est recadrée avant l'envoi. Les informations et le mot de passe se mettent à jour sans quitter la page.
                    </p>
                    <div class="profile-main-actions">
                        <a href="/compte" class="account-secondary-btn">Retour au profil</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Informations personnelles</h3>
            <p class="profile-help-text">
                Vérifiez vos informations avant d'enregistrer. Les champs restent maintenant correctement contenus dans la carte.
            </p>

            <form id="account-profile-form" class="grid-form profile-form-fix" autocomplete="off" data-can-edit="1">
                <div>
                    <label for="account_user_name">Nom d'utilisateur</label>
                    <input
                        type="text"
                        id="account_user_name"
                        name="user_name"
                        value="<?= htmlspecialchars($user['user_name']) ?>"
                    >
                </div>
                <div>
                    <label for="account_email">Adresse e-mail</label>
                    <input
                        type="email"
                        id="account_email"
                        name="email"
                        value="<?= htmlspecialchars($user['email']) ?>"
                    >
                </div>
                <div class="full-width">
                    <button type="submit">Enregistrer les informations personnelles</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Sécurité du compte</h3>
            <p class="profile-help-text">
                Pour changer le mot de passe, remplissez les trois champs. Sinon, laissez-les vides.
            </p>

            <form id="account-security-form" class="grid-form profile-form-fix" autocomplete="off" data-can-edit="1">
                <div>
                    <label for="account_old_password">Ancien mot de passe</label>
                    <div class="profile-password-field">
                        <input type="password" id="account_old_password" name="old_password">
                        <button type="button" class="account-password-toggle" data-target="#account_old_password">Afficher</button>
                    </div>
                </div>
                <div>
                    <label for="account_new_password">Nouveau mot de passe</label>
                    <div class="profile-password-field">
                        <input type="password" id="account_new_password" name="new_password">
                        <button type="button" class="account-password-toggle" data-target="#account_new_password">Afficher</button>
                    </div>
                </div>
                <div class="full-width">
                    <label for="account_confirm_password">Confirmer le nouveau mot de passe</label>
                    <div class="profile-password-field">
                        <input type="password" id="account_confirm_password" name="confirm_password">
                        <button type="button" class="account-password-toggle" data-target="#account_confirm_password">Afficher</button>
                    </div>
                </div>
                <div class="full-width">
                    <button type="submit">Mettre à jour le mot de passe</button>
                </div>
            </form>
        </div>
    </main>

    <div id="cropper-modal" class="account-cropper-modal">
        <div class="account-cropper-card">
            <div class="account-cropper-card__head">
                <h3>Recadrer la photo de profil</h3>
                <button type="button" class="account-close-btn" id="close-cropper-modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <p>Choisissez un cadrage carré puis lancez l'envoi. La progression du chargement s'affiche juste en dessous.</p>
            <div class="account-cropper-frame">
                <img id="image-to-crop" src="" alt="Image à recadrer">
            </div>
            <div class="account-upload-progress null" id="account_upload_progress_wrap">
                <div class="account-upload-progress__head">
                    <span>Chargement de la photo de profil</span>
                    <strong id="account_upload_progress_text">0%</strong>
                </div>
                <div class="account-upload-progress__bar">
                    <div class="account-upload-progress__fill" id="account_upload_progress_fill" style="width: 0%;"></div>
                </div>
            </div>
            <div class="account-cropper-actions">
                <button type="button" class="account-primary-btn" id="save-cropped-avatar">Enregistrer la photo</button>
                <button type="button" class="account-secondary-btn" id="cancel-cropper-modal">Annuler</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo ASSET; ?>js/account.js?<?= filemtime(ROOT . "asset/js/account.js") ?>"></script>
