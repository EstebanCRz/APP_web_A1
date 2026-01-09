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
    // GET - Récupérer les conversations ou une conversation spécifique
    if ($method === 'GET') {
        if (isset($_GET['action']) && $_GET['action'] === 'search') {
            // Rechercher des utilisateurs
            $query = $_GET['query'] ?? '';
            
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'users' => []]);
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    u.id,
                    u.username,
                    u.first_name,
                    u.last_name
                FROM users u
                WHERE u.id != ?
                AND (
                    u.username LIKE ?
                    OR u.first_name LIKE ?
                    OR u.last_name LIKE ?
                    OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?
                )
                ORDER BY u.first_name, u.last_name
                LIMIT 20
            ");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$user_id, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'users' => $users]);
            exit;
        }
        
        if (isset($_GET['id'])) {
            // Détails d'une conversation
            $conversation_id = (int)$_GET['id'];
            
            // Vérifier que l'utilisateur fait partie de la conversation
            $stmt = $pdo->prepare("
                SELECT * FROM private_conversations
                WHERE id = ? AND (user1_id = ? OR user2_id = ?)
            ");
            $stmt->execute([$conversation_id, $user_id, $user_id]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$conversation) {
                throw new Exception('Conversation non trouvée');
            }
            
            // Déterminer l'autre utilisateur
            $other_user_id = ($conversation['user1_id'] == $user_id) 
                ? $conversation['user2_id'] 
                : $conversation['user1_id'];
            
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$other_user_id]);
            $other_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Récupérer les messages
            $stmt = $pdo->prepare("
                SELECT pm.*, u.username as sender_name
                FROM private_messages pm
                JOIN users u ON pm.sender_id = u.id
                WHERE pm.conversation_id = ?
                ORDER BY pm.created_at ASC
            ");
            $stmt->execute([$conversation_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Marquer comme lu
            $stmt = $pdo->prepare("
                UPDATE private_messages
                SET is_read = TRUE
                WHERE conversation_id = ? AND sender_id != ?
            ");
            $stmt->execute([$conversation_id, $user_id]);
            
            echo json_encode([
                'success' => true,
                'conversation' => [
                    'id' => $conversation['id'],
                    'other_user_name' => $other_user['username']
                ],
                'messages' => $messages,
                'current_user_id' => $user_id
            ]);
            
        } else {
            // Liste des conversations
            $stmt = $pdo->prepare("
                SELECT 
                    pc.id,
                    pc.last_message_at,
                    CASE 
                        WHEN pc.user1_id = ? THEN u2.username
                        ELSE u1.username
                    END as other_user_name,
                    (SELECT message FROM private_messages 
                     WHERE conversation_id = pc.id 
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM private_messages
                     WHERE conversation_id = pc.id 
                     AND sender_id != ? 
                     AND is_read = FALSE) as unread_count
                FROM private_conversations pc
                JOIN users u1 ON pc.user1_id = u1.id
                JOIN users u2 ON pc.user2_id = u2.id
                WHERE pc.user1_id = ? OR pc.user2_id = ?
                ORDER BY pc.last_message_at DESC
            ");
            $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'conversations' => $conversations]);
        }
        exit;
    }
    
    // POST - Créer une conversation ou envoyer un message
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        
        if ($action === 'start_conversation') {
            $other_user_id = (int)($data['user_id'] ?? 0);
            
            if ($other_user_id === $user_id) {
                throw new Exception('Vous ne pouvez pas discuter avec vous-même');
            }
            
            // Vérifier si une conversation existe déjà
            $stmt = $pdo->prepare("
                SELECT id FROM private_conversations
                WHERE (user1_id = ? AND user2_id = ?)
                OR (user1_id = ? AND user2_id = ?)
            ");
            $stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                echo json_encode([
                    'success' => true,
                    'conversation_id' => $existing['id']
                ]);
                exit;
            }
            
            // Créer la conversation
            $stmt = $pdo->prepare("
                INSERT INTO private_conversations (user1_id, user2_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$user_id, $other_user_id]);
            $conversation_id = $pdo->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'conversation_id' => $conversation_id
            ]);
            
        } elseif ($action === 'send_message') {
            $conversation_id = (int)($data['conversation_id'] ?? 0);
            $message = trim($data['message'] ?? '');
            $image_path = $data['image_path'] ?? null;
            
            if (empty($message) && empty($image_path)) {
                throw new Exception('Le message est vide');
            }
            
            // Vérifier que l'utilisateur fait partie de la conversation
            $stmt = $pdo->prepare("
                SELECT id FROM private_conversations
                WHERE id = ? AND (user1_id = ? OR user2_id = ?)
            ");
            $stmt->execute([$conversation_id, $user_id, $user_id]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Accès refusé');
            }
            
            // Envoyer le message
            $stmt = $pdo->prepare("
                INSERT INTO private_messages (conversation_id, sender_id, message, image_path)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$conversation_id, $user_id, $message, $image_path]);
            
            // Mettre à jour last_message_at
            $stmt = $pdo->prepare("
                UPDATE private_conversations
                SET last_message_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            
            echo json_encode(['success' => true]);
            
        } elseif ($action === 'delete_conversation') {
            $conversation_id = (int)($data['conversation_id'] ?? 0);
            
            // Vérifier que l'utilisateur fait partie de la conversation
            $stmt = $pdo->prepare("
                SELECT id FROM private_conversations
                WHERE id = ? AND (user1_id = ? OR user2_id = ?)
            ");
            $stmt->execute([$conversation_id, $user_id, $user_id]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Accès refusé');
            }
            
            // Supprimer les messages
            $stmt = $pdo->prepare("
                DELETE FROM private_messages WHERE conversation_id = ?
            ");
            $stmt->execute([$conversation_id]);
            
            // Supprimer la conversation
            $stmt = $pdo->prepare("
                DELETE FROM private_conversations WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            
            echo json_encode(['success' => true]);
            
        } elseif ($action === 'upload_image') {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Erreur lors de l\'upload');
            }
            
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            // Vérifier le type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Type de fichier non autorisé');
            }
            
            // Vérifier la taille
            if ($file['size'] > $maxSize) {
                throw new Exception('Fichier trop volumineux (max 5MB)');
            }
            
            // Créer un nom unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('msg_') . '_' . $user_id . '.' . $extension;
            $uploadPath = __DIR__ . '/../../uploads/messages/' . $filename;
            
            // Déplacer le fichier
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Erreur lors de la sauvegarde');
            }
            
            echo json_encode([
                'success' => true,
                'image_path' => 'uploads/messages/' . $filename
            ]);
            
        } else {
            throw new Exception('Action invalide');
        }
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
