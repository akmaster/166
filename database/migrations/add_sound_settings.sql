/**
 * Add Sound Settings Columns to Users Table
 * 
 * Run this in Supabase SQL Editor
 */

-- Add sound settings columns
ALTER TABLE users ADD COLUMN IF NOT EXISTS sound_enabled BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS code_sound VARCHAR(50) DEFAULT 'threeTone';
ALTER TABLE users ADD COLUMN IF NOT EXISTS countdown_sound VARCHAR(50) DEFAULT 'tickTock';
ALTER TABLE users ADD COLUMN IF NOT EXISTS code_sound_enabled BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS countdown_sound_enabled BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS countdown_sound_start_at INT DEFAULT 0;

-- Set defaults for existing users
UPDATE users SET sound_enabled = TRUE WHERE sound_enabled IS NULL;
UPDATE users SET code_sound = 'threeTone' WHERE code_sound IS NULL;
UPDATE users SET countdown_sound = 'tickTock' WHERE countdown_sound IS NULL;
UPDATE users SET code_sound_enabled = TRUE WHERE code_sound_enabled IS NULL;
UPDATE users SET countdown_sound_enabled = TRUE WHERE countdown_sound_enabled IS NULL;
UPDATE users SET countdown_sound_start_at = 0 WHERE countdown_sound_start_at IS NULL;

-- Add comments
COMMENT ON COLUMN users.sound_enabled IS 'Master sound toggle for overlay (all sounds)';
COMMENT ON COLUMN users.code_sound IS 'Selected sound for code reveal';
COMMENT ON COLUMN users.countdown_sound IS 'Selected sound for countdown ticks';
COMMENT ON COLUMN users.code_sound_enabled IS 'Individual toggle for code reveal sound';
COMMENT ON COLUMN users.countdown_sound_enabled IS 'Individual toggle for countdown tick sound';
COMMENT ON COLUMN users.countdown_sound_start_at IS 'Start countdown sound when X seconds remaining (0 = all seconds)';

