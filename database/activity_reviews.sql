-- ========================================
-- TABLE: activity_reviews
-- Description: Avis des utilisateurs sur les activités passées
-- ========================================

CREATE TABLE IF NOT EXISTS activity_reviews (
    -- Clé primaire auto-incrémentée
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- ID de l'activité évaluée (lien vers table activities)
    activity_id INT NOT NULL,
    
    -- ID de l'utilisateur qui laisse l'avis (lien vers table users)
    user_id INT NOT NULL,
    
    -- Note de 1 à 5 étoiles (validation SQL avec CHECK)
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    
    -- Commentaire texte (max 1000 caractères validé en PHP)
    comment TEXT,
    
    -- Date de création de l'avis (automatique)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Date de dernière modification (automatique à chaque UPDATE)
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère: si l'activité est supprimée, supprime aussi l'avis
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    
    -- Clé étrangère: si l'utilisateur est supprimé, supprime aussi ses avis
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Contrainte unique: un utilisateur ne peut laisser qu'un seul avis par activité
    UNIQUE KEY unique_review (activity_id, user_id),
    
    -- Index pour rechercher tous les avis d'une activité (performance)
    INDEX idx_activity (activity_id),
    
    -- Index pour rechercher tous les avis d'un utilisateur (performance)
    INDEX idx_user (user_id),
    
    -- Index pour filtrer/trier par note (performance)
    INDEX idx_rating (rating),
    
    -- Index pour trier par date de création (performance)
    INDEX idx_created (created_at)
    
-- Moteur InnoDB pour support transactions + clés étrangères
-- Encodage UTF-8 pour emojis et caractères spéciaux
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


