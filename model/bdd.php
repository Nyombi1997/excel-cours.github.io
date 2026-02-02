<?php
    $host = 'localhost'; // L'adresse de votre serveur de base de données
    $dbname = 'u577654037_excel'; // Le nom de votre base de données
    //$dbname = 'ekuke'; // Le nom de votre base de données
    $username = 'u577654037_excel'; // Le nom d'utilisateur de votre base de données
    //$username = 'root'; // Le nom d'utilisateur de votre base de données
    $password = 'Kinshasa@1997'; // Le mot de passe de votre base de données
    //$password = ''; // Le mot de passe de votre base de données

    try {
        $bdd = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $GLOBALS['bdd'] = $bdd;
    } catch (PDOException $e) {
        echo "Échec de la connexion : " . $e->getMessage();
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    } 
?>