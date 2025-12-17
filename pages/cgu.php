<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Conditions Générales d'Utilisation - AmiGo";
$pageDescription = "CGU de la plateforme AmiGo";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

include '../includes/header.php';
?>

<main class="legal-container">
    <header class="legal-header">
        <div class="legal-header-content">
            <h1>Conditions Générales d'Utilisation</h1>
            <p class="subtitle">AmiGo - Plateforme de partage d'événements</p>
        </div>
        <div class="last-updated-badge">
            Dernière mise à jour : <?php echo date('d/m/Y'); ?>
        </div>
    </header>

    <nav class="legal-toc">
        <h3>Table des matières</h3>
        <ul>
            <li><a href="#section-1">1. Objet</a></li>
            <li><a href="#section-2">2. Inscription</a></li>
            <li><a href="#section-3">3. Utilisation du service</a></li>
            <li><a href="#section-4">4. Événements</a></li>
            <li><a href="#section-5">5. Données personnelles</a></li>
            <li><a href="#section-6">6. Propriété intellectuelle</a></li>
            <li><a href="#section-7">7. Responsabilité</a></li>
            <li><a href="#section-8">8. Modification des CGU</a></li>
            <li><a href="#section-9">9. Contact</a></li>
        </ul>
    </nav>

    <section class="legal-section" id="section-1">
        <div class="section-header">
            <h2>1. Objet</h2>
        </div>
        <p>
            Les présentes conditions générales d'utilisation (CGU) régissent l'utilisation
            de la plateforme AmiGo, permettant aux utilisateurs de créer et de participer
            à des événements. En accédant à cette plateforme, vous acceptez sans réserve
            l'ensemble de ces conditions.
        </p>
    </section>

    <section class="legal-section" id="section-2">
        <div class="section-header">
            <h2>2. Inscription</h2>
        </div>
        <p>
            L'inscription sur AmiGo nécessite la création d'un compte avec des informations
            exactes et à jour. Vous êtes responsable de la confidentialité de vos identifiants
            et du maintien de la sécurité de votre compte. Vous acceptez de notifier AmiGo
            de toute utilisation non autorisée.
        </p>
    </section>

    <section class="legal-section" id="section-3">
        <div class="section-header">
            <h2>3. Utilisation du service</h2>
        </div>
        <p>
            Vous vous engagez à utiliser AmiGo de manière respectueuse et conforme aux lois
            en vigueur. Tout comportement abusif ou illégal pourra entraîner la suspension
            du compte sans préavis. Sont notamment interdits : le harcèlement, la diffamation,
            la publicité non autorisée et la dissémination de contenus offensants.
        </p>
    </section>

    <section class="legal-section" id="section-4">
        <div class="section-header">
            <h2>4. Événements</h2>
        </div>
        <p>
            Les organisateurs d'événements sont seuls responsables du contenu et du bon
            déroulement de leurs événements. AmiGo ne saurait être tenu responsable des
            événements organisés via la plateforme, notamment en cas de litige entre
            participants ou d'annulation d'événement.
        </p>
    </section>

    <section class="legal-section" id="section-5">
        <div class="section-header">
            <h2>5. Données personnelles</h2>
        </div>
        <p>
            Les données personnelles des utilisateurs sont traitées conformément à la
            réglementation en vigueur, notamment le Règlement Général sur la Protection
            des Données (RGPD). Pour connaître les détails sur le traitement de vos données,
            veuillez consulter notre politique de confidentialité.
        </p>
    </section>

    <section class="legal-section" id="section-6">
        <div class="section-header">
            <h2>6. Propriété intellectuelle</h2>
        </div>
        <p>
            L'ensemble des contenus présents sur la plateforme AmiGo (textes, images,
            logos, éléments graphiques) est protégé par le droit de la propriété intellectuelle.
            Toute reproduction, modification ou distribution sans autorisation expresse
            est interdite.
        </p>
    </section>

    <section class="legal-section" id="section-7">
        <div class="section-header">
            <h2>7. Responsabilité</h2>
        </div>
        <p>
            AmiGo met tout en œuvre pour assurer l'accessibilité et le bon fonctionnement
            du service, mais ne peut garantir une disponibilité continue et sans interruption.
            AmiGo ne saurait être tenu responsable en cas de perte de données ou de
            dysfonctionnement temporaire.
        </p>
    </section>

    <section class="legal-section" id="section-8">
        <div class="section-header">
            <h2>8. Modification des CGU</h2>
        </div>
        <p>
            AmiGo se réserve le droit de modifier les présentes conditions à tout moment.
            Les utilisateurs seront informés de toute modification substantielle via
            l'adresse email associée à leur compte ou lors de leur prochaine visite.
        </p>
    </section>

    <section class="legal-section" id="section-9">
        <div class="section-header">
            <h2>9. Contact</h2>
        </div>
        <p>
            Pour toute question relative aux présentes CGU, vous pouvez nous contacter via
            la <a href="contact.php" class="link-button">page de contact</a>.
            Nous nous efforçons de répondre à toute demande dans les meilleurs délais.
        </p>
    </section>

    <div class="legal-footer">
        <p>© 2025 AmiGo. Tous droits réservés.</p>
        <p><a href="../index.php" class="footer-link">Retour à l'accueil</a></p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
