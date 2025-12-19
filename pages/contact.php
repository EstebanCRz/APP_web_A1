<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Contact - AmiGo";
$pageDescription = "Contactez l'équipe AmiGo";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/contact.css"
];

include '../includes/header.php';
?>

<div class="container">
    <h2>Contactez-nous</h2>
    <p>Vous avez une question ? N'hésitez pas à nous contacter !</p>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="subject">Sujet</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
    
    <div class="contact-info">
        <h3>Coordonnées</h3>
        <p> Email : contact@amigo.fr</p>
        <p> Téléphone : +33 1 23 45 67 89</p>
        <p> Adresse : 123 Rue de Paris, 75001 Paris</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
