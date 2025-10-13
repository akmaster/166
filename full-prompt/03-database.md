# DATABASE SCHEMA & MIGRATIONS

## 📊 COMPLETE DATABASE STRUCTURE

**Platform:** Supabase (PostgreSQL)  
**Total Tables:** 6  
**Total Indexes:** 17  
**UUID Extension:** Required

---

## 🗄️ TABLE SCHEMAS

### 1. USERS TABLE (Ana Tablo)

```sql
CREATE TABLE users (
    -- Identity
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    twitch_user_id VARCHAR(255) UNIQUE NOT NULL,
    twitch_username VARCHAR(255) NOT NULL,
    twitch_email VARCHAR(255),
    twitch_avatar_url TEXT,
    twitch_display_name VARCHAR(255),

    -- Overlay
    overlay_token VARCHAR(64) UNIQUE NOT NULL,
    overlay_theme VARCHAR(50) DEFAULT 'neon',

    -- Balances
    streamer_balance DECIMAL(10, 2) DEFAULT 0.00,

    -- Custom Settings
    custom_reward_amount DECIMAL(10, 2) DEFAULT NULL,
    custom_code_duration INT DEFAULT NULL,
    custom_code_interval INT DEFAULT NULL,
    custom_countdown_duration INT DEFAULT NULL,

    -- Random Reward
    use_random_reward BOOLEAN DEFAULT FALSE,
    random_reward_min DECIMAL(10, 2) DEFAULT NULL,
    random_reward_max DECIMAL(10, 2) DEFAULT NULL,

    -- Sound Settings (v6.2)
    sound_enabled BOOLEAN DEFAULT TRUE,
    code_sound VARCHAR(50) DEFAULT 'threeTone',
    countdown_sound VARCHAR(50) DEFAULT 'tickTock',
    code_sound_enabled BOOLEAN DEFAULT TRUE,
    countdown_sound_enabled BOOLEAN DEFAULT TRUE,
    countdown_sound_start_at INT DEFAULT 0,

    -- Timing
    next_code_time TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
```

**Indexes:**

- `idx_users_overlay_token` → Overlay erişimi (çok hızlı olmalı)
- `idx_users_next_code_time` → Cron job sorguları
- `idx_users_twitch_user_id` → OAuth login

**Field Açıklamaları:**

| Field                      | Type           | Açıklama                           | Default                  |
| -------------------------- | -------------- | ---------------------------------- | ------------------------ |
| `id`                       | UUID           | Primary key                        | Auto-generated           |
| `twitch_user_id`           | VARCHAR(255)   | Twitch'den gelen unique ID         | -                        |
| `overlay_token`            | VARCHAR(64)    | OBS overlay için unique token      | Auto-generated (64 char) |
| `streamer_balance`         | DECIMAL(10, 2) | Yayıncı bakiyesi (TL)              | 0.00                     |
| `custom_reward_amount`     | DECIMAL(10, 2) | Yayıncı özel ödül                  | NULL = default kullan    |
| `custom_code_duration`     | INT            | Kod görünme süresi (saniye)        | NULL = default kullan    |
| `custom_code_interval`     | INT            | Kod aralığı (saniye)               | NULL = default kullan    |
| `sound_enabled`            | BOOLEAN        | Master ses toggle                  | TRUE                     |
| `countdown_sound_start_at` | INT            | Geri sayım sesi başlama (0-300s)   | 0 (her saniye)           |
| `next_code_time`           | TIMESTAMPTZ    | Bir sonraki kod zamanı (cron için) | NULL                     |

---

### 2. CODES TABLE (Kod Tablosu)

```sql
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
```

**Indexes:**

- `idx_codes_streamer_id` → Yayıncıya göre kodlar
- `idx_codes_active` → Aktif kodları hızlı bul
- `idx_codes_expires_at` → Expire cleanup
- `idx_codes_code` → Kod girişinde arama
- `idx_codes_streamer_active` → Composite (aktif kodu bul)

**Field Açıklamaları:**

| Field                | Açıklama                     | Önemli Not           |
| -------------------- | ---------------------------- | -------------------- |
| `code`               | 6 haneli kod (000000-999999) | Random generate      |
| `is_active`          | Kod hala kullanılabilir mi?  | Expire olunca FALSE  |
| `is_bonus_code`      | Admin bonus kodu mu?         | TRUE = bakiye düşmez |
| `expires_at`         | Kod son kullanım zamanı      | UTC timezone!        |
| `duration`           | Kod ekranda kalma süresi     | Saniye cinsinden     |
| `countdown_duration` | Geri sayım süresi            | Saniye cinsinden     |

**⚠️ TIMEZONE KRİTİK:** Tüm TIMESTAMPTZ alanları **UTC** olmalı!

---

### 3. SUBMISSIONS TABLE (Kod Kullanımları)

```sql
CREATE TABLE submissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    code_id UUID REFERENCES codes(id) ON DELETE CASCADE,
    streamer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    reward_amount DECIMAL(10, 2) NOT NULL,
    submitted_at TIMESTAMPTZ DEFAULT NOW()
);
```

**Indexes:**

- `idx_submissions_user_id` → Kullanıcının geçmişi
- `idx_submissions_code_id` → Kodun kullanımları
- `idx_submissions_streamer_id` → Yayıncının istatistikleri
- `idx_submissions_submitted_at` → Zaman sırası (DESC)
- `idx_submissions_user_code` → Duplicate check

