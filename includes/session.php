<?php
/**
 * Gestionnaire de session centralisé
 * À inclure en PREMIER dans tous les fichiers PHP
 */

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.use_only_cookies', 1);
    @ini_set('session.cookie_secure', 0);
    session_start();
}

// Définir le header charset si pas déjà envoyé
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
