<?php
/**
 * Gestion centralisée des sessions
 * Ce fichier doit être inclus en premier dans chaque page PHP
 * pour éviter les problèmes "headers already sent"
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    // Configuration des cookies de session
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    @ini_set('session.use_strict_mode', 1);
    
    // Démarrer la session seulement si les headers n'ont pas été envoyés
    if (!headers_sent()) {
        session_start();
    }
}
