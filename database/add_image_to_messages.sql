-- Ajouter la colonne image aux messages privés
ALTER TABLE private_messages 
ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER message;

-- Créer le dossier uploads/messages (à créer manuellement ou via PHP)
-- mkdir -p uploads/messages
