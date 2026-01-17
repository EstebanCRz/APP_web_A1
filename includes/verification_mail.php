<?php
// Génère un code de vérification à 6 chiffres
function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Envoie le mail de vérification
function sendVerificationMail($to, $code) {
    require_once __DIR__ . '/send_zoho_mail.php';
    $subject = 'Votre code de vérification AmiGo';
    $body = '<h2>Code de vérification</h2>'
          . '<p>Voici votre code de vérification pour confirmer votre connexion :</p>'
          . '<div style="font-size:2rem; font-weight:bold; letter-spacing:0.2em; color:#3ab5c8; margin:1.5em 0;">'
          . htmlspecialchars($code) . '</div>'
          . '<p>Ce code est valable 10 minutes.</p>'
          . '<br><div style="color:#888; font-size:0.95em; margin-top:2em;">© 2026 AmiGo - Tous droits réservés</div>';
    return sendZohoMail($to, $subject, $body, 'AmiGo', 'amigocontact@zohomail.eu');
}
