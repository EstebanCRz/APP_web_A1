-- Création de la table des villes
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des villes françaises
INSERT INTO cities (name) VALUES
('Paris'), ('Lyon'), ('Marseille'), ('Toulouse'), ('Nice'), 
('Nantes'), ('Strasbourg'), ('Montpellier'), ('Bordeaux'), ('Lille'),
('Rennes'), ('Reims'), ('Le Havre'), ('Saint-Étienne'), ('Toulon'),
('Grenoble'), ('Dijon'), ('Angers'), ('Nîmes'), ('Clermont-Ferrand'),
('Le Mans'), ('Aix-en-Provence'), ('Brest'), ('Tours'), ('Amiens'),
('Limoges'), ('Annecy'), ('Perpignan'), ('Besançon'), ('Orléans'),
('Metz'), ('Rouen'), ('Mulhouse'), ('Caen'), ('Nancy'),
('Avignon'), ('Poitiers'), ('Versailles'), ('Pau'), ('Calais'),
('La Rochelle'), ('Cannes'), ('Antibes'), ('Ajaccio'), ('Bastia')
ON DUPLICATE KEY UPDATE name=name;
