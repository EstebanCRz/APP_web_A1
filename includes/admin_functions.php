<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/config.php';

/**
 * Vérifie si l'utilisateur connecté est un administrateur
 * @return bool True si l'utilisateur est admin, False sinon
 */
function isAdmin() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user && $user['role'] === 'admin';
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Redirige vers la page d'accueil si l'utilisateur n'est pas admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}
