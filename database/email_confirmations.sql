-- Table pour stocker les codes de confirmation d'email
CREATE TABLE IF NOT EXISTS email_confirmations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(10) NOT NULL,
    created_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0
);