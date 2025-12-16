<?php
session_start();

$pageTitle = "AmiGo - Accueil";
$pageDescription = "AmiGo - Plateforme de rencontre et d'événements";
$assetsDepth = 0; // Fichier à la racine
$customCSS = "assets/css/index.css";

include 'includes/header.php';
?>

<div class="container">
    <section>
        <h2>Bienvenue sur AmiGo</h2>
        <p>Découvrez et participez à des événements proches de vous. Rencontrez de nouvelles personnes et partagez des moments inoubliables !</p>
        
        <div style="margin: 2rem 0;">
            <a href="auth/register.php" class="btn btn-primary">S'inscrire</a>
            <a href="auth/login.php" class="btn btn-secondary">Se connecter</a>
        </div>
    </section>

    <section>
        <h3>Événements tendance</h3>
        <div class="grid">
            <div class="event-card">
                <div class="event-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                <div class="event-info">
                    <h4 class="event-title">Concert Rock en plein air</h4>
                    <p class="event-details"> 25/11/2025 - 20h00</p>
                    <p class="event-details"> Paris, France</p>
                    <p class="event-details"> 50 places disponibles</p>
                    <a href="events/event-details.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>

            <div class="event-card">
                <div class="event-banner" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                <div class="event-info">
                    <h4 class="event-title">Match de Football</h4>
                    <p class="event-details"> 28/11/2025 - 15h00</p>
                    <p class="event-details"> Lyon, France</p>
                    <p class="event-details"> 20 places disponibles</p>
                    <a href="events/event-details.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>

            <div class="event-card">
                <div class="event-banner" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                <div class="event-info">
                    <h4 class="event-title">Soirée Cinéma</h4>
                    <p class="event-details"> 30/11/2025 - 19h30</p>
                    <p class="event-details"> Marseille, France</p>
                    <p class="event-details"> 30 places disponibles</p>
                    <a href="events/event-details.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>
        </div>
    </section>

    <section>
        <h3>Rechercher un événement</h3>
        <form action="events/events-list.php" method="get">
            <div class="form-group">
                <input type="text" name="search" placeholder="Rechercher par mots-clés..." aria-label="Rechercher un événement">
            </div>
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
