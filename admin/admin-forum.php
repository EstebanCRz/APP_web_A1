<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$pageTitle = "Gestion du Forum - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1> Gestion du Forum</h1>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php"> Dashboard</a>
        <a href="admin-users.php"> Utilisateurs</a>
        <a href="admin-events.php"> Événements</a>
        <a href="admin-forum.php" class="active"> Forum</a>
        <a href="admin-messages.php"> Messages</a>
        <a href="admin-content.php"> Contenu</a>
    </div>

    <div class="admin-section">
        <h2>📋 Sujets du Forum</h2>
        
        <?php
        $pdo = getDB();
        
        // Récupérer tous les topics avec stats
        $stmt = $pdo->query("
            SELECT 
                ft.id,
                ft.title,
                ft.created_at,
                u.first_name,
                u.last_name,
                COUNT(DISTINCT fp.id) as post_count,
                MAX(fp.created_at) as last_activity
            FROM forum_topics ft
            JOIN users u ON ft.author_id = u.id
            LEFT JOIN forum_posts fp ON ft.id = fp.topic_id
            GROUP BY ft.id
            ORDER BY last_activity DESC
        ");
        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-value"><?php echo count($topics); ?></div>
                <div class="stat-label">Sujets totaux</div>
            </div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Réponses</th>
                    <th>Créé le</th>
                    <th>Dernière activité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topics as $topic): ?>
                <tr>
                    <td>#<?php echo $topic['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($topic['title']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($topic['first_name'] . ' ' . $topic['last_name']); ?></td>
                    <td><?php echo $topic['post_count']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($topic['created_at'])); ?></td>
                    <td><?php echo $topic['last_activity'] ? date('d/m/Y H:i', strtotime($topic['last_activity'])) : '-'; ?></td>
                    <td>
                        <a href="../pages/forum-topic.php?id=<?php echo $topic['id']; ?>" class="btn btn-sm btn-info" target="_blank">👁️ Voir</a>
                        <button onclick="deleteTopic(<?php echo $topic['id']; ?>)" class="btn btn-sm btn-danger">🗑️ Supprimer</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($topics)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun sujet de forum pour le moment
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteTopic(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce sujet et tous ses posts ?')) return;
    
    fetch(`api/delete-topic.php?id=${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
}
</script>

<?php include '../includes/footer.php';