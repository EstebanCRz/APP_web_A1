<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Données des activités (Assure-toi que les 'type' correspondent aux boutons)
$activities = [
    ['id' => 1, 'title' => 'Sortie Running au Parc', 'type' => 'Sport', 'loc' => 'Parc Monceau, Paris', 'date' => 'jeu. 23 oct', 'user' => 'Camille', 'color' => '#8BC34A', 'inscrits' => '7/12', 'img' => 'https://images.unsplash.com/photo-1502904550040-7534597429ae?q=80&w=400'],
    ['id' => 2, 'title' => 'Balade Photo au Bord de l\'Eau', 'type' => 'Art', 'loc' => 'Bordeaux', 'date' => 'sam. 25 oct', 'user' => 'Zoé', 'color' => '#03A9F4', 'inscrits' => '2/15', 'img' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?q=80&w=400'],
    ['id' => 3, 'title' => 'Initiation Yoga Vinyasa', 'type' => 'Bien-être', 'loc' => 'Marseille', 'date' => 'lun. 27 oct', 'user' => 'Nora', 'color' => '#FFC107', 'inscrits' => '4/10', 'img' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?q=80&w=400'],
    ['id' => 4, 'title' => 'Soirée Jeux de Société', 'type' => 'Jeux', 'loc' => 'Toulouse', 'date' => 'dim. 26 oct', 'user' => 'Mathis', 'color' => '#9C27B0', 'inscrits' => '16/20', 'img' => 'https://images.unsplash.com/photo-1585504198199-2027774e50af?q=80&w=400']
];

$pageTitle = "AmiGo - Partage d'activités";
$assetsDepth = 0;
$customCSS = [
    "assets/css/style.css",
    "assets/css/index.css"
];

include 'includes/header.php';
?>

<div class="main-container">
    <section class="hero-section">
        <div class="hero-content">
            <h1>Partage d'activités entre particuliers</h1>
            <p>Découvrez, créez et rejoignez des activités près de chez vous : sport, cuisine, art, musique, nature...</p>
            
            <input type="text" id="searchBar" placeholder="Chercher une activité (mot-clé, ville, organisateur)">
            
            <div class="filter-tags">
                <button class="filter-btn active" data-filter="all">Tous</button>
                <button class="filter-btn" data-filter="Sport">Sport</button>
                <button class="filter-btn" data-filter="Cuisine">Cuisine</button>
                <button class="filter-btn" data-filter="Art">Art</button>
                <button class="filter-btn" data-filter="Musique">Musique</button>
                <button class="filter-btn" data-filter="Jeux">Jeux</button>
                <button class="filter-btn" data-filter="Nature">Nature</button>
                <button class="filter-btn" data-filter="Bien-être">Bien-être</button>
            </div>
            
            <div class="hero-badges">
                <span class="hero-badge badge-local">Communauté locale</span>
                <span class="hero-badge badge-convivial">Convivial</span>
                <span class="hero-badge badge-gratuit">Gratuit</span>
            </div>
        </div>
        
        <div class="hero-grid">
            <img src="https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=400" alt="Running">
            <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400" alt="Nature">
            <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=400" alt="Yoga">
            <img src="https://images.unsplash.com/photo-1511367461989-f85a21fda167?w=400" alt="Music">
            <img src="https://images.unsplash.com/photo-1511876484798-816e2c24f1c3?w=400" alt="Games">
        </div>
    </section>

    <div class="section-header">
        <div>
            <h2>Activités récentes</h2>
            <p>Les dernières propositions de la communauté</p>
        </div>
        <a href="events/events-list.php" class="voir-tout">Voir tout</a>
    </div>
    
    <div class="activities-grid" id="activitiesContainer">
        <?php foreach ($activities as $act): ?>
            <div class="activity-item" data-type="<?php echo htmlspecialchars($act['type'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card-img" style="background-image: url('<?php echo htmlspecialchars($act['img'], ENT_QUOTES, 'UTF-8'); ?>');">
                    <span class="badge" style="background: <?php echo htmlspecialchars($act['color'], ENT_QUOTES, 'UTF-8'); ?>;"><?php echo htmlspecialchars($act['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($act['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                    <div class="card-meta">
                        <span class="info">📍 <?php echo htmlspecialchars($act['loc'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="info">📅 <?php echo htmlspecialchars($act['date'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="info">👤 <?php echo htmlspecialchars($act['user'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="card-footer">
                        <span><?php echo htmlspecialchars($act['inscrits'], ENT_QUOTES, 'UTF-8'); ?> inscrits</span>
                        <button class="btn-subscribe">S'inscrire</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="assets/script.js"></script>
<?php include 'includes/footer.php'; ?>