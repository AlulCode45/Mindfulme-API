-- Create chats table for complaint chat system
CREATE TABLE IF NOT EXISTS `chats` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `complaint_id` VARCHAR(255) NOT NULL,  -- UUID from complaints table
  `sender_id` VARCHAR(255) NOT NULL,     -- UUID from users table
  `sender_type` ENUM('user', 'admin', 'psychologist') NOT NULL,
  `message_text` TEXT NOT NULL,
  `message_type` ENUM('text', 'image', 'file') NOT NULL DEFAULT 'text',
  `file_url` VARCHAR(255) NULL,
  `file_name` VARCHAR(255) NULL,
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_chats_complaint_id` (`complaint_id`),
  INDEX `idx_chats_sender_id` (`sender_id`),
  INDEX `idx_chats_sender_type` (`sender_type`),
  INDEX `idx_chats_is_read` (`is_read`),
  INDEX `idx_chats_complaint_created` (`complaint_id`, `created_at`),
  INDEX `idx_chats_sender_read` (`sender_id`, `is_read`)
);

-- Add foreign key constraints if the tables exist
-- ALTER TABLE `chats` ADD CONSTRAINT `fk_chats_complaint_id`
--   FOREIGN KEY (`complaint_id`) REFERENCES `complaints`(`uuid`) ON DELETE CASCADE;
-- ALTER TABLE `chats` ADD CONSTRAINT `fk_chats_sender_id`
--   FOREIGN KEY (`sender_id`) REFERENCES `users`(`uuid`) ON DELETE CASCADE;