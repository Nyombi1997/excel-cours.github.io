<?php
    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    unset($_SESSION['use_cours_excel_987654321']);
    header('Location: accueil');
    exit();
?>