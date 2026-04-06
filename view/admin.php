<?php
    // DÃ©marrer la session uniquement si elle n'est pas dÃ©jÃ  active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    /* si l'utilisateur est connecter */
    if(!isset($_SESSION['admin_cours_excel_987654321']))
    {
        header("location: connexion");
    }

?>
<!-- container admin -->
<div class="container_admin">
    <div class="titre_admin">
        Admin
    </div>
    <a href="/gestion-cours" class="link_admin">Gestion cours</a>
    <a href="/gestion-utilisateurs" class="link_admin">Gestion utilisateurs</a>
    <a href="/messages-admin" class="link_admin">Messages reçus</a>
    <a href="/deconnexion" class="link_admin">Se dÃ©connecter</a>
</div>
