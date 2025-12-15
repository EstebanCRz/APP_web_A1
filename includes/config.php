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

// Configuration de la base de données (à compléter)
define('DB_HOST', 'localhost');
define('DB_NAME', 'amigo');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration de session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en HTTPS

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
