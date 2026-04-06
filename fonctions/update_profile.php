<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "profile_context.php";
    header('Content-Type: application/json; charset=utf-8');

    profile_start_session_if_needed();

    $results = [
        "result" => "error",
        "msg" => "La mise à jour du profil a échoué."
    ];

    $connected_unique_id = profile_connected_user_unique_id();
    if (!$connected_unique_id || profile_connected_admin_unique_id()) {
        $results = [
            "result" => "error",
            "msg" => "Vous n'êtes pas autorisé à modifier ce profil."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $user = profile_find_user_by_unique_id($bdd, $connected_unique_id);
    if (!$user) {
        $results = [
            "result" => "error",
            "msg" => "Votre compte est introuvable."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $user_name = trim(html_entity_decode(filter_var(isset($_POST['user_name']) ? $_POST['user_name'] : '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $email = trim(html_entity_decode(filter_var(isset($_POST['email']) ? $_POST['email'] : '', FILTER_SANITIZE_EMAIL)));
    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if ($user_name === '') {
        $results = [
            "result" => "error",
            "msg" => "Le nom d'utilisateur est obligatoire."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $results = [
            "result" => "error",
            "msg" => "L'adresse e-mail est invalide."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /*
        Vérification d'unicité en excluant l'utilisateur courant.
        On utilise des requêtes préparées pour éviter les problèmes liés
        aux apostrophes et aux caractères spéciaux.
    */
    $stmt = $bdd->prepare("SELECT id FROM utilisateur WHERE user_name = ? AND unique_id != ? LIMIT 1");
    $stmt->execute([$user_name, $connected_unique_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $results = [
            "result" => "error",
            "msg" => "Ce nom d'utilisateur est déjà utilisé."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $stmt = $bdd->prepare("SELECT id FROM utilisateur WHERE email = ? AND unique_id != ? LIMIT 1");
    $stmt->execute([$email, $connected_unique_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $results = [
            "result" => "error",
            "msg" => "Cette adresse e-mail est déjà utilisée."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $password_change_requested = ($old_password !== '' || $new_password !== '' || $confirm_password !== '');
    $update_data = [
        'user_name' => $user_name,
        'email' => $email
    ];

    if ($password_change_requested) {
        if ($old_password === '' || $new_password === '' || $confirm_password === '') {
            $results = [
                "result" => "error",
                "msg" => "Merci de compléter tous les champs de mot de passe."
            ];
            echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if (!password_verify($old_password, $user['mdp'])) {
            $results = [
                "result" => "error",
                "msg" => "L'ancien mot de passe est incorrect."
            ];
            echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if (strlen($new_password) < 6) {
            $results = [
                "result" => "error",
                "msg" => "Le nouveau mot de passe doit contenir au moins 6 caractères."
            ];
            echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if ($new_password !== $confirm_password) {
            $results = [
                "result" => "error",
                "msg" => "La confirmation du mot de passe ne correspond pas."
            ];
            echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $update_data['mdp'] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    $update_ok = update_bdd($bdd, "utilisateur", $update_data, "unique_id = '" . addslashes($connected_unique_id) . "'");
    if (!$update_ok) {
        $results = [
            "result" => "error",
            "msg" => "La base de données n'a pas pu être mise à jour."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (!isset($user['slug']) || trim($user['slug']) === '') {
        $new_slug = generateSlug($user_name);
        update_bdd($bdd, "utilisateur", ['slug' => $new_slug], "unique_id = '" . addslashes($connected_unique_id) . "'");
    }

    $results = [
        "result" => "ok",
        "msg" => "Vos informations personnelles ont été mises à jour.",
        "user_name" => $user_name,
        "email" => $email
    ];

    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
