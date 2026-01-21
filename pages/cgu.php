<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';

$pageTitle = t('pages.cgu') . " - AmiGo";
$pageDescription = t('pages.cgu');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/legal.css"
];

include '../includes/header.php';
?>

<main class="legal-container">
    <header class="legal-header">
        <div class="legal-header-content">
            <h1><?php echo t('pages.cgu'); ?></h1>
            <p class="subtitle"><?php echo t('pages.cgu_subtitle'); ?></p>
        </div>
        <div class="last-updated-badge">
            <?php echo t('pages.last_updated'); ?> : <?php echo date('d/m/Y'); ?>
        </div>
    </header>

    <nav class="legal-toc">
        <h3><?php echo t('pages.table_of_contents'); ?></h3>
        <ul>
            <li><a href="#section-1"><?php echo t('cgu.section1_title'); ?></a></li>
            <li><a href="#section-2"><?php echo t('cgu.section2_title'); ?></a></li>
            <li><a href="#section-3"><?php echo t('cgu.section3_title'); ?></a></li>
            <li><a href="#section-4"><?php echo t('cgu.section4_title'); ?></a></li>
            <li><a href="#section-5"><?php echo t('cgu.section5_title'); ?></a></li>
            <li><a href="#section-6"><?php echo t('cgu.section6_title'); ?></a></li>
            <li><a href="#section-7"><?php echo t('cgu.section7_title'); ?></a></li>
            <li><a href="#section-8"><?php echo t('cgu.section8_title'); ?></a></li>
            <li><a href="#section-9"><?php echo t('cgu.section9_title'); ?></a></li>
        </ul>
    </nav>

    <section class="legal-section" id="section-1">
        <div class="section-header">
            <h2><?php echo t('cgu.section1_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section1_content'); ?></p>
    </section>

    <section class="legal-section" id="section-2">
        <div class="section-header">
            <h2><?php echo t('cgu.section2_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section2_content'); ?></p>
    </section>

    <section class="legal-section" id="section-3">
        <div class="section-header">
            <h2><?php echo t('cgu.section3_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section3_content'); ?></p>
    </section>

    <section class="legal-section" id="section-4">
        <div class="section-header">
            <h2><?php echo t('cgu.section4_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section4_content'); ?></p>
    </section>

    <section class="legal-section" id="section-5">
        <div class="section-header">
            <h2><?php echo t('cgu.section5_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section5_content'); ?></p>
    </section>

    <section class="legal-section" id="section-6">
        <div class="section-header">
            <h2><?php echo t('cgu.section6_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section6_content'); ?></p>
    </section>

    <section class="legal-section" id="section-7">
        <div class="section-header">
            <h2><?php echo t('cgu.section7_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section7_content'); ?></p>
    </section>

    <section class="legal-section" id="section-8">
        <div class="section-header">
            <h2><?php echo t('cgu.section8_title'); ?></h2>
        </div>
        <p><?php echo t('cgu.section8_content'); ?></p>
    </section>

    <section class="legal-section" id="section-9">
        <div class="section-header">
            <h2><?php echo t('cgu.section9_title'); ?></h2>
        </div>
        <p>
            <?php echo t('cgu.section9_content'); ?>
            <a href="contact.php" class="link-button"><?php echo t('cgu.section9_link'); ?></a>.
            <?php echo t('cgu.section9_content2'); ?>
        </p>
    </section>

    <div class="legal-footer">
        <p>© 2025 AmiGo. <?php echo t('cgu.all_rights_reserved'); ?></p>
        <p><a href="../index.php" class="footer-link"><?php echo t('cgu.back_to_home'); ?></a></p>
    </div>
</main>

<?php include '../includes/footer.php';
