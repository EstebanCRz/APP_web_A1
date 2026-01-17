<?php
// Fichier de test pour vérifier la configuration PHP IMAP et l'accès Gmail

echo "<h2>Test PHP IMAP et connexion Gmail</h2>";

// Vérification extension IMAP
if (function_exists('imap_open')) {
    echo "<p style='color:green;'>L'extension PHP IMAP est activée ✔️</p>";
} else {
    echo "<p style='color:red;'>L'extension PHP IMAP n'est PAS activée ❌</p>";
}

// Test connexion Gmail (remplacer par mot de passe d'application pour un vrai test)
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'amigoocontact@gmail.com';
$password = 'Azerty123,';

$inbox = @imap_open($hostname, $username, $password);
if ($inbox) {
    echo "<p style='color:green;'>Connexion à Gmail réussie ✔️</p>";
    imap_close($inbox);
} else {
    echo "<p style='color:red;'>Échec de connexion à Gmail ❌<br>" . imap_last_error() . "</p>";
}
