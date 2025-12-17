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
$customCSS = "assets/css/index.css";

include 'includes/header.php';
?>

<div class="main-container">
    <section class="hero-section">
        <div class="hero-content">
            <h1>Partage d'activités entre particuliers</h1>
            <input type="text" id="searchBar" placeholder="Chercher une activité (mot-clé, ville...)">
            
            <div class="filter-tags">
                <button class="filter-btn active" data-filter="all">Tous</button>
                <button class="filter-btn" data-filter="Sport">Sport</button>
                <button class="filter-btn" data-filter="Art">Art</button>
                <button class="filter-btn" data-filter="Bien-être">Bien-être</button>
                <button class="filter-btn" data-filter="Jeux">Jeux</button>
            </div>
        </div>
        
        <div class="hero-grid">
            <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300" alt="">
            <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=300" alt="">
            <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=300" alt="">
        </div>
    </section>

    <section>
        <h2>Activités récentes</h2>
        <div class="activities-grid" id="activitiesContainer">
            <?php foreach ($activities as $act): ?>
                <div class="activity-item" data-type="<?php echo $act['type']; ?>">
                    <div class="card-img" style="background-image: url('<?php echo $act['img']; ?>');">
                        <span class="badge" style="background: <?php echo $act['color']; ?>;"><?php echo $act['type']; ?></span>
                    </div>
                    <div class="card-body">
                        <h4><?php echo htmlspecialchars($act['title']); ?></h4>
                        <p class="info"><?php echo $act['loc']; ?> • <?php echo $act['date']; ?></p>
                        <div class="card-footer">
                            <span><?php echo $act['inscrits']; ?></span>
                            <button class="btn-subscribe">S'inscrire</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script src="assets/script.js"></script>
<?php include 'includes/footer.php'; ?>