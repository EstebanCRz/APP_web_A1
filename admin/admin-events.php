<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

// Gérer la suppression d'une activité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_activity'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM activities WHERE id = ?');
        $stmt->execute([$_POST['activity_id']]);
        $successMsg = "Activité supprimée avec succès.";
    } catch (Exception $e) {
        $errorMsg = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Récupérer les statistiques et activités
$search = $_GET['search'] ?? '';
try {
    // Statistiques par catégorie
    $stmt = $pdo->prepare('
        SELECT ac.name as category, COUNT(a.id) as total
        FROM activity_categories ac
        LEFT JOIN activities a ON ac.id = a.category_id
        GROUP BY ac.id, ac.name
        ORDER BY total DESC
    ');
    $stmt->execute();
    $statsByCategory = $stmt->fetchAll();

    // Statistiques par date (30 derniers jours)
    $stmt = $pdo->prepare('
        SELECT DATE(created_at) as date, COUNT(*) as total
        FROM activities
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ');
    $stmt->execute();
    $statsByDate = $stmt->fetchAll();

    // Liste des activités
    if ($search) {
        $stmt = $pdo->prepare('
            SELECT a.*, ac.name as category_name, u.username as creator_name
            FROM activities a
            LEFT JOIN activity_categories ac ON a.category_id = ac.id
            LEFT JOIN users u ON a.creator_id = u.id
            WHERE a.title LIKE ? OR a.description LIKE ?
            ORDER BY a.created_at DESC
        ');
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->prepare('
            SELECT a.*, ac.name as category_name, u.username as creator_name
            FROM activities a
            LEFT JOIN activity_categories ac ON a.category_id = ac.id
            LEFT JOIN users u ON a.creator_id = u.id
            ORDER BY a.created_at DESC
        ');
        $stmt->execute();
    }
    $activities = $stmt->fetchAll();
} catch (Exception $e) {
    $activities = [];
    $statsByCategory = [];
    $statsByDate = [];
    $errorMsg = "Erreur de chargement: " . $e->getMessage();
}

$pageTitle = "Gestion des Événements - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>🎉 Gestion des Événements</h1>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php">📊 Dashboard</a>
        <a href="admin-users.php">👥 Utilisateurs</a>
        <a href="admin-events.php" class="active">🎉 Événements</a>
        <a href="admin-forum.php">💬 Forum</a>
        <a href="admin-messages.php">✉️ Messages</a>
        <a href="admin-content.php">📝 Contenu</a>
    </div>

    <?php if (isset($successMsg)): ?>
        <div class="alert alert-success"><?php echo $successMsg; ?></div>
    <?php endif; ?>
    <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="admin-section">
            <h3>📊 Activités par catégorie</h3>
            <div style="margin-top: 1rem;">
                <?php foreach ($statsByCategory as $stat): ?>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; border-bottom: 1px solid #e9ecef;">
                        <strong><?php echo htmlspecialchars($stat['category']); ?></strong>
                        <span style="background: #667eea; color: white; padding: 0.25rem 0.75rem; border-radius: 20px;"><?php echo $stat['total']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="admin-section">
            <h3>📅 Créations (30 derniers jours)</h3>
            <div style="margin-top: 1rem;">
                <?php foreach (array_slice($statsByDate, 0, 10) as $stat): ?>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; border-bottom: 1px solid #e9ecef;">
                        <strong><?php echo date('d/m/Y', strtotime($stat['date'])); ?></strong>
                        <span style="background: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 20px;">+<?php echo $stat['total']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="admin-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Liste des activités (<?php echo count($activities); ?>)</h2>
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" style="padding: 0.5rem 1rem; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">🔍</button>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">ID</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Titre</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Catégorie</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Créateur</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Date</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Participants</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 1rem;"><?php echo $activity['id']; ?></td>
                            <td style="padding: 1rem;">
                                <strong><?php echo htmlspecialchars($activity['title']); ?></strong><br>
                                <small style="color: #6c757d;"><?php echo htmlspecialchars(substr($activity['description'], 0, 50)); ?>...</small>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="background: #e9ecef; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($activity['category_name']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;"><?php echo htmlspecialchars($activity['creator_name']); ?></td>
                            <td style="padding: 1rem;"><?php echo date('d/m/Y H:i', strtotime($activity['event_date'])); ?></td>
                            <td style="padding: 1rem;">
                                <?php
                                try {
                                    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM activity_registrations WHERE activity_id = ?');
                                    $stmt->execute([$activity['id']]);
                                    $participants = $stmt->fetch()['count'];
                                    echo "$participants / " . $activity['max_participants'];
                                } catch (Exception $e) {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="../events/event-details.php?id=<?php echo $activity['id']; ?>" target="_blank" style="padding: 0.5rem 1rem; background: #17a2b8; color: white; border: none; border-radius: 5px; text-decoration: none; font-size: 0.875rem;">Voir</a>
                                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette activité ?');" style="display: inline;">
                                        <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                        <input type="hidden" name="delete_activity" value="1">
                                        <button type="submit" style="padding: 0.5rem 1rem; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.875rem;">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php';
