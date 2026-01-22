<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';
require_once '../includes/language.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

// Récupérer les statistiques
try {
    $pdo = getDB();
    
    // Stats utilisateurs
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM users');
    $totalUsers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()');
    $newUsersToday = $stmt->fetch()['total'];
    
    // Stats activités
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM activities');
    $totalActivities = $stmt->fetch()['total'];
    
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM activities WHERE DATE(created_at) = CURDATE()');
    $newActivitiesToday = $stmt->fetch()['total'];
    
    // Stats inscriptions
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM activity_registrations');
    $totalRegistrations = $stmt->fetch()['total'];
    
    // Stats messages forum
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM forum_posts');
    $totalForumPosts = $stmt->fetch()['total'] ?? 0;
    
} catch (Exception $e) {
    error_log("Erreur stats admin: " . $e->getMessage());
    $totalUsers = $newUsersToday = $totalActivities = $newActivitiesToday = $totalRegistrations = $totalForumPosts = 0;
}

$pageTitle = "Tableau de bord Admin - AmiGo";
$pageDescription = "Administration de la plateforme AmiGo";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>🎛️ Tableau de bord Admin</h1>
        <p>Bienvenue dans l'interface d'administration AmiGo</p>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php" class="active">📊 Dashboard</a>
        <a href="admin-users.php">👥 Utilisateurs</a>
        <a href="admin-events.php">🎉 Événements</a>
        <a href="admin-forum.php">💬 Forum</a>
        <a href="admin-messages.php">✉️ Messages</a>
        <a href="admin-content.php">📝 Contenu</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo number_format($totalUsers); ?></h3>
                <p>Utilisateurs</p>
                <span class="stat-badge">+<?php echo $newUsersToday; ?> aujourd'hui</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎉</div>
            <div class="stat-info">
                <h3><?php echo number_format($totalActivities); ?></h3>
                <p>Événements</p>
                <span class="stat-badge">+<?php echo $newActivitiesToday; ?> aujourd'hui</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo number_format($totalRegistrations); ?></h3>
                <p>Inscriptions</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-info">
                <h3><?php echo number_format($totalForumPosts); ?></h3>
                <p>Messages Forum</p>
            </div>
        </div>
    </div>

    <div class="admin-sections">
        <div class="admin-section">
            <h2>Actions rapides</h2>
            <div class="quick-actions">
                <a href="admin-users.php" class="action-btn">
                    <span>👥</span>
                    Gérer les utilisateurs
                </a>
                <a href="admin-events.php" class="action-btn">
                    <span>🎉</span>
                    Gérer les événements
                </a>
                <a href="admin-forum.php" class="action-btn">
                    <span>💬</span>
                    Modérer le forum
                </a>
                <a href="admin-messages.php?tab=contact" class="action-btn">
                    <span>✉️</span>
                    Messages de contact
                </a>
            </div>
        </div>

        <div class="admin-section">
            <h2>Activité récente</h2>
            <?php
            try {
                $stmt = $pdo->query('
                    SELECT u.username, u.created_at, "Nouvel utilisateur" as type
                    FROM users u
                    ORDER BY u.created_at DESC
                    LIMIT 5
                ');
                $recentActivity = $stmt->fetchAll();
                
                if ($recentActivity): ?>
                    <ul class="activity-list">
                        <?php foreach ($recentActivity as $activity): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($activity['username']); ?></strong>
                                <span><?php echo $activity['type']; ?></span>
                                <time><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></time>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucune activité récente</p>
                <?php endif;
            } catch (Exception $e) {
                echo '<p>Erreur de chargement</p>';
            }
            ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php';
