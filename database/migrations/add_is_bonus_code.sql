-- Migration: Add is_bonus_code field to codes table
-- Date: 2025-01-11
-- Description: Admin panelinden gönderilen bonus kodları için flag

-- Add is_bonus_code column
ALTER TABLE codes 
ADD COLUMN IF NOT EXISTS is_bonus_code BOOLEAN DEFAULT FALSE;

-- Update existing codes (set to FALSE by default)
UPDATE codes 
SET is_bonus_code = FALSE 
WHERE is_bonus_code IS NULL;

-- Add comment
COMMENT ON COLUMN codes.is_bonus_code IS 'True if this code is a bonus code sent by admin (no balance deduction when used)';

