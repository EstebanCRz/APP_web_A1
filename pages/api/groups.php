<?php
session_start();
require_once '../../includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

try {
    // GET - Récupérer les groupes ou un groupe spécifique
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Détails d'un groupe
            $group_id = (int)$_GET['id'];
            
            // Vérifier que l'utilisateur est membre
            $stmt = $pdo->prepare("
                SELECT * FROM group_members 
                WHERE group_id = ? AND user_id = ?
            ");
            $stmt->execute([$group_id, $user_id]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Accès refusé');
            }
            
            // Récupérer le groupe
            $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = ?");
            $stmt->execute([$group_id]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Récupérer les messages
            $stmt = $pdo->prepare("
                SELECT gm.*, u.username 
                FROM group_messages gm
                JOIN users u ON gm.user_id = u.id
                WHERE gm.group_id = ?
                ORDER BY gm.created_at ASC
            ");
            $stmt->execute([$group_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les membres
            $stmt = $pdo->prepare("
                SELECT gm.*, u.username 
                FROM group_members gm
                JOIN users u ON gm.user_id = u.id
                WHERE gm.group_id = ?
                ORDER BY gm.role DESC, u.username ASC
            ");
            $stmt->execute([$group_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'group' => $group,
                'messages' => $messages,
                'members' => $members,
                'current_user_id' => $user_id
            ]);
            
        } else {
            // Liste des groupes de l'utilisateur avec invitations
            if (isset($_GET['action']) && $_GET['action'] === 'invitations') {
                // Récupérer les invitations en attente
                $stmt = $pdo->prepare("
                    SELECT gi.*, g.name as group_name, g.description, 
                           u.username as invited_by_name, a.title as activity_title
                    FROM group_invitations gi
                    JOIN groups g ON gi.group_id = g.id
                    LEFT JOIN activities a ON g.activity_id = a.id
                    JOIN users u ON gi.invited_by = u.id
                    WHERE gi.user_id = ? AND gi.status = 'pending'
                    ORDER BY gi.created_at DESC
                ");
                $stmt->execute([$user_id]);
                $invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'invitations' => $invitations]);
                exit;
            }
            
            // Liste des groupes de l'utilisateur
            $stmt = $pdo->prepare("
                SELECT g.*, COUNT(DISTINCT gm2.user_id) as member_count,
                       a.title as activity_title
                FROM groups g
                JOIN group_members gm ON g.id = gm.group_id
                LEFT JOIN group_members gm2 ON g.id = gm2.group_id
                LEFT JOIN activities a ON g.activity_id = a.id
                WHERE gm.user_id = ?
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'groups' => $groups]);
        }
        exit;
    }
    
    // POST - Créer un groupe ou envoyer un message
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        
        if ($action === 'create_group') {
            $name = trim($data['name'] ?? '');
            $description = trim($data['description'] ?? '');
            
            if (empty($name)) {
                throw new Exception('Le nom du groupe est requis');
            }
            
            $pdo->beginTransaction();
            
            // Créer le groupe
            $stmt = $pdo->prepare("
                INSERT INTO groups (name, description, created_by)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$name, $description, $user_id]);
            $group_id = $pdo->lastInsertId();
            
            // Ajouter le créateur comme admin
            $stmt = $pdo->prepare("
                INSERT INTO group_members (group_id, user_id, role)
                VALUES (?, ?, 'admin')
            ");
            $stmt->execute([$group_id, $user_id]);
            
            $pdo->commit();
            
            echo json_encode(['success' => true, 'group_id' => $group_id]);
            
        } elseif ($action === 'send_message') {
            $group_id = (int)($data['group_id'] ?? 0);
            $message = trim($data['message'] ?? '');
            $image_path = $data['image_path'] ?? null;
            
            if (empty($message) && empty($image_path)) {
                throw new Exception('Le message est vide');
            }
            
            // Vérifier que l'utilisateur est membre
            $stmt = $pdo->prepare("
                SELECT id FROM group_members 
                WHERE group_id = ? AND user_id = ?
            ");
            $stmt->execute([$group_id, $user_id]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Accès refusé');
            }
            
            // Envoyer le message
            $stmt = $pdo->prepare("
                INSERT INTO group_messages (group_id, user_id, message, image_path)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$group_id, $user_id, $message, $image_path]);
            
            echo json_encode(['success' => true]);
            
        } elseif ($action === 'accept_invitation') {
            $invitation_id = (int)($data['invitation_id'] ?? 0);
            
            // Récupérer l'invitation
            $stmt = $pdo->prepare("
                SELECT * FROM group_invitations
                WHERE id = ? AND user_id = ? AND status = 'pending'
            ");
            $stmt->execute([$invitation_id, $user_id]);
            $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invitation) {
                throw new Exception('Invitation non trouvée');
            }
            
            $pdo->beginTransaction();
            
            // Ajouter comme membre
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO group_members (group_id, user_id, role)
                VALUES (?, ?, 'member')
            ");
            $stmt->execute([$invitation['group_id'], $user_id]);
            
            // Mettre à jour l'invitation
            $stmt = $pdo->prepare("
                UPDATE group_invitations
                SET status = 'accepted'
                WHERE id = ?
            ");
            $stmt->execute([$invitation_id]);
            
            $pdo->commit();
            
            echo json_encode(['success' => true]);
            
        } elseif ($action === 'decline_invitation') {
            $invitation_id = (int)($data['invitation_id'] ?? 0);
            
            // Mettre à jour l'invitation
            $stmt = $pdo->prepare("
                UPDATE group_invitations
                SET status = 'declined'
                WHERE id = ? AND user_id = ? AND status = 'pending'
            ");
            $stmt->execute([$invitation_id, $user_id]);
            
            echo json_encode(['success' => true]);
            
        } elseif ($action === 'leave_group') {
            $group_id = (int)($data['group_id'] ?? 0);
            
            // Vérifier que ce n'est pas le dernier admin
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as admin_count
                FROM group_members
                WHERE group_id = ? AND role = 'admin'
            ");
            $stmt->execute([$group_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("
                SELECT role FROM group_members
                WHERE group_id = ? AND user_id = ?
            ");
            $stmt->execute([$group_id, $user_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($member && $member['role'] === 'admin' && $result['admin_count'] <= 1) {
                throw new Exception('Vous êtes le dernier admin, vous ne pouvez pas quitter le groupe');
            }
            
            // Retirer du groupe
            $stmt = $pdo->prepare("
                DELETE FROM group_members
                WHERE group_id = ? AND user_id = ?
            ");
            $stmt->execute([$group_id, $user_id]);
            
            echo json_encode(['success' => true]);
            
        } elseif ($action === 'delete_group') {
            $group_id = (int)($data['group_id'] ?? 0);
            
            // Vérifier que l'utilisateur est admin du groupe
            $stmt = $pdo->prepare("
                SELECT role FROM group_members
                WHERE group_id = ? AND user_id = ?
            ");
            $stmt->execute([$group_id, $user_id]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$member || $member['role'] !== 'admin') {
                throw new Exception('Seuls les admins peuvent supprimer le groupe');
            }
            
            // Supprimer le groupe (cascade supprimera automatiquement les membres, messages, invitations)
            $stmt = $pdo->prepare("
                DELETE FROM groups WHERE id = ?
            ");
            $stmt->execute([$group_id]);
            
            echo json_encode(['success' => true]);
            
        } else {
            throw new Exception('Action invalide');
        }
        exit;
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
