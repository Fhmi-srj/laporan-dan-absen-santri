-- Add reset token columns for password reset functionality
ALTER TABLE `pendaftaran` 
ADD COLUMN `reset_token` VARCHAR(64) NULL DEFAULT NULL,
ADD COLUMN `reset_token_expires` DATETIME NULL DEFAULT NULL;

-- Add index for faster token lookup
ALTER TABLE `pendaftaran` ADD INDEX `idx_reset_token` (`reset_token`);
