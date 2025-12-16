<?php 
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Mentions légales - AmiGo";
$pageDescription = "Mentions légales de la plateforme AmiGo";
$assetsDepth = 1;
$customCSS = "css/mentions-legales.css";

include '../includes/header.php';
?>

<main class="legal-container">
    <header class="legal-header">
        <h1>Mentions légales</h1>
        <p>
            Conformément aux dispositions légales en vigueur, vous trouverez ci-dessous
            les informations relatives à l’éditeur et à l’exploitation du site AmiGo.
        </p>
    </header>

    <section class="legal-section">
        <h2>1. Éditeur du site</h2>
        <p><strong>Nom :</strong> AmiGo</p>
        <p><strong>Forme juridique :</strong> Projet étudiant (à but non commercial)</p>
        <p><strong>Siège social :</strong> 123 rue de Paris, 75001 Paris, France</p>
        <p><strong>Email :</strong> contact@amigo.fr</p>
        <p><strong>Téléphone :</strong> +33 1 23 45 67 89</p>
    </section>

    <section class="legal-section">
        <h2>2. Directeur de la publication</h2>
        <p>
            <strong>Responsable de la publication :</strong> Équipe AmiGo  
        </p>
        <p>
            <strong>Contact :</strong> contact@amigo.fr
        </p>
    </section>

    <section class="legal-section">
        <h2>3. Hébergement</h2>
        <p><strong>Hébergeur :</strong> [Nom de l’hébergeur]</p>
        <p><strong>Adresse :</strong> [Adresse de l’hébergeur]</p>
        <p><strong>Téléphone :</strong> [Numéro de téléphone]</p>
    </section>

    <section class="legal-section">
        <h2>4. Propriété intellectuelle</h2>
        <p>
            L’ensemble des contenus présents sur le site AmiGo (textes, images,
            éléments graphiques, logo) est protégé par le droit de la propriété
            intellectuelle. Toute reproduction, représentation ou diffusion, même
            partielle, est interdite sans autorisation préalable.
        </p>
    </section>

    <section class="legal-section">
        <h2>5. Données personnelles</h2>
        <p>
            Les données personnelles collectées sur le site sont traitées conformément
            au Règlement Général sur la Protection des Données (RGPD).
        </p>
        <p>
            Vous disposez d’un droit d’accès, de rectification et de suppression de vos
            données. Pour exercer ces droits, vous pouvez nous contacter à l’adresse :
            <strong>contact@amigo.fr</strong>.
        </p>
    </section>

    <section class="legal-section">
        <h2>6. Cookies</h2>
        <p>
            Le site AmiGo utilise des cookies afin d’améliorer l’expérience utilisateur
            et de mesurer l’audience. En poursuivant votre navigation sur le site,
            vous acceptez l’utilisation de ces cookies.
        </p>
    </section>

    <section class="legal-section">
        <h2>7. Liens externes</h2>
        <p>
            Le site AmiGo peut contenir des liens vers des sites externes. AmiGo ne
            saurait être tenu responsable du contenu ou du fonctionnement de ces sites.
        </p>
    </section>

    <section class="legal-section">
        <h2>8. Contact</h2>
        <p>
            Pour toute question relative aux présentes mentions légales, vous pouvez
            nous contacter via la <a href="contact.php">page de contact</a>.
        </p>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
