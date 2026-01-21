<?php
session_start();
require_once '../includes/language.php';
require_once '../includes/config.php';
require_once '../includes/activities_functions.php';
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$pdo = getDB();

// Récupérer les activités favorites de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.title,
            a.description,
            a.location,
            a.city,
            a.event_date,
            a.event_date as date,
            a.event_time,
            a.max_participants,
            a.current_participants,
            a.image,
            a.status,
            c.name as category_name,
            c.color as category_color,
            c.icon as category_icon,
            u.first_name as creator_first_name,
            u.last_name as creator_last_name,
            f.created_at as favorited_at
        FROM user_favorites f
        INNER JOIN activities a ON f.activity_id = a.id
        INNER JOIN activity_categories c ON a.category_id = c.id
        INNER JOIN users u ON a.creator_id = u.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$userId]);
    $favorites = $stmt->fetchAll();
} catch (PDOException $e) {
    $favorites = [];
}

$pageTitle = "Mes Favoris - AmiGo";
$pageDescription = "Événements favoris";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css",
    "../events/css/events-list.css"
];

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1> Mes Favoris</h1>
        <p class="subtitle">Retrouvez toutes les activités que vous avez sauvegardées</p>
    </div>
    
    <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <div class="empty-icon">💔</div>
            <h2>Aucun favori pour le moment</h2>
            <p>Commencez à ajouter des activités à vos favoris en cliquant sur l'icône ❤️</p>
            <a href="../events/events-list.php" class="btn btn-primary">Découvrir des activités</a>
        </div>
    <?php else: ?>
        <div class="favorites-count">
            <span class="count-badge"><?php echo count($favorites); ?></span> activité<?php echo count($favorites) > 1 ? 's' : ''; ?> en favori
        </div>
        
        <div class="events-grid">
            <?php foreach ($favorites as $activity): ?>
                <div class="event-card" data-activity-id="<?php echo $activity['id']; ?>">
                    <div class="event-card-header">
                        <span class="event-badge" style="background-color: <?php echo htmlspecialchars($activity['category_color'] ?? '#3498db'); ?>;">
                            <?php echo htmlspecialchars($activity['category_icon'] ?? '📌'); ?>
                            <?php echo htmlspecialchars($activity['category_name']); ?>
                        </span>
                        <button 
                            class="favorite-btn active" 
                            data-activity-id="<?php echo $activity['id']; ?>"
                            title="Retirer des favoris"
                        >
                            ❤️
                        </button>
                    </div>
                    
                    <?php if (!empty($activity['image'])): ?>
                        <div class="event-image">
                            <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['title']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="event-card-body">
                        <h3 class="event-title"><?php echo htmlspecialchars($activity['title']); ?></h3>
                        <p class="event-description"><?php echo htmlspecialchars(substr($activity['description'], 0, 120)) . '...'; ?></p>
                        
                        <div class="event-meta">
                            <div class="meta-item">
                                📍 <?php echo htmlspecialchars($activity['location']); ?>
                            </div>
                            <div class="meta-item">
                                📅 <?php echo date('d/m/Y', strtotime($activity['event_date'])); ?>
                            </div>
                            <div class="meta-item">
                                👥 <?php echo $activity['current_participants']; ?>/<?php echo $activity['max_participants']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="event-card-footer">
                        <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" class="btn btn-primary">
                            Voir les détails
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 2.5rem;
    color: var(--accent-structure);
    margin-bottom: 0.5rem;
}

.page-header .subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--surface-color);
    border-radius: 16px;
    max-width: 500px;
    margin: 3rem auto;
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.empty-state h2 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 2rem;
    font-size: 1.05rem;
}

.favorites-count {
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.1rem;
    color: var(--text-secondary);
}

.count-badge {
    display: inline-block;
    background: linear-gradient(135deg, var(--accent-color), var(--accent-secondary));
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.event-card {
    background: var(--surface-color);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.event-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.event-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: linear-gradient(135deg, rgba(85, 213, 224, 0.1), rgba(59, 106, 255, 0.1));
}

.event-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
    color: white;
    font-weight: 600;
}

.favorite-btn {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0.25rem;
}

.favorite-btn:not(.active) {
    opacity: 0.3;
}

.favorite-btn:hover {
    transform: scale(1.2);
}

.event-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.event-card-body {
    padding: 1.5rem;
    flex: 1;
}

.event-title {
    font-size: 1.3rem;
    color: var(--accent-structure);
    margin-bottom: 0.75rem;
    font-weight: 700;
}

.event-description {
    color: var(--text-secondary);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.meta-item {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.event-card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
}

.event-card-footer .btn {
    width: 100%;
    text-align: center;
}

@media (max-width: 768px) {
    .events-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const activityId = this.dataset.activityId;
            const isActive = this.classList.contains('active');
            
            fetch('../events/api/favorite-toggle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `activity_id=${activityId}&action=${isActive ? 'remove' : 'add'}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Retirer la carte de la grille avec animation
                    const card = this.closest('.event-card');
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        card.remove();
                        
                        // Vérifier s'il reste des favoris
                        const remainingCards = document.querySelectorAll('.event-card').length;
                        if (remainingCards === 0) {
                            location.reload();
                        } else {
                            // Mettre à jour le compteur
                            const countBadge = document.querySelector('.count-badge');
                            if (countBadge) {
                                const newCount = remainingCards;
                                countBadge.textContent = newCount;
                                const text = countBadge.parentElement;
                                text.innerHTML = `<span class="count-badge">${newCount}</span> activité${newCount > 1 ? 's' : ''} en favori`;
                            }
                        }
                    }, 300);
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de connexion. Vérifiez la console pour plus de détails.');
            });
        });
    });
});
</script>

<?php include '../includes/footer.php';
