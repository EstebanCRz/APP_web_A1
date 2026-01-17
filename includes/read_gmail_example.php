<?php
// Script PHP pour lire les e-mails Gmail via IMAP
// Prérequis : extension PHP IMAP activée

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'amigoocontact@gmail.com';
$password = 'Azerty123,'; // Utilisez un mot de passe d'application Google

// Connexion à la boîte mail
$inbox = imap_open($hostname, $username, $password) or die('Impossible de se connecter à Gmail : ' . imap_last_error());

// Recherche des 10 derniers e-mails
$emails = imap_search($inbox, 'ALL');

if ($emails) {
    rsort($emails);
    $count = 0;
    foreach ($emails as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0);
        $message = imap_fetchbody($inbox, $email_number, 1);
        echo '<hr><b>Sujet :</b> ' . htmlspecialchars($overview[0]->subject) . '<br>';
        echo '<b>De :</b> ' . htmlspecialchars($overview[0]->from) . '<br>';
        echo '<b>Date :</b> ' . htmlspecialchars($overview[0]->date) . '<br>';
        echo '<b>Message :</b><br>' . nl2br(htmlspecialchars($message)) . '<br>';
        $count++;
        if ($count >= 10) break;
    }
} else {
    echo 'Aucun e-mail trouvé.';
}

imap_close($inbox);
