<?php
    include_once FONCTION . 'profile_context.php';
    include_once FONCTION . 'contact_context.php';

    $actor = contact_get_connected_actor($bdd);
    if (!$actor) {
        header("location: /connexion");
        exit;
    }

    $schema_ready = contact_schema_ready($bdd);
    $conversation = $schema_ready ? contact_find_conversation_by_user_unique_id($bdd, (string) $actor['user']['unique_id']) : null;
    $phone_display = '+243 813 689 713';
    $phone_raw = '243813689713';
?>

<section class="contact-page-shell">
    <div class="contact-page-hero">
        <div class="contact-page-hero__content">
            <span class="contact-page-kicker">Contact direct</span>
            <h1>Entrons en contact facilement</h1>
            <!-- <p>
                Depuis cette page, l'utilisateur connecté peut appeler, ouvrir WhatsApp et laisser un message directement sur le site.
            </p> -->
            <!-- <div class="contact-page-hero__badges">
                <span><i class="fa-solid fa-shield-halved"></i> Accès réservé aux comptes connectés</span>
                <span><i class="fa-solid fa-database"></i> Provenance enregistrée en base</span>
            </div> -->
            <!-- <div class="contact-hero-inline-note">
                <i class="fa-solid fa-circle-info"></i>
                <p>Choisissez une action rapide à gauche, puis utilisez le formulaire pour garder une trace du message dans votre espace.</p>
            </div> -->
        </div>

        <div class="contact-page-hero__aside">
            <div class="contact-highlight-card">
                <strong>Besoin d'une réponse rapide ?</strong>
                <p>Choisissez le canal qui vous convient, puis laissez un message si nécessaire.</p>
                <a href="<?= $actor['is_admin'] ? '/messages-admin' : '/mes-messages' ?>" class="account-secondary-btn">Voir les messages</a>
            </div>
        </div>
    </div>

    <div class="contact-page-grid">
        <div class="contact-panel">
            <div class="contact-panel__head">
                <span class="contact-page-kicker">Actions rapides</span>
                <h2>Joindre AbsoluHub</h2>
            </div>

            <div class="contact-actions-grid">
                <a href="tel:+243813689713" class="contact-action-card">
                    <div class="contact-action-card__icon">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                    <div class="contact-action-card__content">
                        <strong>Appeler maintenant</strong>
                        <span><?= htmlspecialchars($phone_display) ?></span>
                        <small>Lance un appel direct depuis l'appareil.</small>
                    </div>
                </a>

                <a href="whatsapp://send?phone=<?= htmlspecialchars($phone_raw) ?>" class="contact-action-card">
                    <div class="contact-action-card__icon">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <div class="contact-action-card__content">
                        <strong>WhatsApp sur l'application</strong>
                        <span>Ouvrir directement l'application WhatsApp</span>
                        <!-- <small>Pratique sur mobile si WhatsApp est déjà installé.</small> -->
                    </div>
                </a>

                <a href="https://wa.me/<?= htmlspecialchars($phone_raw) ?>" class="contact-action-card" target="_blank" rel="noopener noreferrer">
                    <div class="contact-action-card__icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <div class="contact-action-card__content">
                        <strong>Conversation WhatsApp</strong>
                        <span>Accéder à la conversation avec <?= htmlspecialchars($phone_display) ?></span>
                        <!-- <small>Fonctionne aussi via le navigateur avec `wa.me`.</small> -->
                    </div>
                </a>
            </div>
        </div>

        <div class="contact-panel">
            <div class="contact-panel__head">
                <span class="contact-page-kicker">Message sur le site</span>
                <h2>Laisser un message</h2>
            </div>

            <div class="contact-identity-box">
                <div>
                    <span>Compte utilisé</span>
                    <strong><?= htmlspecialchars((string) $actor['user']['user_name']) ?></strong>
                </div>
                <div>
                    <span>Adresse e-mail</span>
                    <strong><?= htmlspecialchars((string) $actor['user']['email']) ?></strong>
                </div>
                <div>
                    <span>Statut de session</span>
                    <strong><?= $actor['is_admin'] ? 'Administrateur connecté' : 'Utilisateur connecté' ?></strong>
                </div>
            </div>

            <!-- <div class="contact-form-intro">
                <strong>Message enregistré dans votre espace</strong>
                <p>Après envoi, le formulaire se vide automatiquement et la conversation reste consultable dans la page dédiée.</p>
            </div> -->

            <?php if (!$schema_ready) { ?>
                <div class="contact-warning-box">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Le module de messages n'est pas encore prêt.</strong>
                        <p>Exécutez le SQL ajouté dans le README pour créer les tables nécessaires, puis revenez sur cette page.</p>
                    </div>
                </div>
            <?php } else { ?>
                <form id="contact-message-form" class="contact-form-card" autocomplete="off">
                    <input type="hidden" name="provenance_page" value="/contact">
                    <input type="hidden" name="provenance_label" value="Formulaire de contact">
                    <input type="hidden" id="contact_redirect_url" value="<?= $actor['is_admin'] ? '/conversation-admin?id=' : '/ma-conversation?id=' ?>">

                    <div class="contact-form-field">
                        <label for="contact_message">Votre message</label>
                        <textarea
                            id="contact_message"
                            name="message"
                            rows="8"
                            placeholder="Écrivez votre message ici..."
                        ></textarea>
                    </div>

                    <div class="contact-form-actions">
                        <button type="submit" class="account-primary-btn">Envoyer le message</button>
                        <?php if (!empty($conversation['id'])) { ?>
                            <a href="<?= $actor['is_admin'] ? '/conversation-admin?id=' . (int) $conversation['id'] : '/ma-conversation?id=' . (int) $conversation['id'] ?>" class="account-secondary-btn">Ouvrir la conversation</a>
                        <?php } ?>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</section>

<script src="<?php echo ASSET; ?>js/contact.js?<?= filemtime(ROOT . "asset/js/contact.js") ?>"></script>

<?php include_once VIEW . "/composant/footer.php"; ?>
