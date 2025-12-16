<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Conditions Générales d'Utilisation - AmiGo";
$pageDescription = "CGU de la plateforme AmiGo";
$assetsDepth = 1;
$customCSS = "css/cgu.css";

include '../includes/header.php';
?>

<main class="legal-container">
    <header class="legal-header">
        <h1>Conditions Générales d'Utilisation</h1>
        <p class="last-updated">
            Dernière mise à jour : <?php echo date('d/m/Y'); ?>
        </p>
    </header>

    <section class="legal-section">
        <h2>1. Objet</h2>
        <p>
            Les présentes conditions générales d'utilisation (CGU) régissent l'utilisation
            de la plateforme AmiGo, permettant aux utilisateurs de créer et de participer
            à des événements.
        </p>
    </section>

    <section class="legal-section">
        <h2>2. Inscription</h2>
        <p>
            L'inscription sur AmiGo nécessite la création d'un compte avec des informations
            exactes et à jour. Vous êtes responsable de la confidentialité de vos identifiants.
        </p>
    </section>

    <section class="legal-section">
        <h2>3. Utilisation du service</h2>
        <p>
            Vous vous engagez à utiliser AmiGo de manière respectueuse et conforme aux lois
            en vigueur. Tout comportement abusif ou illégal pourra entraîner la suspension
            du compte.
        </p>
    </section>

    <section class="legal-section">
        <h2>4. Événements</h2>
        <p>
            Les organisateurs d'événements sont seuls responsables du contenu et du bon
            déroulement de leurs événements. AmiGo ne saurait être tenu responsable des
            événements organisés via la plateforme.
        </p>
    </section>

    <section class="legal-section">
        <h2>5. Données personnelles</h2>
        <p>
            Les données personnelles des utilisateurs sont traitées conformément à la
            réglementation en vigueur, notamment le Règlement Général sur la Protection
            des Données (RGPD).
        </p>
    </section>

    <section class="legal-section">
        <h2>6. Propriété intellectuelle</h2>
        <p>
            L'ensemble des contenus présents sur la plateforme AmiGo (textes, images,
            logos, éléments graphiques) est protégé par le droit de la propriété intellectuelle.
        </p>
    </section>

    <section class="legal-section">
        <h2>7. Responsabilité</h2>
        <p>
            AmiGo met tout en œuvre pour assurer l'accessibilité et le bon fonctionnement
            du service, mais ne peut garantir une disponibilité continue et sans interruption.
        </p>
    </section>

    <section class="legal-section">
        <h2>8. Modification des CGU</h2>
        <p>
            AmiGo se réserve le droit de modifier les présentes conditions à tout moment.
            Les utilisateurs seront informés de toute modification substantielle.
        </p>
    </section>

    <section class="legal-section">
        <h2>9. Contact</h2>
        <p>
            Pour toute question relative aux présentes CGU, vous pouvez nous contacter via
            la <a href="contact.php">page de contact</a>.
        </p>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
