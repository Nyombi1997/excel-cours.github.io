<?php
    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Nettoyer explicitement les deux types de session utilisés par l'application.
    unset($_SESSION['use_cours_excel_987654321']);
    unset($_SESSION['admin_cours_excel_987654321']);

    // Vider le tableau de session pour éviter qu'une donnée résiduelle reste active.
    $_SESSION = [];

    // Supprimer le cookie de session courant si PHP en utilise un.
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Détruire complètement la session en cours avant de rediriger l'utilisateur.
    session_destroy();

    header('Location: accueil');
    exit();
?>
