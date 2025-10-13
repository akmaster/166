# API ENDPOINTS - Complete Reference

## 🌐 API ARCHITECTURE

**Base URL:** `https://yourdomain.com/api/`  
**Format:** JSON (request & response)  
**Authentication:** Session-based (`$_SESSION['user_id']`)  
**Method:** POST (most), GET (public data)

---

## 🔐 AUTHENTICATION APIS

### 1. `/api/auth.php` - Twitch OAuth Start

**Method:** GET  
**Auth:** None

**Redirect to Twitch:**

```php
$redirectUrl = 'https://id.twitch.tv/oauth2/authorize?' . http_build_query([
    'client_id' => TWITCH_CLIENT_ID,
    'redirect_uri' => TWITCH_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'user:read:email'
]);
header('Location: ' . $redirectUrl);
```

---

### 2. `/callback.php` - Twitch OAuth Callback

**Method:** GET  
**Params:** `?code=TWITCH_CODE`

**Flow:**

1. Get access token from Twitch
2. Get user data
3. Create/update user in database
4. Generate `overlay_token` (64 chars)
5. Set session
6. Redirect to dashboard

**Session Data:**

```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['twitch_username'] = $user['twitch_username'];
$_SESSION['is_admin'] = false; // Admin kontrolü ayrı
```

---

### 3. `/api/logout.php` - Logout

**Method:** GET  
**Auth:** Required

**Response:**

```json
{
  "success": true,
  "message": "Çıkış başarılı"
}
```

---

## 📊 PUBLIC DATA APIS

### 4. `/api/get-public-stats.php` - Public Statistics

**Method:** GET  
**Auth:** None

**Response:**

```json
{
  "success": true,
  "data": {
    "total_users": 1234,
    "total_rewards": "5678.90",
    "total_codes": 9876,
    "active_streamers": 45
  }
}
```

---

### 5. `/api/get-live-streamers.php` - Live Streamers List

**Method:** GET  
**Auth:** None  
**Params:** `?limit=20` (optional)

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "twitch_username": "streamer1",
      "twitch_display_name": "Streamer One",
      "twitch_avatar_url": "https://...",
      "reward_amount": "0.10",
      "is_live": true
    }
  ]
}
```

**Note:** `is_live` Twitch API ile kontrol edilir (caching önerilir)

---

## 🎮 OVERLAY API

### 6. `/api/get-active-code.php` - Get Active Code (Overlay)

**Method:** GET  
**Params:** `?streamer_id=UUID`  
**Auth:** None (token via query param)

**Request:**

```
GET /api/get-active-code.php?streamer_id=abc-123
```

**Response (No Code):**

```json
{
  "success": true,
  "data": {
    "has_code": false
  }
}
```

**Response (Active Code):**

```json
{
  "success": true,
  "data": {
    "has_code": true,
    "id": "code-uuid",
    "code": "123456",
    "created_at": "2025-01-13T12:30:00.000Z",
    "expires_at": "2025-01-13T12:30:35.000Z",
    "countdown_duration": 5,
    "duration": 30,
    "time_since_created": 10,
    "time_until_expiry": 25
  }
}
```

**Important Fields:**

- `time_since_created` → Elapsed time (seconds) - **UTC calculated!**
- `time_until_expiry` → Remaining time (seconds)

**Usage in Overlay:**

```javascript
const elapsed = code.time_since_created;
const total = code.countdown_duration + code.duration;

if (elapsed < code.countdown_duration) {
  // Show countdown
  startCountdown(code.countdown_duration - elapsed);
} else if (elapsed < total) {
  // Show code
  showCode(code.code, total - elapsed);
}
```

**Caching:** 5 seconds file cache

---

## 💰 VIEWER APIS

### 7. `/api/submit-code.php` - Submit Code

**Method:** POST  
**Auth:** Required (viewer)

**Request:**

```json
{
  "code": "123456"
}
```

**Response (Success):**

```json
{
  "success": true,
  "message": "Kod kabul edildi!",
  "data": {
    "reward_amount": "0.10",
    "new_balance": "5.60"
  }
}
```

**Response (Errors):**

```json
// Invalid code
{
  "success": false,
  "message": "Geçersiz kod!"
}

// Already used
{
  "success": false,
  "message": "Bu kodu zaten kullandınız!"
}

// Expired
{
  "success": false,
  "message": "Kod süresi dolmuş!"
}
```

**Validation:**

1. Code exists & active
2. Not expired (`time_since_created < countdown + duration`)
3. User hasn't used this code before
4. Update user balance
5. Create submission record
6. Deduct from streamer balance (if not bonus code)

---

### 8. `/api/get-activity.php` - Recent Activity

**Method:** GET  
**Auth:** Required

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "code": "123456",
      "streamer_username": "streamer1",
      "reward_amount": "0.10",
      "submitted_at": "2025-01-13T12:30:00Z"
    }
  ]
}
```

---

### 9. `/api/request-payout.php` - Request Payout

**Method:** POST  
**Auth:** Required (viewer)

**Request:**

```json
{
  "amount": "10.00"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Ödeme talebi oluşturuldu!"
}
```

**Validation:**

- Amount >= `payout_threshold` (default: 5.00 TL)
- User balance >= amount
- No pending payout request

---

## 🎛️ STREAMER APIS

### 10. `/api/update-reward-amount.php` - Update Reward Amount

