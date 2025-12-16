-- IMPORTANT: This script assumes the 'aisu_db' database already exists
-- OR that the user running this script has CREATE DATABASE privileges.
-- Based on previous errors, 'if0_39920279' DOES NOT have CREATE DATABASE privileges.
-- Therefore, 'aisu_db' MUST be created manually by a privileged user first.

-- Furthermore, the user 'if0_39920279' MUST have full permissions (ALL PRIVILEGES)
-- granted on 'aisu_db.*' by a privileged user for this script to run.

-- Create the database if it doesn't exist
-- (This line will likely still fail with "Access denied" for 'if0_39920279'
-- unless permissions are specifically granted to create databases.)

-- Users table (students and admins)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `level` int(1) DEFAULT NULL, -- Assuming 'level' might be used for student academic levels, or similar
  `profile_image` varchar(255) DEFAULT 'default.png',
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages table
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `admin_response` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0, -- 0 for private, 1 for public (e.g., FAQ)
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `responded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blocked numbers table
CREATE TABLE IF NOT EXISTS `blocked_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(20) NOT NULL UNIQUE,
  `blocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Grades announcements table
CREATE TABLE IF NOT EXISTS `grades_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `level` int(1) NOT NULL,
  `search_column` varchar(100) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `grades_announcements_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Grades data table
CREATE TABLE IF NOT EXISTS `grades_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `announcement_id` int(11) NOT NULL,
  `row_data` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `announcement_id` (`announcement_id`),
  CONSTRAINT `grades_data_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `grades_announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert the main admin account if it doesn't already exist
-- The password is 'admin' (hashed using bcrypt)
-- You should never store plain text passwords.
-- This checks if a user with the specific username 'AISU' and role 'admin' exists.
-- If not, it inserts the admin user.
INSERT INTO `users` (`username`, `first_name`, `last_name`, `phone_number`, `password`, `role`)
SELECT 'AISU', 'Admin', 'User', '0000000000', '$2y$10$w09uCj/n8wQY/d6JzFzTpOyI.3pC9P/P2A7kQ1.x/O4i.n2xYfB/y', 'admin'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'AISU' AND `role` = 'admin');
