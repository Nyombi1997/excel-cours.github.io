<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $unique_id = uniqid('user_', true);
    // Hachage du mot de passe
    $mdp = password_hash(
        $_POST['mdp'],
        PASSWORD_DEFAULT
    );

    $verif_email = only_select("utilisateur", "email = '$email'", $order = null, $limit = null);
    
    if($verif_email && password_verify($_POST['mdp'], $verif_email['mdp']) && $verif_email['admin']==1)
    {
        $results = [
            "result" => "admin",
            "msg" => ""
        ];
        $_SESSION['admin_cours_excel_987654321'] = $verif_email['unique_id'];
    }    
    else if($verif_email && password_verify($_POST['mdp'], $verif_email['mdp']))
    {
        $results = [
            "result" => "ok",
            "msg" => ""
        ];
        $_SESSION['use_cours_excel_987654321'] = $verif_email['unique_id'];
    }
    else
    {
        $results = [
            "result" => "error",
            "msg" => "L'adresse email ou le mot de passe est incorrect"
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>