<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";

    $titre = html_entity_decode(filter_var($_POST['titre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    // Génération du nom fichier de base
    $separator = "_";
    $titre_nom = strtolower($titre);
    $titre_nom = iconv('UTF-8', 'ASCII//TRANSLIT', $titre_nom);
    $titre_nom = preg_replace('/[^a-z0-9]+/i', $separator, $titre_nom);
    $titre_nom = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $titre_nom);
    $titre_nom = trim($titre_nom, $separator);
    $titre_nom = str_replace(" ", "_", $titre_nom);

    $description = html_entity_decode(filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $video = "";
    $fichier = "";
    if (!isset($_FILES['video'])) {
        http_response_code(400);
        echo "Aucun fichier";
        exit;
    }

    $uploadDir = "../asset/videos/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $extension = basename($_FILES['video']['name']);
    $extension = explode('.',$extension);
    $extension = $extension[count($extension)-1];
    $filename = "video_".$titre_nom."_".uniqid().".". $extension;
    $target = $uploadDir . $filename;
    $video = $filename;

    if (move_uploaded_file($_FILES['video']['tmp_name'], $target)) {
        echo "OK";
    } else {
        http_response_code(500);
        echo "Erreur upload";
    }
    /* preparer le fichier si existe */
    if (isset($_FILES['fichier'])) {

        $uploadDir = "../asset/fichier/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $extension = basename($_FILES['fichier']['name']);
        $extension = explode('.',$extension);
        $extension = $extension[count($extension)-1];
        $filename = "fichier_".$titre_nom."_".uniqid().".". $extension;
        $target = $uploadDir . $filename;
        $fichier = $filename;

        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $target)) {
        } else {
            http_response_code(500);
            echo "Erreur upload";
        }
    }
    $cours = select_bdd($bdd, "cours", $where = null, $limit = null, $offset = 0, $order = 'position', $random = false);
    $position = intval($cours[count($cours)]['position']) + 1;
    $insert_data = [
        "quiz" => 0,
        "video" => 1,
        "titre" => $titre,
        "lien_video" => $video,
        "fichier" => $fichier,
        "description" => $description,
        "position" => $position,
    ];
    insert_bdd($bdd, "cours", $insert_data);
?>