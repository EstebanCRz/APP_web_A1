<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

// Gérer les actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ban_user'])) {
        try {
            $stmt = $pdo->prepare('UPDATE users SET banned = 1 WHERE id = ? AND role != "admin"');
            $stmt->execute([$_POST['user_id']]);
            $successMsg = "Utilisateur banni avec succès.";
        } catch (Exception $e) {
            $errorMsg = "Erreur: " . $e->getMessage();
        }
    } elseif (isset($_POST['unban_user'])) {
        try {
            $stmt = $pdo->prepare('UPDATE users SET banned = 0 WHERE id = ?');
            $stmt->execute([$_POST['user_id']]);
            $successMsg = "Utilisateur débanni avec succès.";
        } catch (Exception $e) {
            $errorMsg = "Erreur: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        try {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
            $stmt->execute([$_POST['user_id']]);
            $successMsg = "Utilisateur supprimé avec succès.";
        } catch (Exception $e) {
            $errorMsg = "Erreur: " . $e->getMessage();
        }
    }
}

// Récupérer la liste des utilisateurs
$search = $_GET['search'] ?? '';
try {
    if ($search) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC');
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
    }
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
    $errorMsg = "Erreur de chargement: " . $e->getMessage();
}

$pageTitle = "Gestion des Utilisateurs - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>👥 Gestion des Utilisateurs</h1>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php">📊 Dashboard</a>
        <a href="admin-users.php" class="active">👥 Utilisateurs</a>
        <a href="admin-events.php">🎉 Événements</a>
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

    <div class="admin-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Liste des utilisateurs (<?php echo count($users); ?>)</h2>
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" style="padding: 0.5rem 1rem; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">🔍</button>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-success">User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($user['banned']) && $user['banned']): ?>
                                    <span class="badge badge-danger">Banni</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Actif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="../profile/profile-other.php?id=<?php echo $user['id']; ?>" target="_blank" class="btn btn-info">Voir</a>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <?php if (isset($user['banned']) && $user['banned']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="unban_user" value="1">
                                                <button type="submit" class="btn btn-success">Débannir</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" onsubmit="return confirm('Bannir cet utilisateur ?');" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="ban_user" value="1">
                                                <button type="submit" class="btn btn-danger">Bannir</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="delete_user" value="1">
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                        </form>
                                    <?php endif; ?>
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
