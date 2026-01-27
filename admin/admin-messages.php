<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../includes/admin_functions.php';

// Vérifier que l'utilisateur est admin
requireAdmin();

$pageTitle = "Gestion des Messages - Admin";
$assetsDepth = 1;
$customCSS = ["css/admin-dashboard.css"];

include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1> Gestion des Messages</h1>
    </div>

    <div class="admin-nav">
        <a href="admin-dashboard.php"> Dashboard</a>
        <a href="admin-users.php"> Utilisateurs</a>
        <a href="admin-events.php"> Événements</a>
        <a href="admin-forum.php"> Forum</a>
        <a href="admin-messages.php" class="active"> Messages</a>
        <a href="admin-content.php"> Contenu</a>
    </div>

    <div class="admin-section">
        <h2>📧 Messages de Contact</h2>
        
        <?php
        $pdo = getDB();
        
        // Récupérer tous les messages de contact
        $stmt = $pdo->query("
            SELECT * FROM contact_messages 
            ORDER BY created_at DESC
        ");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $unread = array_filter($messages, fn($m) => $m['read_status'] == 0);
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📬</div>
                <div class="stat-value"><?php echo count($messages); ?></div>
                <div class="stat-label">Messages totaux</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <div class="stat-value"><?php echo count($unread); ?></div>
                <div class="stat-label">Non lus</div>
            </div>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Statut</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Sujet</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr style="<?php echo $msg['read_status'] == 0 ? 'background: #fff3cd;' : ''; ?>">
                    <td>#<?php echo $msg['id']; ?></td>
                    <td>
                        <?php if ($msg['read_status'] == 0): ?>
                            <span class="badge badge-warning">Non lu</span>
                        <?php else: ?>
                            <span class="badge badge-success">Lu</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                    <td><strong><?php echo htmlspecialchars($msg['subject']); ?></strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></td>
                    <td>
                        <button onclick="viewMessage(<?php echo $msg['id']; ?>)" class="btn btn-sm btn-info">👁️ Voir</button>
                        <?php if ($msg['read_status'] == 0): ?>
                        <button onclick="markAsRead(<?php echo $msg['id']; ?>)" class="btn btn-sm btn-success">✓ Marquer lu</button>
                        <?php endif; ?>
                        <button onclick="deleteMessage(<?php echo $msg['id']; ?>)" class="btn btn-sm btn-danger">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #999;">
                        Aucun message de contact
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pour voir le message -->
<div id="messageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:12px; max-width:600px; width:90%; max-height:80vh; overflow-y:auto;">
        <div id="messageContent"></div>
        <button onclick="document.getElementById('messageModal').style.display='none'" class="btn btn-secondary" style="margin-top:1rem;">Fermer</button>
    </div>
</div>

<script>
function viewMessage(id) {
    fetch(`api/get-message.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const msg = data.message;
                document.getElementById('messageContent').innerHTML = `
                    <h3>${msg.subject}</h3>
                    <p><strong>De:</strong> ${msg.name} (${msg.email})</p>
                    <p><strong>Date:</strong> ${new Date(msg.created_at).toLocaleString('fr-FR')}</p>
                    <hr>
                    <div style="white-space: pre-wrap; line-height: 1.6;">${msg.message}</div>
                `;
                document.getElementById('messageModal').style.display='flex';
            }
        });
}

function markAsRead(id) {
    fetch(`api/mark-message-read.php?id=${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
}

function deleteMessage(id) {
    if (!confirm('Supprimer ce message ?')) return;
    fetch(`api/delete-message.php?id=${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
}
</script>

<?php include '../includes/footer.php';