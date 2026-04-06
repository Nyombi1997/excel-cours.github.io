<?php
    include_once FONCTION . 'contact_context.php';

    $actor = contact_get_connected_actor($bdd);
    if (!$actor || empty($actor['is_admin'])) {
        header("location: /connexion");
        exit;
    }

    $schema_ready = contact_schema_ready($bdd);
    $conversations = $schema_ready ? contact_get_conversation_list($bdd, $actor) : [];
?>

<section class="message-page-shell">
    <div class="message-page-hero">
        <div class="contact-page-hero__content">
            <span class="contact-page-kicker">Administration</span>
            <h1>Messages reçus</h1>
            <p>Chaque conversation est séparée de la page admin principale pour garder un espace dédié au suivi.</p>
            <div class="message-page-actions">
                <a href="/admin" class="account-secondary-btn">Retour à l'admin</a>
                <a href="/contact" class="account-primary-btn">Ouvrir la page contact</a>
            </div>
        </div>
    </div>

    <?php if (!$schema_ready) { ?>
        <div class="contact-warning-box">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Le module de messages n'est pas encore configuré.</strong>
                <p>Exécutez d'abord le SQL fourni dans le README.</p>
            </div>
        </div>
    <?php } else if (empty($conversations)) { ?>
        <div class="message-empty-card">
            <strong>Aucun message reçu pour le moment.</strong>
            <p>Les nouvelles conversations apparaîtront ici automatiquement.</p>
        </div>
    <?php } else { ?>
        <div class="message-list-grid">
            <?php foreach ($conversations as $conversation) { ?>
                <a href="/conversation-admin?id=<?= (int) $conversation['id'] ?>" class="message-list-card">
                    <div class="message-list-card__head">
                        <strong><?= htmlspecialchars((string) $conversation['user_name']) ?></strong>
                        <span><?= !empty($conversation['last_message_date']) ? htmlspecialchars(date_fr_short((string) $conversation['last_message_date'])) : htmlspecialchars(date_fr_short((string) $conversation['date_ajout'])) ?></span>
                    </div>
                    <p><?= htmlspecialchars(mb_strimwidth((string) $conversation['last_message'], 0, 160, '...')) ?></p>
                    <div class="message-list-card__meta">
                        <span><?= (int) $conversation['total_messages'] ?> message(s)</span>
                        <span><?= htmlspecialchars((string) $conversation['user_email']) ?></span>
                    </div>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</section>
