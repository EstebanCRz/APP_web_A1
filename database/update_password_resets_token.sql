-- Modifier la table password_resets pour utiliser un token au lieu d'un code
ALTER TABLE password_resets 
  DROP COLUMN code,
  DROP COLUMN used;

ALTER TABLE password_resets 
  ADD COLUMN token VARCHAR(64) NOT NULL AFTER email;

-- Créer un index sur le token
CREATE INDEX idx_token ON password_resets(token);
