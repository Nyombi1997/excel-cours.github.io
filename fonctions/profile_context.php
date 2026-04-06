<?php
    /*
        Helpers de contexte pour l'espace profil.
        On centralise ici la logique de session et de chargement utilisateur
        pour garder la même base entre la vue et les endpoints AJAX.
    */

    if (!function_exists('profile_start_session_if_needed')) {
        function profile_start_session_if_needed()
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
    }

    if (!function_exists('profile_connected_user_unique_id')) {
        function profile_connected_user_unique_id()
        {
            profile_start_session_if_needed();
            return isset($_SESSION['use_cours_excel_987654321']) ? $_SESSION['use_cours_excel_987654321'] : null;
        }
    }

    if (!function_exists('profile_connected_admin_unique_id')) {
        function profile_connected_admin_unique_id()
        {
            profile_start_session_if_needed();
            return isset($_SESSION['admin_cours_excel_987654321']) ? $_SESSION['admin_cours_excel_987654321'] : null;
        }
    }

    if (!function_exists('profile_find_user_by_unique_id')) {
        function profile_find_user_by_unique_id($bdd, $unique_id)
        {
            if ($unique_id === null || $unique_id === '') {
                return null;
            }

            $stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE unique_id = ? LIMIT 1");
            $stmt->execute([$unique_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $user ? $user : null;
        }
    }

    if (!function_exists('profile_get_viewed_user')) {
        function profile_get_viewed_user($bdd)
        {
            if (isset($GLOBALS['user']) && !empty($GLOBALS['user']['slug'])) {
                $stmt = $bdd->prepare("SELECT * FROM utilisateur WHERE slug = ? LIMIT 1");
                $stmt->execute([$GLOBALS['user']['slug']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                return $user ? $user : null;
            }

            return profile_find_user_by_unique_id($bdd, profile_connected_user_unique_id());
        }
    }

    if (!function_exists('profile_get_avatar_url')) {
        function profile_get_avatar_url($user)
        {
            if (!is_array($user) || empty($user['profile'])) {
                return ASSET . 'images/profile/default.jpg';
            }

            return ASSET . 'images/profile/' . $user['profile'];
        }
    }

    if (!function_exists('profile_can_edit_user')) {
        function profile_can_edit_user($user)
        {
            if (!is_array($user) || empty($user['unique_id'])) {
                return false;
            }

            $connected_unique_id = profile_connected_user_unique_id();
            $connected_admin_id = profile_connected_admin_unique_id();

            if ($connected_admin_id) {
                return false;
            }

            return $connected_unique_id !== null && $connected_unique_id === $user['unique_id'];
        }
    }
?>
