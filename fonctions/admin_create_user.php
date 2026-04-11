<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['admin_cours_excel_987654321'])) {
        echo json_encode([
            "result" => "error",
            "msg" => "Votre session administrateur n'est pas active."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $user_name = trim((string) html_entity_decode(filter_var($_POST['user_name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $email = trim((string) html_entity_decode(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL)));
    $plain_password = trim((string) ($_POST['password'] ?? ''));

    if ($user_name === '' || $email === '' || $plain_password === '') {
        echo json_encode([
            "result" => "error",
            "msg" => "Merci de remplir le nom, l'adresse e-mail et le mot de passe."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "result" => "error",
            "msg" => "L'adresse e-mail saisie n'est pas valide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (mb_strlen($plain_password) < 8) {
        echo json_encode([
            "result" => "error",
            "msg" => "Le mot de passe doit contenir au moins 8 caractères."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $verif_pseudo = only_select("utilisateur", "user_name = " . $bdd->quote($user_name), $order = null, $limit = null);
    $verif_email = only_select("utilisateur", "email = " . $bdd->quote($email), $order = null, $limit = null);

    if ($verif_pseudo) {
        echo json_encode([
            "result" => "error",
            "msg" => "Le nom d'utilisateur est déjà utilisé."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if ($verif_email) {
        echo json_encode([
            "result" => "error",
            "msg" => "L'adresse e-mail est déjà utilisée."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $unique_id = uniqid('user_', true);
    $default_profile = 'default.jpg';
    $password_hash = password_hash($plain_password, PASSWORD_DEFAULT);
    $slug = generateSlug($user_name);

    $insert_data = [
        "profile" => $default_profile,
        "unique_id" => $unique_id,
        "user_name" => $user_name,
        "email" => $email,
        "mdp" => $password_hash,
        "admin" => 0,
        "slug" => $slug
    ];

    insert_bdd($bdd, "utilisateur", $insert_data);

    echo json_encode([
        "result" => "ok",
        "msg" => "L'utilisateur a été créé avec succès.",
        "user" => [
            "user_name" => $user_name,
            "email" => $email,
            "password" => $plain_password
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
