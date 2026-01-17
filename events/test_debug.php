<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Test de base PHP

echo "<h2>Test PHP OK</h2>";

// Test connexion BDD
try {
    require_once __DIR__ . '/../includes/config.php';
    if (function_exists('getDB')) {
        $pdo = getDB();
        echo '<p style="color:green">Connexion BDD OK</p>';
    } else {
        echo '<p style="color:orange">Fonction getDB() non trouvée</p>';
    }
} catch (Throwable $e) {
    echo '<p style="color:red">Erreur BDD : ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Test include language.php
try {
    require_once __DIR__ . '/../includes/language.php';
    echo '<p style="color:green">language.php OK</p>';
} catch (Throwable $e) {
    echo '<p style="color:red">Erreur language.php : ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Test include activities_functions.php
try {
    require_once __DIR__ . '/../includes/activities_functions.php';
    echo '<p style="color:green">activities_functions.php OK</p>';
} catch (Throwable $e) {
    echo '<p style="color:red">Erreur activities_functions.php : ' . htmlspecialchars($e->getMessage()) . '</p>';
}

?>