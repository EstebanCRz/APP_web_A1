<?php
require_once '../includes/session.php';
require_once '../includes/config.php';

// Vérifier si l'utilisateur est admin
function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user && $user['role'] === 'admin';
    } catch (Exception $e) {
        error_log("Erreur vérification admin: " . $e->getMessage());
        return false;
    }
}

// Rediriger si pas admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../auth/login.php?error=access_denied');
        exit;
    }
}
