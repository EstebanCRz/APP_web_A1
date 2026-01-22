<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Script démarre<br>";

session_start();
require_once '../includes/session.php';
require_once '../includes/config.php';

echo "2. Avant header.php<br>";

$pageTitle = 'Test Header';
$pageDescription = 'Test';
$assetsDepth = 1;
$customCSS = ["../assets/css/style.css", "css/verify-email.css"];

echo "3. Variables définies<br>";
echo "4. Tentative d'inclusion header.php...<br>";

try {
    include '../includes/header.php';
    echo "5. Header.php chargé avec succès<br>";
} catch (Exception $e) {
    echo "ERREUR header.php: " . $e->getMessage() . "<br>";
}

echo "6. Après header - Test terminé";
?>
