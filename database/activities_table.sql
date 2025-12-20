-- ===============================================
-- SYSTEME DE GESTION DES ACTIVITES - AmiGo
-- ===============================================
-- 
-- IMPORTANT : Ce fichier utilise des emojis et nécessite UTF8MB4
-- 
-- SI VOUS AVEZ UNE ERREUR "Incorrect string value" :
-- 1. Configurez votre base de données en utf8mb4 :
--    ALTER DATABASE amigo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 
-- 2. OU utilisez le fichier alternatif : activities_table_no_emoji.sql
-- 
-- 3. Voir le guide complet : FIX_EMOJI_ERROR.md
-- 
-- ===============================================

-- Configuration du charset pour supporter les emojis
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Suppression des tables existantes (dans le bon ordre à cause des clés étrangères)
DROP TABLE IF EXISTS activity_registrations;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS activity_categories;
DROP TABLE IF EXISTS users;

-- Table pour les utilisateurs (si elle n'existe pas déjà)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour les catégories d'activités
CREATE TABLE IF NOT EXISTS activity_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) NOT NULL,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour les activités
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    excerpt VARCHAR(500),
    category_id INT NOT NULL,
    creator_id INT NOT NULL,
    location VARCHAR(200),
    city VARCHAR(100),
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    max_participants INT DEFAULT 0,
    current_participants INT DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES activity_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date),
    INDEX idx_category (category_id),
    INDEX idx_creator (creator_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour les inscriptions aux activités
CREATE TABLE IF NOT EXISTS activity_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('registered', 'waitlist', 'cancelled') DEFAULT 'registered',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (activity_id, user_id),
    INDEX idx_activity (activity_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des catégories par défaut
INSERT INTO activity_categories (name, color, icon) VALUES
('Sport', '#8BC34A', '⚽'),
('Cuisine', '#FF9800', '🍳'),
('Art', '#03A9F4', '🎨'),
('Musique', '#E91E63', '🎵'),
('Jeux', '#9C27B0', '🎮'),
('Nature', '#4CAF50', '🌿'),
('Bien-être', '#FFC107', '🧘'),
('Culture', '#00BCD4', '📚')
ON DUPLICATE KEY UPDATE name=name;

-- Insertion d'utilisateurs de test
INSERT INTO users (username, email, password, first_name, last_name, avatar) VALUES
('camille', 'camille@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Camille', 'Dupont', 'https://i.pravatar.cc/150?img=1'),
('zoe', 'zoe@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zoé', 'Martin', 'https://i.pravatar.cc/150?img=5'),
('nora', 'nora@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nora', 'Bernard', 'https://i.pravatar.cc/150?img=9'),
('mathis', 'mathis@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mathis', 'Petit', 'https://i.pravatar.cc/150?img=12'),
('romain', 'romain@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Romain', 'Dubois', 'https://i.pravatar.cc/150?img=14'),
('sophie', 'sophie@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophie', 'Thomas', 'https://i.pravatar.cc/150?img=16'),
('luc', 'luc@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luc', 'Robert', 'https://i.pravatar.cc/150?img=33'),
('alex', 'alex@amigo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex', 'Richard', 'https://i.pravatar.cc/150?img=52')
ON DUPLICATE KEY UPDATE username=username;

-- Insertion des activités de test
INSERT INTO activities (title, description, excerpt, category_id, creator_id, location, city, event_date, event_time, max_participants, current_participants, image) VALUES
('Sortie Running au Parc', 'Rejoignez-nous pour un footing convivial de 5km à travers le magnifique Parc Monceau. Tous niveaux sont les bienvenus, l\'important est de se faire plaisir ensemble !', 'Rejoignez-nous pour un footing convivial de 5km, tous niveaux bienvenus !', 1, 1, 'Parc Monceau', 'Paris', '2025-11-25', '09:00:00', 50, 7, 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?q=80&w=800'),
('Balade Photo au Bord de l\'Eau', 'Découvrons ensemble les meilleurs spots photo au coucher du soleil le long des quais de Bordeaux. Débutants bienvenus, apportez votre appareil ou votre smartphone !', 'Découvrons les meilleurs spots photo au coucher du soleil, débutants bienvenus.', 3, 2, 'Quais de Bordeaux', 'Bordeaux', '2025-11-28', '18:00:00', 20, 12, 'https://images.unsplash.com/photo-1511876484798-816e2c24f1c3?q=80&w=800'),
('Initiation Yoga Vinyasa', 'Séance de yoga détente et respiration dans un cadre apaisant. Parfait pour décompresser après le travail. Pensez à apporter votre tapis de yoga.', 'Séance détente et respiration, pensez à apporter votre tapis de yoga.', 7, 3, 'Studio Zen', 'Marseille', '2025-11-30', '18:00:00', 30, 4, 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&w=800'),
('Soirée Jeux de Société', 'Ambiance conviviale garantie ! Venez avec vos jeux préférés ou découvrez les nôtres. Boissons et snacks à partager entre tous.', 'Ambiance conviviale, venez avec vos jeux préférés, boissons partagées.', 5, 4, 'Café Ludique', 'Toulouse', '2025-11-27', '20:00:00', 20, 16, 'https://images.unsplash.com/photo-1511367461989-f85a21fda167?q=80&w=800'),
('Randonnée en Forêt', 'Parcours de 10km à travers les magnifiques sentiers de montagne. Prévoir des chaussures de marche et de l\'eau. Belle vue garantie au sommet !', 'Parcours de 10km, prévoir chaussures de marche et eau. Belle vue garantie !', 6, 5, 'Départ parking forestier', 'Chamonix', '2025-11-29', '08:00:00', 15, 6, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=800'),
('Concert Jazz Improvisé', 'Soirée musicale décontractée au son du jazz. Quelques musiciens amateurs se retrouvent pour improviser ensemble. Apéro inclus !', 'Soirée musicale décontractée au son du jazz, apéro inclus.', 4, 6, 'Bar Le Blue Note', 'Nice', '2025-11-30', '21:00:00', 25, 18, 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=800'),
('Picnic d\'Automne en Montagne', 'Partageons un moment convivial avec vue panoramique sur les Alpes. Chacun apporte un plat à partager. Couvertures et bonne humeur requises !', 'Partageons un moment convivial avec vue panoramique sur les Alpes.', 6, 7, 'Lac d\'Annecy', 'Annecy', '2025-12-01', '12:00:00', 35, 22, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=800'),
('Cours de Badminton', 'Entraînement récréatif tous niveaux avec un moniteur bénévole. Raquettes disponibles sur place si besoin. Ambiance fun et sportive !', 'Entraînement récréatif tous niveaux avec moniteur bénévole.', 1, 8, 'Gymnase Municipal', 'Lyon', '2025-11-26', '19:00:00', 12, 9, 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=800');
