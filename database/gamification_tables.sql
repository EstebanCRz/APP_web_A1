-- Tables pour le système de gamification

-- Table des points utilisateurs
CREATE TABLE IF NOT EXISTS user_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_points INT DEFAULT 0,
    level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de l'historique des points
CREATE TABLE IF NOT EXISTS points_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    reference_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des badges disponibles
CREATE TABLE IF NOT EXISTS badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    description_fr TEXT,
    description_en TEXT,
    icon VARCHAR(10) NOT NULL,
    condition_type VARCHAR(50) NOT NULL,
    condition_value INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des badges obtenus par les utilisateurs
CREATE TABLE IF NOT EXISTS user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, badge_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des badges de base
INSERT INTO badges (code, name_fr, name_en, description_fr, description_en, icon, condition_type, condition_value) VALUES
('first_event', 'Premier Pas', 'First Step', 'Créez votre premier événement', 'Create your first event', '🎯', 'events_created', 1),
('events_master', 'Organisateur Pro', 'Event Master', 'Créez 10 événements', 'Create 10 events', '🏆', 'events_created', 10),
('super_organizer', 'Super Organisateur', 'Super Organizer', 'Créez 50 événements', 'Create 50 events', '⭐', 'events_created', 50),

('first_participation', 'Première Sortie', 'First Outing', 'Participez à votre premier événement', 'Attend your first event', '🎉', 'events_attended', 1),
('social_butterfly', 'Papillon Social', 'Social Butterfly', 'Participez à 10 événements', 'Attend 10 events', '🦋', 'events_attended', 10),
('party_animal', 'Fêtard', 'Party Animal', 'Participez à 25 événements', 'Attend 25 events', '🎊', 'events_attended', 25),
('legend', 'Légende', 'Legend', 'Participez à 100 événements', 'Attend 100 events', '👑', 'events_attended', 100),

('first_friend', 'Premier Ami', 'First Friend', 'Ajoutez votre premier ami', 'Add your first friend', '🤝', 'friends_count', 1),
('popular', 'Populaire', 'Popular', 'Ayez 10 amis', 'Have 10 friends', '💫', 'friends_count', 10),
('influencer', 'Influenceur', 'Influencer', 'Ayez 50 amis', 'Have 50 friends', '🌟', 'friends_count', 50),

('reviewer', 'Critique', 'Reviewer', 'Laissez 5 avis', 'Leave 5 reviews', '📝', 'reviews_count', 5),
('expert_reviewer', 'Expert Critique', 'Expert Reviewer', 'Laissez 25 avis', 'Leave 25 reviews', '📖', 'reviews_count', 25),

('early_bird', 'Lève-tôt', 'Early Bird', 'Inscrivez-vous à 5 événements à l\'avance', 'Register early for 5 events', '🐦', 'early_registrations', 5),
('group_creator', 'Créateur de Groupe', 'Group Creator', 'Créez 3 groupes de discussion', 'Create 3 discussion groups', '💬', 'groups_created', 3),
('chatty', 'Bavard', 'Chatty', 'Envoyez 100 messages', 'Send 100 messages', '💭', 'messages_sent', 100),

('level_10', 'Niveau 10', 'Level 10', 'Atteignez le niveau 10', 'Reach level 10', '🔟', 'level', 10),
('level_25', 'Niveau 25', 'Level 25', 'Atteignez le niveau 25', 'Reach level 25', '🔆', 'level', 25),
('level_50', 'Niveau 50', 'Level 50', 'Atteignez le niveau 50', 'Reach level 50', '💎', 'level', 50);

-- Initialiser les points pour les utilisateurs existants
INSERT INTO user_points (user_id, total_points, level)
SELECT id, 0, 1 FROM users
ON DUPLICATE KEY UPDATE total_points = total_points;
