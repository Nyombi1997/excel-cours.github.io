<?php
    include_once FONCTION . 'contact_context.php';

    $actor = contact_get_connected_actor($bdd);
    if (!$actor || !empty($actor['is_admin'])) {
        header("location: /connexion");
        exit;
    }

    $schema_ready = contact_schema_ready($bdd);
    $conversations = $schema_ready ? contact_get_conversation_list($bdd, $actor) : [];
?>

<section class="message-page-shell">
    <div class="message-page-hero">
        <div class="contact-page-hero__content">
            <span class="contact-page-kicker">Espace compte</span>
            <h1>Mes messages</h1>
            <p>Retrouvez ici les messages envoyés depuis le site et ouvrez votre conversation dédiée.</p>
            <div class="message-page-actions">
                <a href="/contact" class="account-primary-btn">Laisser un nouveau message</a>
                <a href="/compte" class="account-secondary-btn">Retour au compte</a>
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
            <strong>Aucune conversation pour le moment.</strong>
            <p>Quand vous enverrez un premier message depuis la page contact, il apparaîtra ici.</p>
        </div>
    <?php } else { ?>
        <div class="message-list-grid">
            <?php foreach ($conversations as $conversation) { ?>
                <a href="/ma-conversation?id=<?= (int) $conversation['id'] ?>" class="message-list-card">
                    <div class="message-list-card__head">
                        <strong><?= htmlspecialchars((string) $conversation['user_name']) ?></strong>
                        <span><?= !empty($conversation['last_message_date']) ? htmlspecialchars(date_fr_short((string) $conversation['last_message_date'])) : htmlspecialchars(date_fr_short((string) $conversation['date_ajout'])) ?></span>
                    </div>
                    <p><?= htmlspecialchars(mb_strimwidth((string) $conversation['last_message'], 0, 140, '...')) ?></p>
                    <div class="message-list-card__meta">
                        <span><?= (int) $conversation['total_messages'] ?> message(s)</span>
                        <span><?= htmlspecialchars((string) $conversation['provenance']) ?></span>
                    </div>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</section>
