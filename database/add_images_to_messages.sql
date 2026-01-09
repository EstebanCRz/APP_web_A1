-- Script pour ajouter le support d'images dans les messages
-- Exécutez ce script dans phpMyAdmin ou MySQL Workbench

USE amigo_db;

-- Ajouter la colonne image_path à la table private_messages
ALTER TABLE private_messages 
ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER message;

-- Vérifier que la colonne a été ajoutée
DESCRIBE private_messages;

SELECT 'Migration terminée avec succès !' as message;
