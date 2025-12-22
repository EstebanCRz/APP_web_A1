<?php
/**
 * Autoloader simple pour PHPMailer (sans Composer)
 */

spl_autoload_register(function ($class) {
    // Namespace de base de PHPMailer
    $prefix = 'PHPMailer\\PHPMailer\\';
    
    // Répertoire de base pour PHPMailer
    $base_dir = __DIR__ . '/vendor/phpmailer/src/';
    
    // Vérifier si la classe utilise le namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Obtenir le nom de fichier relatif
    $relative_class = substr($class, $len);
    
    // Remplacer le namespace par la structure de dossiers
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si le fichier existe, l'inclure
    if (file_exists($file)) {
        require $file;
    }
});
