<?php
// Configuration de session (AVANT session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/activities_functions.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$pageTitle = "Mon Profil - AmiGo";
$pageDescription = "Gérez votre profil AmiGo";
$assetsDepth = 1;
$customCSS = [
    "../assets/css/style.css",
    "css/profile.css"
];

// Récupérer les activités créées par l'utilisateur
$myActivities = getUserCreatedActivities($_SESSION['user_id']);

// Récupérer les activités auxquelles l'utilisateur est inscrit
$registeredActivities = getUserRegisteredActivities($_SESSION['user_id']);

include '../includes/header.php';
?>

<div class="container">
    <section class="profile-header">
        <h2>Mon Profil</h2>
        <p>Bienvenue <?php echo htmlspecialchars($_SESSION['user_first_name'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['user_last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> !</p>
        
        <div class="profile-tabs">
            <button class="tab-btn btn btn-primary active" data-tab="modifier">Modifier mon profil</button>
            <button class="tab-btn btn btn-secondary" data-tab="created">Événements créés (<?php echo count($myActivities); ?>)</button>
            <button class="tab-btn btn btn-secondary" data-tab="registered">Événements inscrits (<?php echo count($registeredActivities); ?>)</button>
            <a href="../auth/login.php?logout=1" class="btn btn-secondary">Déconnexion</a>
        </div>
    </section>

    <!-- Onglet Modifier profil -->
    <section class="tab-content active" id="tab-modifier">
        <div style="padding: 2rem; background: #f9f9f9; border-radius: 8px; text-align: center;">
            <h3>Modifier mon profil</h3>
            <p>Cette fonctionnalité sera bientôt disponible.</p>
            <a href="profile-edit.php" class="btn btn-primary">Accéder aux paramètres</a>
        </div>
    </section>

    <!-- Onglet Mes activités créées -->
    <section class="tab-content" id="tab-created">
        <h3>Mes activités créées</h3>
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
</script>

<?php include '../includes/footer.php'; ?>