**Method:** POST  
**Auth:** Required (streamer)

**Request:**

```json
{
  "reward_amount": "0.15"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Ödül miktarı güncellendi!",
  "data": {
    "custom_reward_amount": "0.15"
  }
}
```

---

### 11. `/api/update-code-settings.php` - Update Code Settings

**Method:** POST  
**Auth:** Required (streamer)

**Request:**

```json
{
  "countdown": 10,
  "duration": 60,
  "interval": 300
}
```

**Validation:**

```php
// Min/Max checks
MIN_COUNTDOWN_DURATION = 0
MAX_COUNTDOWN_DURATION = 300 (5 min)

MIN_CODE_DURATION = 1
MAX_CODE_DURATION = 3600 (1 hour)

MIN_CODE_INTERVAL = 60
MAX_CODE_INTERVAL = 86400 (1 day)
```

**Response:**

```json
{
  "success": true,
  "message": "Kod ayarları güncellendi!",
  "data": {
    "custom_countdown_duration": 10,
    "custom_code_duration": 60,
    "custom_code_interval": 300
  }
}
```

---

### 12. `/api/update-random-reward.php` - Update Random Reward

**Method:** POST  
**Auth:** Required (streamer)

**Request:**

```json
{
  "use_random_reward": true,
  "min_reward": "0.05",
  "max_reward": "0.50"
}
```

**Validation:**

- `min_reward` < `max_reward`
- Both > 0

---

### 13. `/api/update-sound-settings.php` - Update Sound Settings

**Method:** POST  
**Auth:** Required (streamer)

**Request:**

```json
{
  "sound_enabled": true,
  "code_sound": "threeTone",
  "countdown_sound": "tickTock",
  "code_sound_enabled": true,
  "countdown_sound_enabled": true,
  "countdown_sound_start_at": 10
}
```

**Validation:**

- `code_sound` in `AVAILABLE_CODE_SOUNDS`
- `countdown_sound` in `AVAILABLE_COUNTDOWN_SOUNDS`
- `countdown_sound_start_at`: 0-300

---

### 14. `/api/update-theme.php` - Update Overlay Theme

**Method:** POST  
**Auth:** Required (streamer)

**Request:**

```json
{
  "theme": "neon"
}
```

**Available Themes (20):**

```javascript
const themes = [
  'neon',
  'cyberpunk',
  'matrix',
  'gradient',
  'minimal',
  'dark',
  'light',
  'retro',
  'vaporwave',
  'synthwave',
  'ocean',
  'forest',
  'sunset',
  'galaxy',
  'fire',
  'ice',
  'gold',
  'purple',
  'rainbow',
  'monochrome',
];
```

---

### 15. `/api/request-topup.php` - Request Balance Top-up

**Method:** POST  
**Auth:** Required (streamer)

**Request:**

```json
{
  "amount": "100.00",
  "payment_proof": "https://imgur.com/xyz",
  "note": "PayPal reference: XYZ123"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Bakiye yükleme talebi oluşturuldu!"
}
```

---

### 16. `/api/calculate-budget.php` - Calculate Budget

**Method:** POST  
**Auth:** Required (streamer)

**Request:**

```json
{
  "reward_amount": "0.10",
  "duration_hours": 4,
  "interval_minutes": 5
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "codes_per_hour": 12,
    "total_codes": 48,
    "total_cost": "4.80"
  }
}
```

---

## 🔧 ADMIN APIS

### `/api/admin/generate-code.php` - Manual Code Send

**Method:** POST  
**Auth:** Required (admin)

**Request:**

```json
{
  "streamer_id": "uuid",
  "countdown_duration": 10,
  "duration": 30,
  "is_bonus_code": true
}
```

**Process:**

1. Check for active code (prevent duplicate)
2. Generate 6-digit code
3. Calculate `expires_at` (UTC!)
4. Insert to database
5. **IF bonus code:** Skip balance check/deduction
6. Update streamer's `next_code_time`

---

### `/api/admin/get-code-details.php` - Get Code Details

**Method:** GET  
**Params:** `?code_id=UUID`  
**Auth:** Required (admin)

**Response:**

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "code": "123456",
    "streamer": {
      "username": "streamer1",
      "display_name": "Streamer One"
    },
    "created_at": "2025-01-13T12:30:00Z",
    "is_active": true,
    "is_bonus_code": false,
    "usage_count": 15,
    "users_who_used": ["user1", "user2"]
  }
}
```

---

## 🔐 ERROR HANDLING

**Standard Error Response:**

```json
{
  "success": false,
  "message": "Hata mesajı",
  "code": 400
}
```

**HTTP Status Codes:**

- `200` → Success
- `400` → Bad Request (validation error)
- `401` → Unauthorized (not logged in)
- `403` → Forbidden (insufficient permissions)
- `500` → Server Error

---

## 📋 API SECURITY CHECKLIST

- [ ] Session check (`isLoggedIn()`)
- [ ] Input validation (required params)
- [ ] SQL injection prevention (prepared statements via Supabase)
- [ ] XSS prevention (`htmlspecialchars()`)
- [ ] Rate limiting (caching)
- [ ] HTTPS enforced
- [ ] Admin routes protected
- [ ] Timezone consistency (UTC!)

---

**Next:** `06-sound-system.md` → 20 Web Audio functions
