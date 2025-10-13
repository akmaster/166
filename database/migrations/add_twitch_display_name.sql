-- =====================================================
-- MIGRATION: Add twitch_display_name column to users table
-- Date: 2025-10-13
-- Reason: Display name and username can be different on Twitch
-- =====================================================

-- Add twitch_display_name column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS twitch_display_name VARCHAR(255);

-- Update existing records to use username as display name (fallback)
UPDATE users 
SET twitch_display_name = twitch_username 
WHERE twitch_display_name IS NULL;

-- Add index for performance
CREATE INDEX IF NOT EXISTS idx_users_twitch_display_name ON users(twitch_display_name);

-- Migration complete

