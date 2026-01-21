<?php
/**
 * Charge les variables d'environnement depuis le fichier .env
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parser la ligne
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Supprimer les guillemets si présents
            $value = trim($value, '"\'');
            
            // Définir la variable d'environnement
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

/**
 * Récupère une variable d'environnement
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

// Charger le fichier .env
loadEnv(__DIR__ . '/../.env');
