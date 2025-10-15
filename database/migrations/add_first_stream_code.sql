-- Migration: Add first stream code tracking
-- Date: 2025-01-XX
-- Description: Add columns to track first welcome code for streamers

-- Add column to users table to track if streamer received first welcome code
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS received_first_code BOOLEAN DEFAULT false;

-- Add column to codes table to mark welcome codes
ALTER TABLE codes 
ADD COLUMN IF NOT EXISTS is_welcome_code BOOLEAN DEFAULT false;

-- Update existing users to have received_first_code = false
UPDATE users 
SET received_first_code = false 
WHERE received_first_code IS NULL;

-- Add index for better performance on welcome code queries
CREATE INDEX IF NOT EXISTS idx_codes_welcome_code 
ON codes(streamer_id, is_welcome_code, is_active);

-- Add index for better performance on first code tracking
CREATE INDEX IF NOT EXISTS idx_users_first_code 
ON users(received_first_code, next_code_time);
