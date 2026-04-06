<?php
    include_once __DIR__ . '/profile_context.php';

    if (!function_exists('contact_table_exists')) {
        function contact_table_exists($bdd, $table_name)
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ");
            $stmt->execute([$table_name]);

            return (int) $stmt->fetchColumn() > 0;
        }
    }

    if (!function_exists('contact_schema_ready')) {
        function contact_schema_ready($bdd)
        {
            return contact_table_exists($bdd, 'contact_conversations')
                && contact_table_exists($bdd, 'contact_messages');
        }
    }

    if (!function_exists('contact_get_connected_actor')) {
        function contact_get_connected_actor($bdd)
        {
            profile_start_session_if_needed();

            $admin_unique_id = profile_connected_admin_unique_id();
            if ($admin_unique_id) {
                $user = profile_find_user_by_unique_id($bdd, $admin_unique_id);
                if ($user) {
                    return [
                        'is_admin' => true,
                        'session_type' => 'admin',
                        'user' => $user
                    ];
                }
            }

            $user_unique_id = profile_connected_user_unique_id();
            if ($user_unique_id) {
                $user = profile_find_user_by_unique_id($bdd, $user_unique_id);
                if ($user) {
                    return [
                        'is_admin' => false,
                        'session_type' => 'user',
                        'user' => $user
                    ];
                }
            }

            return null;
        }
    }

    if (!function_exists('contact_find_conversation_by_user_unique_id')) {
        function contact_find_conversation_by_user_unique_id($bdd, $user_unique_id)
        {
            if ($user_unique_id === '') {
                return null;
            }

            $stmt = $bdd->prepare("
                SELECT *
                FROM contact_conversations
                WHERE user_unique_id = ?
                LIMIT 1
            ");
            $stmt->execute([$user_unique_id]);

            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

            return $conversation ? $conversation : null;
        }
    }

    if (!function_exists('contact_get_conversation_list')) {
        function contact_get_conversation_list($bdd, $actor)
        {
            if (!contact_schema_ready($bdd) || !is_array($actor)) {
                return [];
            }

            if (!empty($actor['is_admin'])) {
                $stmt = $bdd->query("
                    SELECT
                        c.*,
                        (SELECT COUNT(*) FROM contact_messages m WHERE m.conversation_id = c.id) AS total_messages,
                        (SELECT m.message FROM contact_messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
                        (SELECT m.date_ajout FROM contact_messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message_date
                    FROM contact_conversations c
                    ORDER BY COALESCE(last_message_at, date_ajout) DESC, id DESC
                ");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $stmt = $bdd->prepare("
                SELECT
                    c.*,
                    (SELECT COUNT(*) FROM contact_messages m WHERE m.conversation_id = c.id) AS total_messages,
                    (SELECT m.message FROM contact_messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
                    (SELECT m.date_ajout FROM contact_messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message_date
                FROM contact_conversations c
                WHERE c.user_unique_id = ?
                ORDER BY COALESCE(last_message_at, date_ajout) DESC, id DESC
            ");
            $stmt->execute([(string) $actor['user']['unique_id']]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    if (!function_exists('contact_get_conversation_details')) {
        function contact_get_conversation_details($bdd, $conversation_id, $actor)
        {
            if (!contact_schema_ready($bdd) || !is_numeric($conversation_id) || !is_array($actor)) {
                return null;
            }

            if (!empty($actor['is_admin'])) {
                $stmt = $bdd->prepare("SELECT * FROM contact_conversations WHERE id = ? LIMIT 1");
                $stmt->execute([(int) $conversation_id]);
            } else {
                $stmt = $bdd->prepare("
                    SELECT *
                    FROM contact_conversations
                    WHERE id = ? AND user_unique_id = ?
                    LIMIT 1
                ");
                $stmt->execute([
                    (int) $conversation_id,
                    (string) $actor['user']['unique_id']
                ]);
            }

            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$conversation) {
                return null;
            }

            $message_stmt = $bdd->prepare("
                SELECT *
                FROM contact_messages
                WHERE conversation_id = ?
                ORDER BY id ASC
            ");
            $message_stmt->execute([(int) $conversation_id]);
            $messages = $message_stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'conversation' => $conversation,
                'messages' => $messages
            ];
        }
    }
?>