**Önemli:** Bir kullanıcı aynı kodu sadece 1 kez kullanabilir (unique constraint yok ama API'de kontrol edilmeli)

---

### 4. PAYOUT_REQUESTS TABLE (Ödeme Talepleri)

```sql
CREATE TABLE payout_requests (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    requested_at TIMESTAMPTZ DEFAULT NOW(),
    processed_at TIMESTAMPTZ DEFAULT NULL
);
```

**Status Values:**

- `pending` → Bekliyor
- `approved` → Onaylandı
- `rejected` → Reddedildi
- `completed` → Tamamlandı (ödeme yapıldı)

---

### 5. BALANCE_TOPUPS TABLE (Bakiye Yükleme)

```sql
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
```

**Status Values:** `pending`, `approved`, `rejected`

---

### 6. SETTINGS TABLE (Sistem Ayarları)

```sql
CREATE TABLE settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Default values
INSERT INTO settings (key, value) VALUES
('payout_threshold', '5.00'),      -- Minimum ödeme talebi
('reward_per_code', '0.10'),       -- Default ödül
('code_duration', '30'),           -- Default kod süresi
('code_interval', '600'),          -- Default interval (10 dakika)
('countdown_duration', '5');       -- Default countdown
```

---

## 🔄 MIGRATIONS

### Migration 1: Bonus Code System

**File:** `add_is_bonus_code.sql`

```sql
ALTER TABLE codes ADD COLUMN IF NOT EXISTS is_bonus_code BOOLEAN DEFAULT FALSE;

UPDATE codes SET is_bonus_code = FALSE WHERE is_bonus_code IS NULL;

COMMENT ON COLUMN codes.is_bonus_code IS 'Admin bonus code (no balance deduction)';
```

### Migration 2: Display Name & Theme

**File:** `add_twitch_display_name.sql`

```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS twitch_display_name VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS overlay_theme VARCHAR(50) DEFAULT 'neon';

UPDATE users SET overlay_theme = 'neon' WHERE overlay_theme IS NULL;

COMMENT ON COLUMN users.twitch_display_name IS 'Twitch display name (formatted)';
COMMENT ON COLUMN users.overlay_theme IS 'Selected overlay theme (20 options)';

CREATE INDEX IF NOT EXISTS idx_users_twitch_display_name ON users(twitch_display_name);
```

### Migration 3: Sound Settings (v6.2)

**File:** `add_sound_settings.sql`

```sql
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

COMMENT ON COLUMN users.sound_enabled IS 'Master sound toggle for overlay (all sounds)';
COMMENT ON COLUMN users.code_sound IS 'Selected sound for code reveal';
COMMENT ON COLUMN users.countdown_sound IS 'Selected sound for countdown ticks';
COMMENT ON COLUMN users.code_sound_enabled IS 'Individual toggle for code reveal sound';
COMMENT ON COLUMN users.countdown_sound_enabled IS 'Individual toggle for countdown tick sound';
COMMENT ON COLUMN users.countdown_sound_start_at IS 'Start countdown sound when X seconds remaining (0 = all seconds)';
```

---

## 🔐 REALTIME SETUP

**⚠️ KRİTİK:** Supabase Realtime'ı aktifleştirmeden overlay çalışmaz!

### Adımlar:

1. **Supabase Dashboard** → **Database** → **Replication**
2. **Publications** bölümünde `supabase_realtime` bul
3. "0 tables" yazıyorsa **TIKLA**
4. `codes` tablosunu **✅ işaretle**
5. **KAYDET!**

### Test:

Overlay console'da şu mesajı görmelisin:

```javascript
[Overlay] Realtime connected
```

---

## 📈 PERFORMANCE OPTIMIZATION

### Index Strategy:

| Sorgu Tipi           | Index                       | Neden                 |
| -------------------- | --------------------------- | --------------------- |
| Overlay token lookup | `idx_users_overlay_token`   | Her overlay yüklemede |
| Active code check    | `idx_codes_streamer_active` | Her realtime event    |
| Cron job query       | `idx_users_next_code_time`  | Her dakika            |
| Code submission      | `idx_submissions_user_code` | Duplicate check       |

### Query Examples:

```sql
-- Active code for streamer (FAST - uses composite index)
SELECT * FROM codes
WHERE streamer_id = 'xxx' AND is_active = TRUE
ORDER BY created_at DESC LIMIT 1;

-- Next cron users (FAST - uses time index + tolerance)
SELECT * FROM users
WHERE next_code_time <= NOW() + INTERVAL '45 seconds';

-- User balance calculation (FAST - uses user_id index)
SELECT SUM(reward_amount) FROM submissions
WHERE user_id = 'xxx';
```

---

## 🛡️ SECURITY NOTES

### RLS (Row Level Security):

- **Disabled by default** (API kontrolü ile güvenlik)
- İstersen aktifleştirebilirsin (lines 151-156 in schema.sql)

### Cascade Deletes:

- ✅ User silinince → Tüm kodları, submission'ları silinir
- ✅ Code silinince → Submission'ları silinir
- ⚠️ **PRODUCTION'DA DİKKAT:** Soft delete düşünülebilir

---

**Next:** `04-components.md` → Component detayları (5 component)
