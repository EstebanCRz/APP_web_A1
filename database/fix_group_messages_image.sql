-- Ajouter la colonne image_path à la table group_messages
ALTER TABLE `group_messages` 
ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL AFTER `message`;
