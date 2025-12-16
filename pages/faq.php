<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "FAQ - AmiGo";
$pageDescription = "Questions fréquemment posées";
$assetsDepth = 1;
$customCSS = "../assets/css/index.css";

$faqs = [
    ['question' => 'Comment créer un compte ?', 'answer' => 'Cliquez sur "Inscription" en haut de la page et remplissez le formulaire avec vos informations.'],
    ['question' => 'Comment participer à un événement ?', 'answer' => 'Connectez-vous, parcourez les événements et cliquez sur "S\'inscrire" sur la page de l\'événement qui vous intéresse.'],
    ['question' => 'Comment créer un événement ?', 'answer' => 'Une fois connecté, accédez à la section "Créer un événement" dans le menu et remplissez les détails de votre événement.'],
    ['question' => 'Est-ce gratuit ?', 'answer' => 'Oui, l\'inscription et la participation aux événements gratuits sont entièrement gratuites. Certains événements peuvent avoir un coût fixé par l\'organisateur.'],
    ['question' => 'Comment contacter l\'organisateur d\'un événement ?', 'answer' => 'Sur la page de l\'événement, vous trouverez les informations de contact de l\'organisateur.'],
    ['question' => 'Puis-je annuler ma participation ?', 'answer' => 'Oui, vous pouvez vous désinscrire d\'un événement depuis votre profil dans la section "Événements inscrits".']
];

include '../includes/header.php';
?>

<div class="container">
    <h2>Questions Fréquemment Posées (FAQ)</h2>
    <div class="faq-list">
        <?php foreach ($faqs as $index => $faq): ?>
            <div class="faq-item">
                <h3 class="faq-question"> <?php echo htmlspecialchars($faq['question']); ?></h3>
                <p class="faq-answer"><?php echo htmlspecialchars($faq['answer']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="faq-contact">
        <p>Vous ne trouvez pas la réponse à votre question ?</p>
        <a href="contact.php" class="btn btn-primary">Contactez-nous</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
