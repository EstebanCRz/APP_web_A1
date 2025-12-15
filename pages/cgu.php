<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Conditions Générales d'Utilisation - AmiGo";
$pageDescription = "CGU de la plateforme AmiGo";
$assetsDepth = 1;
$customCSS = "css/cgu.css";

include '../includes/header.php';
?>

<div class="container">
    <h2>Conditions Générales d'Utilisation</h2>
    <p class="last-updated">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
    
    <section>
        <h3>1. Objet</h3>
        <p>Les présentes conditions générales d'utilisation (CGU) régissent l'utilisation de la plateforme AmiGo, permettant aux utilisateurs de créer et participer à des événements.</p>
    </section>
    
    <section>
        <h3>2. Inscription</h3>
        <p>L'inscription sur AmiGo nécessite la création d'un compte avec des informations exactes et à jour. Vous êtes responsable de la confidentialité de vos identifiants.</p>
    </section>
    
    <section>
        <h3>3. Utilisation du service</h3>
        <p>Vous vous engagez à utiliser AmiGo de manière respectueuse et conforme aux lois en vigueur. Tout comportement abusif ou illégal entraînera la suspension de votre compte.</p>
    </section>
    
    <section>
        <h3>4. Événements</h3>
        <p>Les organisateurs d'événements sont responsables du contenu et de la tenue de leurs événements. AmiGo ne peut être tenu responsable des événements organisés via la plateforme.</p>
    </section>
    
    <section>
        <h3>5. Données personnelles</h3>
        <p>Vos données personnelles sont traitées conformément à notre politique de confidentialité et au RGPD.</p>
    </section>
    
    <section>
        <h3>6. Propriété intellectuelle</h3>
        <p>Tous les contenus présents sur AmiGo (textes, images, logos) sont protégés par le droit de la propriété intellectuelle.</p>
    </section>
    
    <section>
        <h3>7. Responsabilité</h3>
        <p>AmiGo met tout en œuvre pour assurer la disponibilité du service mais ne peut garantir une absence totale d'interruption.</p>
    </section>
    
    <section>
        <h3>8. Modification des CGU</h3>
        <p>AmiGo se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés des modifications majeures.</p>
    </section>
    
    <section>
        <h3>9. Contact</h3>
        <p>Pour toute question concernant ces CGU, contactez-nous via notre <a href="contact.php">page de contact</a>.</p>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
