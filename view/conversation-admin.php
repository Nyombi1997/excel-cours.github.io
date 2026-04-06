<?php
    include_once FONCTION . 'contact_context.php';

    $actor = contact_get_connected_actor($bdd);
    if (!$actor || empty($actor['is_admin'])) {
        header("location: /connexion");
        exit;
    }

    $schema_ready = contact_schema_ready($bdd);
    $conversation_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $conversation_bundle = ($schema_ready && $conversation_id > 0) ? contact_get_conversation_details($bdd, $conversation_id, $actor) : null;
?>

<section class="message-page-shell">
    <div class="message-page-hero">
        <div class="contact-page-hero__content">
            <span class="contact-page-kicker">Conversation admin</span>
            <h1>Détail de la conversation</h1>
            <p>Vous retrouvez ici les messages reçus, leur provenance et l'identité du compte émetteur.</p>
            <div class="message-page-actions">
                <a href="/messages-admin" class="account-secondary-btn">Retour aux messages reçus</a>
                <a href="/admin" class="account-primary-btn">Retour à l'admin</a>
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
    <?php } else if (!$conversation_bundle) { ?>
        <div class="message-empty-card">
            <strong>Conversation introuvable.</strong>
            <p>La conversation demandée n'existe pas.</p>
        </div>
    <?php } else { ?>
        <?php
            $conversation = $conversation_bundle['conversation'];
            $messages = $conversation_bundle['messages'];
        ?>
        <div class="conversation-head-card">
            <div>
                <span>Utilisateur</span>
                <strong><?= htmlspecialchars((string) $conversation['user_name']) ?></strong>
            </div>
            <div>
                <span>E-mail</span>
                <strong><?= htmlspecialchars((string) $conversation['user_email']) ?></strong>
            </div>
            <div>
                <span>Origine principale</span>
                <strong><?= htmlspecialchars((string) $conversation['provenance']) ?></strong>
            </div>
        </div>

        <div class="conversation-thread">
            <?php foreach ($messages as $message) { ?>
                <article class="conversation-bubble <?= $message['sender_type'] === 'admin' ? 'is-admin' : 'is-self' ?>">
                    <div class="conversation-bubble__meta">
                        <strong><?= htmlspecialchars((string) $message['sender_name']) ?></strong>
                        <span><?= htmlspecialchars(date_fr_short((string) $message['date_ajout'])) ?></span>
                    </div>
                    <p><?= nl2br(htmlspecialchars((string) $message['message'])) ?></p>
                    <div class="conversation-bubble__foot">
                        <span>Origine : <?= htmlspecialchars((string) $message['provenance_label']) ?></span>
                        <span>Page : <?= htmlspecialchars((string) $message['provenance_page']) ?></span>
                        <span>IP : <?= htmlspecialchars((string) $message['provenance_ip']) ?></span>
                    </div>
                </article>
            <?php } ?>
        </div>
    <?php } ?>
</section>
