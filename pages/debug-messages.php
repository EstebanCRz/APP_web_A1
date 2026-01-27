<?php
require_once '../includes/session.php';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Non connecté");
}

$user_id = $_SESSION['user_id'];
$pdo = getDB();

echo "<h1>Debug Messages - User ID: $user_id</h1>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#55D5E0;color:white;} h2{color:#335F8A;margin-top:30px;}</style>";

// 1. Activités auxquelles l'utilisateur participe
echo "<h2>1. Activités où je participe</h2>";
$stmt = $pdo->prepare("
    SELECT ap.*, a.title, a.creator_id 
    FROM activity_participants ap
    JOIN activities a ON ap.activity_id = a.id
    WHERE ap.user_id = ?
");
$stmt->execute([$user_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($activities) {
    echo "<table><tr><th>Activity ID</th><th>Title</th><th>Creator ID</th><th>Registered At</th></tr>";
    foreach ($activities as $act) {
        echo "<tr><td>{$act['activity_id']}</td><td>{$act['title']}</td><td>{$act['creator_id']}</td><td>{$act['registered_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Aucune participation</p>";
}

// 2. Groupes existants pour ces activités
echo "<h2>2. Groupes liés à ces activités</h2>";
if ($activities) {
    $activityIds = array_column($activities, 'activity_id');
    $placeholders = str_repeat('?,', count($activityIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM groups WHERE activity_id IN ($placeholders)");
    $stmt->execute($activityIds);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($groups) {
        echo "<table><tr><th>Group ID</th><th>Name</th><th>Activity ID</th><th>Created By</th></tr>";
        foreach ($groups as $grp) {
            echo "<tr><td>{$grp['id']}</td><td>{$grp['name']}</td><td>{$grp['activity_id']}</td><td>{$grp['created_by']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Aucun groupe créé pour ces activités</p>";
    }
}

// 3. Invitations en attente pour l'utilisateur
echo "<h2>3. Invitations en attente</h2>";
$stmt = $pdo->prepare("
    SELECT gi.*, g.name as group_name, a.title as activity_title
    FROM group_invitations gi
    JOIN groups g ON gi.group_id = g.id
    LEFT JOIN activities a ON g.activity_id = a.id
    WHERE gi.user_id = ? AND gi.status = 'pending'
");
$stmt->execute([$user_id]);
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($invitations) {
    echo "<table><tr><th>Invitation ID</th><th>Group Name</th><th>Activity</th><th>Status</th><th>Created At</th></tr>";
    foreach ($invitations as $inv) {
        echo "<tr><td>{$inv['id']}</td><td>{$inv['group_name']}</td><td>{$inv['activity_title']}</td><td>{$inv['status']}</td><td>{$inv['created_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>⚠️ Aucune invitation en attente</p>";
}

// 4. Groupes dont je suis membre
echo "<h2>4. Groupes dont je suis membre</h2>";
$stmt = $pdo->prepare("
    SELECT gm.*, g.name, g.activity_id
    FROM group_members gm
    JOIN groups g ON gm.group_id = g.id
    WHERE gm.user_id = ?
");
$stmt->execute([$user_id]);
$memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($memberships) {
    echo "<table><tr><th>Group ID</th><th>Name</th><th>Activity ID</th><th>Role</th><th>Joined At</th></tr>";
    foreach ($memberships as $mem) {
        echo "<tr><td>{$mem['group_id']}</td><td>{$mem['name']}</td><td>{$mem['activity_id']}</td><td>{$mem['role']}</td><td>{$mem['joined_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Membre d'aucun groupe</p>";
}

// 5. Conversations privées
echo "<h2>5. Conversations privées</h2>";
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN user1_id = ? THEN user2_id
            ELSE user1_id
        END as other_user_id,
        u.first_name, u.last_name
    FROM private_conversations pc
    LEFT JOIN users u ON (
        CASE 
            WHEN pc.user1_id = ? THEN pc.user2_id
            ELSE pc.user1_id
        END = u.id
    )
    WHERE user1_id = ? OR user2_id = ?
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($conversations) {
    echo "<table><tr><th>Other User ID</th><th>Name</th></tr>";
    foreach ($conversations as $conv) {
        echo "<tr><td>{$conv['other_user_id']}</td><td>{$conv['first_name']} {$conv['last_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Aucune conversation privée</p>";
}

echo "<hr><p><a href='messages.php'>← Retour aux messages</a></p>";
?>
