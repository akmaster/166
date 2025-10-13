# System Patterns: Rumb

## Mimari Genel Bakış

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐
│   Browser   │─────▶│  PHP Backend │─────▶│  Supabase   │
│  (Overlay)  │◀─────│  (REST API)  │◀─────│ PostgreSQL  │
└─────────────┘      └──────────────┘      └─────────────┘
       │                                           │
       │                                           │
       └───────────── Realtime WebSocket ─────────┘
                      (Supabase JS Client)
```

## Kod Yaşam Döngüsü

1. **Cron Job** (her dakika):

   - `next_code_time <= NOW()` olan yayıncıları bulur
   - Bakiye kontrolü yapar
   - Yeni kod üretir (6 haneli)
   - `codes` tablosuna INSERT eder
   - `next_code_time` günceller

2. **Supabase Realtime**:

   - `codes` tablosuna INSERT eventi yakalar
   - WebSocket ile overlay'e push eder

3. **OBS Overlay**:

   - INSERT eventi alır
   - Countdown başlatır
   - Card flip animasyonu (3D)
   - Ses çalar (Web Audio API)
   - Duration sonunda gizler

4. **İzleyici**:
   - Kodu girer (6 haneli)
   - Validation (format, aktiflik, countdown)
   - Deduplication check
   - Bakiye kontrolü (yayıncı)
   - Submission kaydı
   - Bakiye güncellemesi (- yayıncı, + izleyici)

## Database Schema Patterns

### Users Table

- `id`: UUID (primary key)
- `overlay_token`: Unique 64-char token (overlay auth)
- `streamer_balance`: Decimal (yayıncı bakiyesi)
- `custom_*`: Nullable (system default override)
- `next_code_time`: Timestamp (cron optimization)

### Codes Table

- Lifecycle: `is_active = true` → `expires_at > NOW()`
- Soft expire: `is_active = false` (cron tarafından)
- Index optimization: `(streamer_id, is_active, expires_at)`

### Submissions Table

- İmmutable log (hiç silinmez)
- Balance calculation: SUM(reward_amount) - SUM(payout.completed)

## Caching Strategy

**File-based cache (shared hosting):**

- `cache/` dizini (755 permissions)
- Key: MD5 hash
- TTL: 2 saniye (aktif kodlar için)
- Serialize/unserialize

**Cached Data:**

- Active codes: `active_code_{user_id}`
- Twitch app token: `twitch_app_token` (1 saat)

## Configuration Pattern

```php
// 1. Load .env
loadEnv() → parse .env → putenv()

// 2. Define constants
define('SUPABASE_URL', getenv('SUPABASE_URL'))

// 3. Include dependencies
require_once database.php
require_once helpers.php
```

## API Response Pattern

```json
{
  "success": true|false,
  "data": {},
  "message": "Human readable message"
}
```

## Component Pattern

Her component şu yapıda:

```
ComponentName/
├── ComponentName.php      (HTML/PHP)
├── ComponentName.css      (Source)
├── ComponentName.min.css  (Production)
├── ComponentName.js       (Source)
└── ComponentName.min.js   (Production)
```

## Realtime Fallback Pattern

```javascript
// 1. Primary: Supabase Realtime
subscribeToRealtime();

// 2. Fallback: Polling (5 saniye)
if (CHANNEL_ERROR) {
  startPolling();
}
```

## Security Patterns

1. **Input Sanitization:**

   ```php
   $input = sanitize($_POST['field'])
   // htmlspecialchars + strip_tags + trim
   ```

2. **Authentication:**

   - Twitch OAuth: Authorization Code Flow
   - Admin: Session-based (username/bcrypt)
   - Cron: Secret key in query param

3. **Authorization:**
   - Overlay: Unique token per user
   - API: Session check (`requireLogin()`)
   - Admin: Session check (`requireAdmin()`)

## Error Handling

```php
// Debug mode
if (DEBUG_MODE) {
  error_reporting(E_ALL)
  ini_set('display_errors', 1)
  logDebug($message, $data)
}

// Production
jsonResponse(false, [], 'User-friendly message')
```

## Database Query Pattern

```php
// Always use Database class wrapper
$db = new Database($useServiceKey = false)

// SELECT
$result = $db->select('table', 'columns', $conditions, $orderBy, $limit)

// INSERT
$result = $db->insert('table', $data)

// UPDATE
$result = $db->update('table', $data, $conditions)

// Response: ['success' => bool, 'data' => array|null]
```
