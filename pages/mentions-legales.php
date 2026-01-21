<?php 
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';

$pageTitle = t('legal.title') . " - AmiGo";
$pageDescription = t('legal.description');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/legal.css"
];

include '../includes/header.php';
?>

<main class="legal-container">
    <header class="legal-header">
        <h1><?php echo t('legal.title'); ?></h1>
        <p><?php echo t('legal.description'); ?></p>
    </header>

    <section class="legal-section">
        <h2><?php echo t('legal.section1_title'); ?></h2>
        <p><strong><?php echo t('legal.section1_name'); ?></strong> AmiGo</p>
        <p><strong><?php echo t('legal.section1_legal_form'); ?></strong> <?php echo t('legal.section1_legal_form_value'); ?></p>
        <p><strong><?php echo t('legal.section1_address'); ?></strong> <?php echo t('legal.section1_address_value'); ?></p>
        <p><strong><?php echo t('legal.section1_email'); ?></strong> contact@amigo.fr</p>
        <p><strong><?php echo t('legal.section1_phone'); ?></strong> +33 1 23 45 67 89</p>
    </section>

    <section class="legal-section">
        <h2><?php echo t('legal.section2_title'); ?></h2>
        <p><strong><?php echo t('legal.section2_responsible'); ?></strong> <?php echo t('legal.section2_responsible_value'); ?></p>
        <p><strong><?php echo t('legal.section2_contact'); ?></strong> contact@amigo.fr</p>
    </section>

    <section class="legal-section">
        <h2><?php echo t('legal.section3_title'); ?></h2>
        <p><strong><?php echo t('legal.section3_host'); ?></strong> <?php echo t('legal.section3_host_value'); ?></p>
        <p><strong><?php echo t('legal.section3_address'); ?></strong> <?php echo t('legal.section3_address_value'); ?></p>
        <p><strong><?php echo t('legal.section3_phone'); ?></strong> <?php echo t('legal.section3_phone_value'); ?></p>
    </section>

    <section class="legal-section">
        <h2><?php echo t('legal.section4_title'); ?></h2>
        <p><?php echo t('legal.section4_content'); ?></p>
    </section>

    <section class="legal-section">
        <h2><?php echo t('legal.section5_title'); ?></h2>
        <p><?php echo t('legal.section5_content1'); ?></p>
        <p><?php echo t('legal.section5_content2'); ?> <strong>contact@amigo.fr</strong>.</p>
    </section>

    <section class="legal-section">
        <h2><?php echo t('legal.section6_title'); ?></h2>
        <p><?php echo t('legal.section6_content'); ?></p>
    </section>

    <section class="legal-section">
        <h2><?php echo t('legal.section7_title'); ?></h2>
        <p><?php echo t('legal.section7_content'); ?></p>
    </section>

    <section class="legal-section">
        <h2><?php echo t('legal.section8_title'); ?></h2>
        <p><?php echo t('legal.section8_content'); ?> <a href="contact.php"><?php echo t('legal.section8_contact_link'); ?></a>.</p>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
