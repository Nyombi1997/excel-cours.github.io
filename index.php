<?php

/* include config */
include_once '_config.php';

MyAutoload::start();

// Si 'r' n'est pas défini, on met 'home' comme route par défaut
$request = trim($_GET['r'] ?? '', '/');
if ($request === '') {
    $request = 'accueil';
}

// Délègue toute la logique au routeur
$routeur = new Routeur($request);
$routeur->renderController();

?>