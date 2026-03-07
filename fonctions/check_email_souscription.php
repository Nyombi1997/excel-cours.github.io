<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email_address = select_bdd($bdd, "souscription_news_letter", $where = 'email = "'.$email.'"', $limit = null, $offset = 0, $order = null, $random = false);
    if(count($email_address)!=0)
    {
        $results = [
            "result" => "ok",
            "msg" => "Vous êtes déjà inscrit!"
        ];
    }
    else
    {
        $insert_data = [
            "email" => $email 
        ];
        insert_bdd($bdd, "souscription_news_letter", $insert_data);
        $results = [
            "result" => "ok",
            "msg" => "Votre inscription est réussit!"
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>