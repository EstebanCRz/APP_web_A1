-- Table pour les retours d'expérience après activité
CREATE TABLE IF NOT EXISTS activity_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (activity_id, user_id),
    INDEX idx_activity (activity_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test (à adapter avec vos IDs existants)
INSERT INTO activity_reviews (activity_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Super activité ! Très bien organisée et ambiance conviviale. Je recommande vivement !'),
(1, 2, 4, 'Bonne expérience, j\'ai passé un bon moment. Juste un peu long mais sinon parfait.'),
(2, 1, 5, 'Magnifique sortie photo ! J\'ai appris beaucoup de techniques et rencontré des gens sympas.'),
(3, 2, 3, 'C\'était correct mais je m\'attendais à mieux. L\'organisateur était sympathique.');
