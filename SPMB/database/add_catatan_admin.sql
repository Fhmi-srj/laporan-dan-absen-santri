-- Add catatan_admin column for admin messages to registrants
-- Run this SQL in phpMyAdmin or MySQL CLI

ALTER TABLE pendaftaran 
ADD COLUMN catatan_admin TEXT NULL AFTER status;

-- Optional: Add updated_at for catatan tracking
ALTER TABLE pendaftaran 
ADD COLUMN catatan_updated_at TIMESTAMP NULL AFTER catatan_admin;
