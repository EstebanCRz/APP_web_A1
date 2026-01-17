-- Ajoute la colonne d'activation au profil utilisateur
ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 0;