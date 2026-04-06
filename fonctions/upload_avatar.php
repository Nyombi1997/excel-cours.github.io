<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "profile_context.php";
    header('Content-Type: application/json; charset=utf-8');

    profile_start_session_if_needed();

    $results = [
        "result" => "error",
        "msg" => "La mise à jour de la photo de profil a échoué."
    ];

    $connected_unique_id = profile_connected_user_unique_id();
    if (!$connected_unique_id) {
        $results = [
            "result" => "error",
            "msg" => "Vous devez être connecté pour modifier votre photo de profil."
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

    $image_data = isset($_POST['image']) ? trim($_POST['image']) : '';
    if ($image_data === '') {
        $results = [
            "result" => "error",
            "msg" => "Aucune image n'a été transmise."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $image_data, $matches)) {
        $results = [
            "result" => "error",
            "msg" => "Le format de l'image est invalide."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $extension_map = [
        'jpeg' => 'jpg',
        'jpg' => 'jpg',
        'png' => 'png',
        'webp' => 'webp'
    ];

    $extension = $extension_map[strtolower($matches[1])];
    $base64_image = preg_replace('/^data:image\/(png|jpeg|jpg|webp);base64,/', '', $image_data);
    $base64_image = str_replace(' ', '+', $base64_image);
    $file_data = base64_decode($base64_image, true);

    if ($file_data === false) {
        $results = [
            "result" => "error",
            "msg" => "Impossible de lire l'image envoyée."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $new_file_name = uniqid('profile_', true) . '.' . $extension;
    $target_path = '../asset/images/profile/' . $new_file_name;

    /*
        On enregistre d'abord le fichier sur le disque.
        Si l'écriture échoue, on retourne une erreur claire pour faciliter le dépannage.
    */
    if (!file_put_contents($target_path, $file_data)) {
        $results = [
            "result" => "error",
            "msg" => "Le fichier n'a pas pu être enregistré sur le serveur."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $update_ok = update_bdd($bdd, "utilisateur", ['profile' => $new_file_name], "unique_id = '" . addslashes($connected_unique_id) . "'");
    if (!$update_ok) {
        @unlink($target_path);
        $results = [
            "result" => "error",
            "msg" => "La base de données n'a pas pu être mise à jour."
        ];
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if (!empty($user['profile']) && $user['profile'] !== $new_file_name) {
        $old_path = '../asset/images/profile/' . $user['profile'];
        if (is_file($old_path)) {
            @unlink($old_path);
        }
    }

    $results = [
        "result" => "ok",
        "msg" => "Votre photo de profil a été mise à jour.",
        "image_url" => ASSET . 'images/profile/' . $new_file_name
    ];

    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
