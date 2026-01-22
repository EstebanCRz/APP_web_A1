-- Ajouter les colonnes manquantes à la table users pour le profil
-- Note: Si une colonne existe déjà, vous verrez une erreur - ignorez-la simplement

-- Colonne avatar pour la photo de profil
ALTER TABLE `users` 
ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL AFTER `password`;

-- Colonne bio pour la biographie
ALTER TABLE `users` 
ADD COLUMN `bio` TEXT DEFAULT NULL AFTER `avatar`;

-- Colonne city pour la ville
ALTER TABLE `users` 
ADD COLUMN `city` VARCHAR(100) DEFAULT NULL AFTER `bio`;

-- Colonne phone pour le téléphone
ALTER TABLE `users` 
ADD COLUMN `phone` VARCHAR(20) DEFAULT NULL AFTER `city`;
