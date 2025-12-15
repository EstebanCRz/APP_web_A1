<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Mentions Légales - AmiGo";
$pageDescription = "Mentions légales de la plateforme AmiGo";
$assetsDepth = 1;
$customCSS = "css/mentions-legales.css";

include '../includes/header.php';
?>

<div class="container">
    <h2>Mentions Légales</h2>
    
    <section>
        <h3>1. Éditeur du site</h3>
        <p><strong>Nom :</strong> AmiGo</p>
        <p><strong>Forme juridique :</strong> [À compléter]</p>
        <p><strong>Siège social :</strong> 123 Rue de Paris, 75001 Paris, France</p>
        <p><strong>Email :</strong> contact@amigo.fr</p>
        <p><strong>Téléphone :</strong> +33 1 23 45 67 89</p>
    </section>
    
    <section>
        <h3>2. Directeur de publication</h3>
        <p><strong>Nom :</strong> [À compléter]</p>
        <p><strong>Email :</strong> contact@amigo.fr</p>
    </section>
    
    <section>
        <h3>3. Hébergeur</h3>
        <p><strong>Nom :</strong> [Nom de l'hébergeur]</p>
        <p><strong>Adresse :</strong> [Adresse de l'hébergeur]</p>
        <p><strong>Téléphone :</strong> [Téléphone de l'hébergeur]</p>
    </section>
    
    <section>
        <h3>4. Propriété intellectuelle</h3>
        <p>L'ensemble du contenu de ce site (textes, images, vidéos) est protégé par le droit d'auteur. Toute reproduction, même partielle, est interdite sans autorisation préalable.</p>
    </section>
    
    <section>
        <h3>5. Données personnelles</h3>
        <p>Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression de vos données personnelles.</p>
        <p>Pour exercer ces droits, contactez-nous à : contact@amigo.fr</p>
    </section>
    
    <section>
        <h3>6. Cookies</h3>
        <p>Ce site utilise des cookies pour améliorer l'expérience utilisateur. En continuant votre navigation, vous acceptez l'utilisation de ces cookies.</p>
    </section>
    
    <section>
        <h3>7. Liens externes</h3>
        <p>AmiGo n'est pas responsable du contenu des sites externes vers lesquels des liens peuvent pointer.</p>
    </section>
    
    <section>
        <h3>8. Contact</h3>
        <p>Pour toute question concernant ces mentions légales, contactez-nous via notre <a href="contact.php">page de contact</a>.</p>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
