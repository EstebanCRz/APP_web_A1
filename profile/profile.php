<?php
// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/language.php';
require_once '../includes/activities_functions.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = t('profile.my_profile') . " - AmiGo";
$pageDescription = t('profile.my_profile');
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

// Récupérer les activités créées par l'utilisateur
$myActivities = getUserCreatedActivities($_SESSION['user_id']);

// Récupérer les activités auxquelles l'utilisateur est inscrit
$registeredActivities = getUserRegisteredActivities($_SESSION['user_id']);

// Récupérer les activités favorites de l'utilisateur
$favoriteActivities = [];
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.title,
            a.description,
            a.location,
            a.city,
            a.event_date,
            a.event_time,
            a.max_participants,
            a.current_participants,
            a.image,
            c.name as category_name,
            c.color as category_color,
            u.first_name as creator_first_name,
            u.last_name as creator_last_name,
            u.username as creator_username
        FROM user_favorites f
        INNER JOIN activities a ON f.activity_id = a.id
        INNER JOIN activity_categories c ON a.category_id = c.id
        INNER JOIN users u ON a.creator_id = u.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $favoriteActivities = $stmt->fetchAll();
} catch (PDOException $e) {
    $favoriteActivities = [];
}

include '../includes/header.php';
?>

<div class="container">
    <section class="profile-header">
        <h2><?php echo t('profile.my_profile'); ?></h2>
        <p><?php echo t('profile.welcome'); ?> <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['user_last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> !</p>
        
        <div class="profile-tabs">
            <button class="tab-btn btn btn-primary active" data-tab="modifier"><?php echo t('profile.edit'); ?></button>
            <button class="tab-btn btn btn-secondary" data-tab="favorites">Favoris (<?php echo count($favoriteActivities); ?>)</button>
            <button class="tab-btn btn btn-secondary" data-tab="created"><?php echo t('profile.created_events'); ?> (<?php echo count($myActivities); ?>)</button>
            <button class="tab-btn btn btn-secondary" data-tab="registered"><?php echo t('profile.registered_events'); ?> (<?php echo count($registeredActivities); ?>)</button>
            <a href="../auth/login.php?logout=1" class="btn btn-secondary"><?php echo t('profile.logout'); ?></a>
        </div>
    </section>

    <!-- Onglet Modifier profil -->
    <section class="tab-content active" id="tab-modifier">
        <div style="padding: 2rem; background: #f9f9f9; border-radius: 8px; text-align: center;">
            <h3><?php echo t('profile.edit'); ?></h3>
            <p><?php echo t('profile.upcoming_feature'); ?></p>
            <a href="profile-edit.php" class="btn btn-primary"><?php echo t('profile.access_settings'); ?></a>
        </div>
    </section>

    <!-- Onglet Favoris -->
    <section class="tab-content" id="tab-favorites">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3> Mes Favoris</h3>
            <a href="profile-favorites.php" class="btn btn-primary">Voir tous mes favoris</a>
        </div>
        <?php if (empty($favoriteActivities)): ?>
            <div style="padding: 3rem 2rem; background: #f9f9f9; border-radius: 12px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">💔</div>
                <h4>Aucune activité favorite</h4>
                <p style="color: #666; margin: 1rem 0 1.5rem;">Ajoutez des activités à vos favoris en cliquant sur ❤️</p>
                <a href="../events/events-list.php" class="btn btn-secondary">Découvrir les activités</a>
            </div>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($favoriteActivities as $activity): ?>
                    <div class="event-card-wrapper" data-activity-id="<?php echo $activity['id']; ?>">
                        <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" class="event-card">
                            <div class="event-banner" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center; height: 150px; position: relative;">
                                <button 
                                    class="favorite-remove-btn" 
                                    data-activity-id="<?php echo $activity['id']; ?>"
                                    onclick="event.preventDefault(); event.stopPropagation(); removeFavoriteFromProfile(this);"
                                    title="Retirer des favoris"
                                >
                                    <span class="heart-icon">❤️</span>
                                    <span class="remove-icon">✕</span>
                                </button>
                            </div>
                            <div class="event-info">
                                <span class="badge" style="background: <?php echo htmlspecialchars($activity['category_color'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($activity['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <h4 class="event-title"><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p class="event-details">📅 <?php echo formatEventDate($activity['event_date']); ?> - <?php echo formatEventTime($activity['event_time']); ?></p>
                                <p class="event-details">📍 <?php echo htmlspecialchars($activity['location'] . ', ' . $activity['city'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="event-details">👥 <?php echo $activity['current_participants']; ?>/<?php echo $activity['max_participants']; ?> participants</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($favoriteActivities) >= 6): ?>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="profile-favorites.php" class="btn btn-secondary">Voir tous mes favoris →</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Onglet Mes activités créées -->
    <section class="tab-content" id="tab-created">
        <h3><?php echo t('profile.my_events'); ?></h3>
        <?php if (empty($myActivities)): ?>
            <p>Vous n'avez pas encore créé d'activité. <a href="../events/event-create.php">Créer une activité</a></p>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($myActivities as $activity): ?>
                    <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" class="event-card">
                        <div class="event-banner" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center; height: 150px;"></div>
                        <div class="event-info">
                            <span class="badge" style="background: <?php echo htmlspecialchars($activity['category_color'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($activity['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h4 class="event-title"><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="event-details">📅 <?php echo formatEventDate($activity['event_date']); ?> - <?php echo formatEventTime($activity['event_time']); ?></p>
                            <p class="event-details">📍 <?php echo htmlspecialchars($activity['location'] . ', ' . $activity['city'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="event-details">👥 <?php echo $activity['current_participants']; ?>/<?php echo $activity['max_participants']; ?> participants</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Onglet Activités inscrites -->
    <section class="tab-content" id="tab-registered">
        <h3>Activités auxquelles je participe</h3>
        <?php if (empty($registeredActivities)): ?>
            <p>Vous n'êtes inscrit à aucune activité pour le moment. <a href="../events/events-list.php">Découvrir les activités</a></p>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($registeredActivities as $activity): ?>
                    <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" class="event-card">
                        <div class="event-banner" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'); background-size: cover; background-position: center; height: 150px;"></div>
                        <div class="event-info">
                            <span class="badge" style="background: <?php echo htmlspecialchars($activity['category_color'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($activity['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h4 class="event-title"><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="event-details">📅 <?php echo formatEventDate($activity['event_date']); ?> - <?php echo formatEventTime($activity['event_time']); ?></p>
                            <p class="event-details">📍 <?php echo htmlspecialchars($activity['location'] . ', ' . $activity['city'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="event-details">👤 Organisé par <?php echo htmlspecialchars($activity['creator_first_name'] ?? $activity['creator_username'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
// Gestion des onglets
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Retirer la classe active de tous les boutons
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-secondary');
            });
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-secondary');
            
            // Cacher tous les contenus
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Afficher le contenu correspondant
            const targetContent = document.getElementById('tab-' + targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
});

// Fonction pour retirer un favori depuis le profil
function removeFavoriteFromProfile(button) {
    const activityId = button.dataset.activityId;
    const cardWrapper = button.closest('.event-card-wrapper');
    
    if (!confirm('Retirer cette activité de vos favoris ?')) {
        return;
    }
    
    fetch('../events/api/favorite-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `activity_id=${activityId}&action=remove`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animation de suppression
            cardWrapper.style.opacity = '0';
            cardWrapper.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                cardWrapper.remove();
                
                // Vérifier s'il reste des favoris
                const remainingCards = document.querySelectorAll('#tab-favorites .event-card-wrapper').length;
                if (remainingCards === 0) {
                    location.reload();
                } else {
                    // Mettre à jour le compteur dans l'onglet
                    const favoritesTab = document.querySelector('[data-tab="favorites"]');
                    if (favoritesTab) {
                        const match = favoritesTab.textContent.match(/\((\d+)\)/);
                        if (match) {
                            const newCount = parseInt(match[1]) - 1;
                            favoritesTab.innerHTML = favoritesTab.innerHTML.replace(/\(\d+\)/, `(${newCount})`);
                        }
                    }
                }
            }, 300);
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    });
}
</script>

<style>
.event-card-wrapper {
    transition: all 0.3s ease;
}

.favorite-remove-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    z-index: 10;
}

.favorite-remove-btn .heart-icon {
    font-size: 1.3rem;
    position: absolute;
    transition: all 0.3s ease;
}

.favorite-remove-btn .remove-icon {
    font-size: 1.5rem;
    color: #e74c3c;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
    font-weight: bold;
}

.favorite-remove-btn:hover {
    background: #fee;
    transform: scale(1.1);
}

.favorite-remove-btn:hover .heart-icon {
    opacity: 0;
    transform: scale(0);
}

.favorite-remove-btn:hover .remove-icon {
    opacity: 1;
    transform: scale(1);
}
</style>

<?php include '../includes/footer.php'; ?>
