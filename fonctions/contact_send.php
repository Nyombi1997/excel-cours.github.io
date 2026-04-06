<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "profile_context.php";
    include_once "contact_context.php";
    header('Content-Type: application/json; charset=utf-8');

    $results = [
        "result" => "error",
        "msg" => "L'envoi du message a échoué."
    ];

    $actor = contact_get_connected_actor($bdd);
    if (!$actor) {
        $results["msg"] = "Vous devez être connecté pour envoyer un message.";
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (!contact_schema_ready($bdd)) {
        $results["msg"] = "La base de données du module contact n'est pas encore prête. Exécutez le SQL fourni dans le README.";
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $message = trim(html_entity_decode(filter_var(isset($_POST['message']) ? $_POST['message'] : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $provenance_page = trim(html_entity_decode(filter_var(isset($_POST['provenance_page']) ? $_POST['provenance_page'] : '/contact', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $provenance_label = trim(html_entity_decode(filter_var(isset($_POST['provenance_label']) ? $_POST['provenance_label'] : 'Formulaire de contact', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

    if ($message === '') {
        $results["msg"] = "Merci d'écrire un message avant l'envoi.";
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (mb_strlen($message) < 3) {
        $results["msg"] = "Le message doit contenir au moins 3 caractères.";
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    try {
        /*
            Une conversation est liée au compte connecté.
            Si elle n'existe pas encore, on la crée avant d'ajouter le nouveau message.
        */
        $conversation = contact_find_conversation_by_user_unique_id($bdd, (string) $actor['user']['unique_id']);

        if (!$conversation) {
            $insert_conversation = $bdd->prepare("
                INSERT INTO contact_conversations (
                    user_unique_id,
                    user_name,
                    user_email,
                    provenance,
                    last_message_at,
                    last_sender_type
                ) VALUES (?, ?, ?, ?, NOW(), ?)
            ");
            $insert_conversation->execute([
                (string) $actor['user']['unique_id'],
                (string) $actor['user']['user_name'],
                (string) $actor['user']['email'],
                $provenance_label,
                !empty($actor['is_admin']) ? 'admin' : 'user'
            ]);

            $conversation_id = (int) $bdd->lastInsertId();
        } else {
            $conversation_id = (int) $conversation['id'];
        }

        /*
            On conserve la provenance technique du message pour faciliter
            le support et le troubleshooting côté admin.
        */
        $insert_message = $bdd->prepare("
            INSERT INTO contact_messages (
                conversation_id,
                sender_unique_id,
                sender_type,
                sender_name,
                sender_email,
                provenance_page,
                provenance_label,
                provenance_ip,
                provenance_user_agent,
                message
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert_message->execute([
            $conversation_id,
            (string) $actor['user']['unique_id'],
            !empty($actor['is_admin']) ? 'admin' : 'user',
            (string) $actor['user']['user_name'],
            (string) $actor['user']['email'],
            $provenance_page !== '' ? $provenance_page : '/contact',
            $provenance_label !== '' ? $provenance_label : 'Formulaire de contact',
            isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '',
            isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 500) : '',
            $message
        ]);

        $update_conversation = $bdd->prepare("
            UPDATE contact_conversations
            SET
                user_name = ?,
                user_email = ?,
                provenance = ?,
                last_message_at = NOW(),
                last_sender_type = ?
            WHERE id = ?
        ");
        $update_conversation->execute([
            (string) $actor['user']['user_name'],
            (string) $actor['user']['email'],
            $provenance_label !== '' ? $provenance_label : 'Formulaire de contact',
            !empty($actor['is_admin']) ? 'admin' : 'user',
            $conversation_id
        ]);

        $results = [
            "result" => "ok",
            "msg" => "Votre message a bien été envoyé.",
            "conversation_id" => $conversation_id
        ];
    } catch (Exception $e) {
        $results["msg"] = "Une erreur est survenue pendant l'enregistrement du message.";
    }

    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
