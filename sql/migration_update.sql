-- =====================================================
-- MIGRATION: Update Attendances Table
-- Add type (clock_in/clock_out) and geolocation columns
-- Run this SQL on your existing database
-- =====================================================

-- 1. Add 'type' column for clock_in/clock_out
ALTER TABLE `attendances` 
ADD COLUMN `type` ENUM('clock_in', 'clock_out') DEFAULT 'clock_in' AFTER `jadwal_id`;

-- 2. Add geolocation columns
ALTER TABLE `attendances` 
ADD COLUMN `latitude` VARCHAR(50) NULL AFTER `notes`,
ADD COLUMN `longitude` VARCHAR(50) NULL AFTER `latitude`;

-- 3. Update existing records to have type based on time
UPDATE `attendances` 
SET `type` = CASE 
    WHEN TIME(`attendance_time`) < '12:00:00' THEN 'clock_in'
    ELSE 'clock_out'
END
WHERE `type` IS NULL OR `type` = '';

-- Done!
SELECT 'Migration completed successfully!' as status;
