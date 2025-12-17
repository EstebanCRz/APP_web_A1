<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pageTitle = "Mon Profil - AmiGo";
$pageDescription = "Gérez votre profil AmiGo";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

include '../includes/header.php';
?>

<div class="container">
    <section>
        <h2>Mon Profil</h2>
        <p>Gérez vos informations personnelles et vos préférences.</p>
        
        <div style="margin: 2rem 0;">
            <a href="profile-edit.php" class="btn btn-primary">Modifier mon profil</a>
            <a href="profile-created.php" class="btn btn-secondary">Événements créés</a>
            <a href="profile-registered.php" class="btn btn-secondary">Événements inscrits</a>
        </div>
    </section>

    <section>
        <h3>Événements à venir</h3>
        <!-- TODO: Charger les événements depuis la base de données avec PHP -->
        <div class="grid">
            <div class="event-card">
                <div class="event-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                <div class="event-info">
                    <h4 class="event-title">Concert Rock en plein air</h4>
                    <p class="event-details">📅 25/11/2025 - 20h00</p>
                    <p class="event-details">📍 Paris, France</p>
                    <p class="event-details">👥 50 places disponibles</p>
                    <a href="../events/event-details.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>

            <div class="event-card">
                <div class="event-banner" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                <div class="event-info">
                    <h4 class="event-title">Match de Football</h4>
                    <p class="event-details">📅 28/11/2025 - 15h00</p>
                    <p class="event-details">📍 Lyon, France</p>
                    <p class="event-details">👥 20 places disponibles</p>
                    <a href="../events/event-details.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>

            <div class="event-card">
                <div class="event-banner" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                <div class="event-info">
                    <h4 class="event-title">Soirée Cinéma</h4>
                    <p class="event-details">📅 30/11/2025 - 19h30</p>
                    <p class="event-details">📍 Marseille, France</p>
                    <p class="event-details">👥 30 places disponibles</p>
                    <a href="../events/event-details.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
