<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$pageTitle = "Gestion du Contenu - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1> Gestion du Contenu</h1>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php"> Dashboard</a>
        <a href="admin-users.php"> Utilisateurs</a>
        <a href="admin-events.php"> Événements</a>
        <a href="admin-forum.php"> Forum</a>
        <a href="admin-messages.php"> Messages</a>
        <a href="admin-content.php" class="active"> Contenu</a>
    </div>

    <div class="admin-section">
        <h2>📝 Gestion du Contenu</h2>
        
        <?php
        $pdo = getDB();
        
        // Statistiques
        $statsActivities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
        $statsCategories = $pdo->query("SELECT COUNT(DISTINCT category) FROM user_interest_categories")->fetchColumn();
        $statsCities = $pdo->query("SELECT COUNT(*) FROM cities")->fetchColumn();
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-value"><?php echo $statsActivities; ?></div>
                <div class="stat-label">Activités</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📂</div>
                <div class="stat-value"><?php echo $statsCategories; ?></div>
                <div class="stat-label">Catégories</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏙️</div>
                <div class="stat-value"><?php echo $statsCities; ?></div>
                <div class="stat-label">Villes</div>
            </div>
        </div>

        <!-- Catégories d'intérêts -->
        <h3 style="margin-top:2rem;">📂 Catégories d'Intérêts</h3>
        <?php
        $categories = $pdo->query("
            SELECT category, COUNT(*) as count 
            FROM user_interest_categories 
            GROUP BY category 
            ORDER BY category
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Icône</th>
                    <th>Catégorie</th>
                    <th>Nombre d'intérêts</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td>
                        <?php
                        $icons = ['Sport' => '⚽', 'Culture' => '🎨', 'Nature' => '🌿', 'Gastronomie' => '🍽️', 'Aventure' => '🏔️'];
                        echo $icons[$cat['category']] ?? '📌';
                        ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($cat['category']); ?></strong></td>
                    <td><?php echo $cat['count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Villes les plus populaires -->
        <h3 style="margin-top:2rem;">🏙️ Villes</h3>
        <?php
        $cities = $pdo->query("
            SELECT c.*, COUNT(u.id) as user_count
            FROM cities c
            LEFT JOIN users u ON u.city = c.name
            GROUP BY c.id
            ORDER BY user_count DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ville</th>
                    <th>Code Postal</th>
                    <th>Utilisateurs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cities as $city): ?>
                <tr>
                    <td><?php echo $city['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($city['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($city['postal_code']); ?></td>
                    <td><?php echo $city['user_count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Activités récentes -->
        <h3 style="margin-top:2rem;">🎯 Dernières Activités Créées</h3>
        <?php
        $activities = $pdo->query("
            SELECT a.*, u.first_name, u.last_name
            FROM activities a
            JOIN users u ON a.organizer_id = u.id
            ORDER BY a.created_at DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Organisateur</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $act): ?>
                <tr>
                    <td>#<?php echo $act['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($act['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($act['category']); ?></td>
                    <td><?php echo htmlspecialchars($act['first_name'] . ' ' . $act['last_name']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($act['created_at'])); ?></td>
                    <td>
                        <a href="/events/event-details.php?id=<?php echo $act['id']; ?>" class="btn btn-sm btn-info" target="_blank">👁️ Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php';