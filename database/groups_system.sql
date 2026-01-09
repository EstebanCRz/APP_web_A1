-- Tables pour le système de groupes et messages privés

-- Table des groupes
CREATE TABLE IF NOT EXISTS `groups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `activity_id` INT,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_activity` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des membres de groupes
CREATE TABLE IF NOT EXISTS `group_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role` ENUM('admin', 'member') DEFAULT 'member',
    `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_member` (`group_id`, `user_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages de groupe
CREATE TABLE IF NOT EXISTS `group_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_group` (`group_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des conversations privées
CREATE TABLE IF NOT EXISTS `private_conversations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user1_id` INT NOT NULL,
    `user2_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_message_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user1_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user2_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_conversation` (`user1_id`, `user2_id`),
    INDEX `idx_user1` (`user1_id`),
    INDEX `idx_user2` (`user2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages privés
CREATE TABLE IF NOT EXISTS `private_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `private_conversations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_conversation` (`conversation_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des invitations de groupe
CREATE TABLE IF NOT EXISTS `group_invitations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `invited_by` INT NOT NULL,
    `status` ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`invited_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_invitation` (`group_id`, `user_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
