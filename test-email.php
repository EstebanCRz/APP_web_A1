<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

// Test d'envoi d'email
echo "<h2>Test d'envoi d'email</h2>";

// Test 1: Email simple
echo "<h3>Test 1: Email simple</h3>";
$result1 = sendEmail(
    'test@example.com',
    'Test Email',
    '<h1>Ceci est un test</h1><p>Si vous recevez cet email, le système fonctionne!</p>'
);
echo $result1 ? "✅ Envoi réussi" : "❌ Échec de l'envoi";

echo "<br><br>";

// Test 2: Email de bienvenue
echo "<h3>Test 2: Email de bienvenue</h3>";
$result2 = sendWelcomeEmail('test@example.com', 'TestUser');
echo $result2 ? "✅ Envoi réussi" : "❌ Échec de l'envoi";

echo "<br><br>";

// Afficher la configuration SMTP (masquer le mot de passe)
echo "<h3>Configuration SMTP actuelle:</h3>";
echo "Serveur: " . SMTP_HOST . "<br>";
echo "Port: " . SMTP_PORT . "<br>";
echo "Utilisateur: " . SMTP_USER . "<br>";
echo "Mot de passe: " . (SMTP_PASS ? "Configuré (masqué)" : "❌ Non configuré") . "<br>";
echo "Email expéditeur: " . SMTP_FROM_EMAIL . "<br>";
?>
