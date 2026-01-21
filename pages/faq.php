<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';

$pageTitle = t('faq.title') . " - AmiGo";
$pageDescription = t('faq.description');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/faq.css"
];

$faqs = [
    [
        'question' => t('faq.q1'),
        'answer' => t('faq.a1')
    ],
    [
        'question' => t('faq.q2'),
        'answer' => t('faq.a2')
    ],
    [
        'question' => t('faq.q3'),
        'answer' => t('faq.a3')
    ],
    [
        'question' => t('faq.q4'),
        'answer' => t('faq.a4')
    ],
    [
        'question' => t('faq.q5'),
        'answer' => t('faq.a5')
    ],
    [
        'question' => t('faq.q6'),
        'answer' => t('faq.a6')
    ]
];

include '../includes/header.php';
?>

<main class="faq-container">
    <header class="faq-header">
        <h1><?php echo t('faq.title'); ?></h1>
        <p><?php echo t('faq.description'); ?></p>
    </header>

    <section class="faq-list">
        <?php foreach ($faqs as $index => $faq): ?>
            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <?php echo htmlspecialchars($faq['question']); ?>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="faq-contact">
        <p><?php echo t('faq.cant_find_answer'); ?></p>
        <a href="contact.php" class="btn btn-primary"><?php echo t('footer.contact'); ?></a>
    </section>
</main>

<script src="../assets/js/faq.js"></script>

<?php include '../includes/footer.php'; ?>

