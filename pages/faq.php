<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "FAQ - AmiGo";
$pageDescription = "Questions fréquemment posées";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

$faqs = [
    [
        'question' => 'Comment créer un compte ?',
        'answer' => 'Cliquez sur "Inscription" en haut de la page et remplissez le formulaire avec vos informations.'
    ],
    [
        'question' => 'Comment participer à un événement ?',
        'answer' => 'Connectez-vous, parcourez les événements et cliquez sur "S’inscrire" sur la page de l’événement qui vous intéresse.'
    ],
    [
        'question' => 'Comment créer un événement ?',
        'answer' => 'Une fois connecté, accédez à la section "Créer un événement" depuis le menu et renseignez les informations demandées.'
    ],
    [
        'question' => 'Est-ce gratuit ?',
        'answer' => 'Oui, l’inscription est gratuite. Certains événements peuvent être payants selon le choix de l’organisateur.'
    ],
    [
        'question' => 'Comment contacter l’organisateur d’un événement ?',
        'answer' => 'Les informations de contact de l’organisateur sont disponibles sur la page de l’événement.'
    ],
    [
        'question' => 'Puis-je annuler ma participation ?',
        'answer' => 'Oui, vous pouvez vous désinscrire à tout moment depuis votre profil, dans la section "Événements inscrits".'
    ]
];

include '../includes/header.php';
?>

<main class="faq-container">
    <header class="faq-header">
        <h1>Foire Aux Questions</h1>
        <p>Retrouvez ici les réponses aux questions les plus fréquemment posées sur AmiGo.</p>
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
        <p>Vous ne trouvez pas la réponse à votre question ?</p>
        <a href="contact.php" class="btn btn-primary">Contactez-nous</a>
    </section>
</main>

<script src="js/faq.js"></script>

<?php include '../includes/footer.php'; ?>

