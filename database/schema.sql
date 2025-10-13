-- TWITCH CODE REWARD SYSTEM - DATABASE SCHEMA
-- Supabase PostgreSQL Database
-- Version: 1.0
-- Date: 2025-01-13

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    twitch_user_id VARCHAR(255) UNIQUE NOT NULL,
    twitch_username VARCHAR(255) NOT NULL,
    twitch_email VARCHAR(255),
    twitch_avatar_url TEXT,
    overlay_token VARCHAR(64) UNIQUE NOT NULL,
    streamer_balance DECIMAL(10, 2) DEFAULT 0.00,
    custom_reward_amount DECIMAL(10, 2) DEFAULT NULL,
    custom_code_duration INT DEFAULT NULL,
    custom_code_interval INT DEFAULT NULL,
    custom_countdown_duration INT DEFAULT NULL,
    use_random_reward BOOLEAN DEFAULT FALSE,
    random_reward_min DECIMAL(10, 2) DEFAULT NULL,
    random_reward_max DECIMAL(10, 2) DEFAULT NULL,
    sound_enabled BOOLEAN DEFAULT TRUE,
    sound_type VARCHAR(50) DEFAULT 'threeTone',
    countdown_sound_type VARCHAR(50) DEFAULT 'none',
    overlay_theme VARCHAR(50) DEFAULT 'neon',
    next_code_time TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- =====================================================
-- 2. CODES TABLE
-- =====================================================
CREATE TABLE codes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    streamer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    code VARCHAR(6) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_bonus_code BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMPTZ NOT NULL,
    duration INT DEFAULT 30,
    countdown_duration INT DEFAULT 5,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Indexes for codes table
CREATE INDEX idx_codes_streamer_id ON codes(streamer_id);
CREATE INDEX idx_codes_active ON codes(is_active) WHERE is_active = TRUE;
CREATE INDEX idx_codes_expires_at ON codes(expires_at);
CREATE INDEX idx_codes_code ON codes(code);
CREATE INDEX idx_codes_streamer_active ON codes(streamer_id, is_active) WHERE is_active = TRUE;

-- =====================================================
-- 3. SUBMISSIONS TABLE
-- =====================================================
CREATE TABLE submissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    code_id UUID REFERENCES codes(id) ON DELETE CASCADE,
    streamer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    reward_amount DECIMAL(10, 2) NOT NULL,
    submitted_at TIMESTAMPTZ DEFAULT NOW()
);

-- Indexes for submissions table
CREATE INDEX idx_submissions_user_id ON submissions(user_id);
CREATE INDEX idx_submissions_code_id ON submissions(code_id);
CREATE INDEX idx_submissions_streamer_id ON submissions(streamer_id);
CREATE INDEX idx_submissions_submitted_at ON submissions(submitted_at DESC);
CREATE INDEX idx_submissions_user_code ON submissions(user_id, code_id);

-- =====================================================
-- 4. PAYOUT_REQUESTS TABLE
-- =====================================================
CREATE TABLE payout_requests (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    requested_at TIMESTAMPTZ DEFAULT NOW(),
    processed_at TIMESTAMPTZ DEFAULT NULL
);

-- Indexes for payout_requests table
CREATE INDEX idx_payout_requests_user_id ON payout_requests(user_id);
CREATE INDEX idx_payout_requests_status ON payout_requests(status);

-- =====================================================
-- 5. BALANCE_TOPUPS TABLE
-- =====================================================
CREATE TABLE balance_topups (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    streamer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_proof TEXT,
    note TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    requested_at TIMESTAMPTZ DEFAULT NOW(),
    processed_at TIMESTAMPTZ DEFAULT NULL
);

-- Indexes for balance_topups table
CREATE INDEX idx_balance_topups_streamer_id ON balance_topups(streamer_id);
CREATE INDEX idx_balance_topups_status ON balance_topups(status);

-- =====================================================
-- 6. SETTINGS TABLE
-- =====================================================
CREATE TABLE settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Insert default settings
INSERT INTO settings (key, value) VALUES
('payout_threshold', '5.00'),
('reward_per_code', '0.10'),
('code_duration', '30'),
('code_interval', '600'),
('countdown_duration', '5');

-- =====================================================
-- ADDITIONAL INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_users_overlay_token ON users(overlay_token);
CREATE INDEX idx_users_next_code_time ON users(next_code_time);
CREATE INDEX idx_users_twitch_user_id ON users(twitch_user_id);

-- =====================================================
-- ENABLE REALTIME FOR CODES TABLE
-- =====================================================
-- Note: You need to enable Realtime in Supabase Dashboard
-- Database → Replication → Enable "codes" table

-- =====================================================
-- ROW LEVEL SECURITY (Optional - for additional security)
-- =====================================================
-- Uncomment if you want to use RLS

-- ALTER TABLE users ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE codes ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE submissions ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE payout_requests ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE balance_topups ENABLE ROW LEVEL SECURITY;
-- ALTER TABLE settings ENABLE ROW LEVEL SECURITY;

-- =====================================================
-- FUNCTIONS AND TRIGGERS (Optional)
-- =====================================================

-- Auto-update updated_at timestamp for settings
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_settings_updated_at BEFORE UPDATE ON settings
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- SCHEMA COMPLETE
-- =====================================================
-- Total Tables: 6
-- Total Indexes: 17
-- Ready for production use

