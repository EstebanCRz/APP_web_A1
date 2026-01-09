-- Tables pour le forum AmiGo
-- À exécuter dans phpMyAdmin

-- Table des sujets du forum
CREATE TABLE IF NOT EXISTS forum_topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages du forum
CREATE TABLE IF NOT EXISTS forum_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    author_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_topic (topic_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test
-- Note: Remplacez author_id = 1 par l'ID d'un utilisateur existant dans votre base

-- Sujet 1: Général
INSERT INTO forum_topics (title, category, author_id, views, is_pinned) VALUES
('Bienvenue sur le forum AmiGo !', 'general', 1, 150, TRUE);

INSERT INTO forum_posts (topic_id, author_id, content) VALUES
(1, 1, 'Bienvenue à tous sur le forum AmiGo ! 👋

Ce forum est un espace pour échanger avec la communauté, poser vos questions, partager vos expériences et suggérer des améliorations.

N\'hésitez pas à créer un nouveau sujet si vous avez une question ou une idée à partager !

Bonne discussion à tous ! 😊');

-- Sujet 2: Événements
INSERT INTO forum_topics (title, category, author_id, views) VALUES
('Comment organiser une randonnée en groupe ?', 'events', 1, 45);

INSERT INTO forum_posts (topic_id, author_id, content) VALUES
(2, 1, 'Bonjour à tous,

Je souhaite organiser une randonnée en groupe dans les Alpes le mois prochain. C\'est la première fois que j\'organise ce type d\'événement.

Auriez-vous des conseils sur :
- Le nombre idéal de participants
- Le matériel à prévoir
- Comment gérer les différents niveaux de difficulté

Merci d\'avance pour vos retours ! 🏔️');

-- Sujet 3: Aide & Support
INSERT INTO forum_topics (title, category, author_id, views) VALUES
('Comment modifier mon profil ?', 'help', 1, 32);

INSERT INTO forum_posts (topic_id, author_id, content) VALUES
(3, 1, 'Bonjour,

Je n\'arrive pas à trouver l\'option pour modifier mes informations de profil. Pourriez-vous m\'aider ?

Merci !');

-- Sujet 4: Suggestions
INSERT INTO forum_topics (title, category, author_id, views) VALUES
('Suggestion : Système de notation des événements', 'suggestions', 1, 28);

INSERT INTO forum_posts (topic_id, author_id, content) VALUES
(4, 1, 'Bonjour,

Je pense qu\'il serait intéressant d\'avoir un système de notation pour les événements passés. Cela permettrait de :

✅ Avoir un retour sur la qualité des événements
✅ Aider les futurs participants à choisir
✅ Encourager les organisateurs à proposer des activités de qualité

Qu\'en pensez-vous ?');

-- Sujet 5: Général
INSERT INTO forum_topics (title, category, author_id, views) VALUES
('Partagez vos meilleures expériences AmiGo !', 'general', 1, 67);

INSERT INTO forum_posts (topic_id, author_id, content) VALUES
(5, 1, 'Salut la communauté ! 🎉

J\'aimerais que vous partagiez vos meilleures expériences sur AmiGo. Quelle activité vous a le plus marqué ?

Pour moi, c\'était une sortie photo au bord du lac. J\'ai rencontré des personnes super sympas et j\'ai appris plein de techniques photo !

À vous ! 📸');

-- Sujet 6: Événements
INSERT INTO forum_topics (title, category, author_id, views) VALUES
('Idées d\'activités pour l\'hiver', 'events', 1, 53);

INSERT INTO forum_posts (topic_id, author_id, content) VALUES
(6, 1, 'Avec l\'hiver qui approche, quelles activités pourrait-on organiser ?

Mes idées :
❄️ Patinoire
⛷️ Sortie ski
🍲 Atelier fondue/raclette
🎮 Soirée jeux de société au chaud

Vos suggestions ?');
