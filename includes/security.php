<?php
/**
 * Système de sécurité centralisé
 */

class Security {
    
    /**
     * Nettoie les données contre les injections XSS
     */
    public static function cleanInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'cleanInput'], $data);
        }
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * Génère un token CSRF
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifie le token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        return true;
    }
    
    /**
     * Protection contre les attaques brute force
     */
    public static function checkBruteForce($identifier, $maxAttempts = 5, $timeWindow = 900) {
        $pdo = getDB();
        
        // Créer la table si elle n'existe pas
        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_identifier (identifier),
            INDEX idx_time (attempt_time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Nettoyer les anciennes tentatives
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL :timeWindow SECOND)");
        $stmt->execute([':timeWindow' => $timeWindow]);
        
        // Compter les tentatives récentes
        $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE identifier = :identifier AND attempt_time > DATE_SUB(NOW(), INTERVAL :timeWindow SECOND)");
        $stmt->execute([
            ':identifier' => $identifier,
            ':timeWindow' => $timeWindow
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['attempts'] >= $maxAttempts) {
            return false; // Trop de tentatives
        }
        
        return true;
    }
    
    /**
     * Enregistre une tentative de connexion
     */
    public static function recordLoginAttempt($identifier) {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO login_attempts (identifier) VALUES (:identifier)");
        $stmt->execute([':identifier' => $identifier]);
    }
    
    /**
     * Réinitialise les tentatives de connexion après succès
     */
    public static function resetLoginAttempts($identifier) {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE identifier = :identifier");
        $stmt->execute([':identifier' => $identifier]);
    }
    
    /**
     * Rate limiting pour les formulaires
     */
    public static function checkRateLimit($action, $maxRequests = 10, $timeWindow = 60) {
        $key = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'first_request' => time()
            ];
        }
        
        $data = $_SESSION[$key];
        $currentTime = time();
        
        // Réinitialiser si la fenêtre de temps est dépassée
        if ($currentTime - $data['first_request'] > $timeWindow) {
            $_SESSION[$key] = [
                'count' => 1,
                'first_request' => $currentTime
            ];
            return true;
        }
        
        // Vérifier la limite
        if ($data['count'] >= $maxRequests) {
            return false; // Limite atteinte
        }
        
        // Incrémenter le compteur
        $_SESSION[$key]['count']++;
        return true;
    }
    
    /**
     * Valide et nettoie une URL
     */
    public static function sanitizeURL($url) {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '';
        }
        return $url;
    }
    
    /**
     * Valide un email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Nettoie les données SQL (en complément de PDO)
     */
    public static function sanitizeSQL($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeSQL'], $data);
        }
        return trim($data);
    }
    
    /**
     * Définit les headers de sécurité
     */
    public static function setSecurityHeaders() {
        // Protection XSS
        header("X-XSS-Protection: 1; mode=block");
        
        // Empêche le MIME sniffing
        header("X-Content-Type-Options: nosniff");
        
        // Clickjacking protection
        header("X-Frame-Options: SAMEORIGIN");
        
        // HTTPS strict
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdnjs.cloudflare.com https://raw.githubusercontent.com; style-src 'self' 'unsafe-inline' https://unpkg.com; img-src 'self' data: https:; font-src 'self' data:;");
        
        // Referrer Policy
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }
    
    /**
     * Vérifie si la session est valide et sécurisée
     */
    public static function validateSession() {
        // Regénérer l'ID de session périodiquement
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regénérer après 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Vérifier l'IP et le User-Agent
        $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] ?? '' . ($_SERVER['REMOTE_ADDR'] ?? ''));
        if (!isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = $fingerprint;
        } else if ($_SESSION['fingerprint'] !== $fingerprint) {
            // Session hijacking détecté
            session_unset();
            session_destroy();
            return false;
        }
        
        return true;
    }
}
