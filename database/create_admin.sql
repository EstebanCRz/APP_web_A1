-- Ajouter le champ role à la table users
ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user' AFTER password;

-- Créer le premier compte admin
-- Mot de passe: Admin2026!
INSERT INTO users (username, email, password, first_name, last_name, role, created_at) 
VALUES (
    'admin', 
    'admin@amigo.com', 
    '$2y$10$YPemvGxHfXrQ6LrJmQxBxO8hH9XQK0nK5lZzJVG5wDJTx6mZBwKm6',
    'Admin',
    'AmiGo',
    'admin',
    NOW()
) ON DUPLICATE KEY UPDATE role='admin';
