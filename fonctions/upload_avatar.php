<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['image'])) {
        $img = str_replace('data:image/png;base64,', '', $data['image']);
        $img = str_replace(' ', '+', $img);
        $fileData = base64_decode($img);
        $unique_id = uniqid('user_', true);
        $img = $unique_id.'.png';
        $update_data = [
            'profile' => $img
        ];
        update_bdd($bdd, "utilisateur", $update_data, "unique_id = '".$_SESSION['use_cours_excel_987654321']."'");
        // Sauvegarder l'image (Utilisez un ID utilisateur unique ici)
        $results = [
            "result" => "ok",
            "msg" => ""
        ];
        file_put_contents('../asset/images/profile/'.$img, $fileData);
        echo "Succès";
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>