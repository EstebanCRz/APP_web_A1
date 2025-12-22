<?php
/**
 * Configuration générale de l'application
 */

// Définir les constantes de chemin
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_PATH', BASE_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMAGES_PATH', ASSETS_PATH . '/images');

// URL de base (à adapter selon votre environnement)
define('BASE_URL', 'http://localhost/APP_web_A1');
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');

// Configuration de la base de données MAMP
define('DB_HOST', 'localhost:3306'); // Port MySQL MAMP
define('DB_NAME', 'amigo_db');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Mot de passe MAMP

// Configuration SMTP pour l'envoi d'emails
define('SMTP_HOST', 'smtp.amigo.fr'); // Serveur SMTP de votre hébergeur
define('SMTP_PORT', 587); // 587 pour TLS, 465 pour SSL
define('SMTP_USER', 'noreply@amigo.fr'); // Votre adresse email
define('SMTP_PASS', 'cb2e31538c951f4539aeb88bddd46151');
define('SMTP_FROM_EMAIL', 'noreply@amigo.fr');
define('SMTP_FROM_NAME', 'AmiGo');

// Fonction de connexion à la base de données
function getDB() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
        return $pdo;
    } catch(PDOException $e) {
        die('Erreur de connexion à la base de données: ' . $e->getMessage());
    }
}

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Application name
define('SITE_NAME', 'AmiGo');
