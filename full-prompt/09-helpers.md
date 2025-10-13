# HELPER FUNCTIONS - Utilities Reference

## 📦 FILE: `config/helpers.php`

**Purpose:** Reusable utility functions  
**Loaded:** Automatically in `config/config.php`  
**Usage:** Available in all PHP files

---

## 🔐 AUTHENTICATION HELPERS

### `isLoggedIn()`

**Description:** Check if user is logged in

```php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
```

**Usage:**

```php
if (!isLoggedIn()) {
    header('Location: /index.php');
    exit;
}
```

---

### `isAdmin()`

**Description:** Check if current user is admin

```php
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}
```

**Usage:**

```php
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
```

---

### `requireLogin()`

**Description:** Redirect to homepage if not logged in

```php
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit;
    }
}
```

**Usage:**

```php
// At top of protected pages
requireLogin();
```

---

### `requireAdmin()`

**Description:** Block access for non-admins

```php
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        die('Access denied');
    }
}
```

**Usage:**

```php
// At top of admin pages
requireAdmin();
```

---

## 📊 DATA HELPERS

### `getSetting($key, $default = null)`

**Description:** Get system setting from database

```php
function getSetting($key, $default = null) {
    global $db;

    $setting = $db->selectOne('settings', 'value', ['key' => $key]);

    if ($setting && isset($setting['value'])) {
        return $setting['value'];
    }

    return $default;
}
```

**Usage:**

```php
$payoutThreshold = getSetting('payout_threshold', 5.00);
$defaultReward = getSetting('reward_per_code', 0.10);
```

---

### `getEffectiveSetting($user, $settingKey)`

**Description:** Get user's custom setting or fall back to system default

```php
function getEffectiveSetting($user, $settingKey) {
    $customKey = 'custom_' . $settingKey;

    // Check user's custom value
    if (isset($user[$customKey]) && $user[$customKey] !== null) {
        $value = $user[$customKey];
    } else {
        // Fall back to system default
        $value = getSetting($settingKey);
    }

    // Enforce minimum interval
    if ($settingKey === 'code_interval' && $value < MIN_CODE_INTERVAL) {
        $value = MIN_CODE_INTERVAL;
    }

    return $value;
}
```

**Usage:**

```php
$countdown = getEffectiveSetting($user, 'countdown_duration');
$duration = getEffectiveSetting($user, 'code_duration');
$interval = getEffectiveSetting($user, 'code_interval');
```

**Custom Settings:**

- `custom_countdown_duration` → Default: `countdown_duration`
- `custom_code_duration` → Default: `code_duration`
- `custom_code_interval` → Default: `code_interval`
- `custom_reward_amount` → Default: `reward_per_code`

---

## 💰 BALANCE HELPERS

### `calculateUserBalance($userId)`

**Description:** Calculate user's total balance from submissions

```php
function calculateUserBalance($userId) {
    global $db;

    $result = $db->query("submissions?select=reward_amount&user_id=eq.{$userId}");

    if (!$result['success']) {
        return 0.00;
    }

    $total = 0;
    foreach ($result['data'] as $submission) {
        $total += floatval($submission['reward_amount']);
    }

    // Subtract approved payouts
    $payouts = $db->select('payout_requests', 'amount', [
        'user_id' => $userId,
        'status' => 'approved'
    ]);

    if ($payouts['success'] && is_array($payouts['data'])) {
        foreach ($payouts['data'] as $payout) {
            $total -= floatval($payout['amount']);
        }
    }

    return round($total, 2);
}
```

**Usage:**

```php
$balance = calculateUserBalance($userId);
echo "Balance: " . number_format($balance, 2) . " TL";
```

---

### `updateStreamerBalance($streamerId, $amount)`

**Description:** Add or subtract from streamer balance

```php
function updateStreamerBalance($streamerId, $amount) {
    global $db;

    $streamer = $db->selectOne('users', 'streamer_balance', ['id' => $streamerId]);

    if (!$streamer) {
        return false;
    }

    $currentBalance = floatval($streamer['streamer_balance']);
    $newBalance = $currentBalance + $amount;

    return $db->update('users',
        ['streamer_balance' => $newBalance],
        ['id' => $streamerId]
    );
}
```

**Usage:**

```php
// Deduct balance
updateStreamerBalance($streamerId, -0.10);

// Add balance
updateStreamerBalance($streamerId, 100.00);
```

---

## 🎲 RANDOM HELPERS

### `generateCode()`

**Description:** Generate random 6-digit code

```php
function generateCode() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}
```

**Usage:**

```php
$code = generateCode(); // "042387"
```

---

### `generateToken($length = 64)`

**Description:** Generate secure random token

```php
function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}
```

**Usage:**

```php
$overlayToken = generateToken(64); // 128 hex chars
$sessionToken = generateToken(32);  // 64 hex chars
```

---

### `getRandomReward($user)`

**Description:** Get reward amount (random or fixed)

```php
function getRandomReward($user) {
    if ($user['use_random_reward'] &&
        $user['random_reward_min'] &&
        $user['random_reward_max']) {

        $min = floatval($user['random_reward_min']);
        $max = floatval($user['random_reward_max']);

        // Random float between min and max
        $reward = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return round($reward, 2);
    }

    // Fixed reward
    return getEffectiveSetting($user, 'reward_per_code');
}
```

**Usage:**

```php
$rewardAmount = getRandomReward($user); // 0.10 or random
```

---

## 📝 VALIDATION HELPERS

### `validateRequired($data, $fields)`

**Description:** Validate required fields in array

```php
function validateRequired($data, $fields) {
    $missing = [];

    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing[] = $field;
        }
    }

    return empty($missing) ? true : $missing;
}
```

**Usage:**

```php
$required = ['code', 'user_id'];
$validation = validateRequired($_POST, $required);

if ($validation !== true) {
    echo "Missing fields: " . implode(', ', $validation);
    exit;
}
```

---

### `sanitizeInput($input)`

**Description:** Basic XSS prevention

```php
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
```

**Usage:**

```php
$username = sanitizeInput($_POST['username']);
$note = sanitizeInput($_POST['note']);
```

---

## 🗄️ CACHE HELPERS

### `getFileCache($key, $ttl = 300)`

**Description:** Get cached data from file

```php
function getFileCache($key, $ttl = 300) {
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';

    if (!file_exists($cacheFile)) {
        return null;
    }

    $fileTime = filemtime($cacheFile);
    if (time() - $fileTime > $ttl) {
        unlink($cacheFile);
        return null;
    }

    return json_decode(file_get_contents($cacheFile), true);
}
```

**Usage:**

```php
$data = getFileCache('active_code_' . $userId, 5);
if ($data) {
    return $data; // Cache hit
}
```

---

### `setFileCache($key, $data)`

**Description:** Save data to cache file

```php
function setFileCache($key, $data) {
    $cacheDir = __DIR__ . '/../cache/';

    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . md5($key) . '.cache';
    file_put_contents($cacheFile, json_encode($data));
}
```

**Usage:**

```php
setFileCache('active_code_' . $userId, $codeData);
```

---

### `clearFileCache($pattern = null)`

**Description:** Clear cache files

```php
function clearFileCache($pattern = null) {
    $cacheDir = __DIR__ . '/../cache/';

    if (!is_dir($cacheDir)) {
        return;
    }

    $files = glob($cacheDir . '*.cache');

    foreach ($files as $file) {
        if ($pattern === null || strpos($file, md5($pattern)) !== false) {
            unlink($file);
        }
    }
}
```

**Usage:**

```php
// Clear all cache
clearFileCache();

// Clear specific cache
clearFileCache('active_code_' . $userId);
```

---

## 🌐 JSON RESPONSE HELPERS

### `success($message = '', $data = null)`

**Description:** Return success JSON response

```php
function success($message = '', $data = null) {
    $response = ['success' => true];

    if ($message) {
        $response['message'] = $message;
    }

    if ($data !== null) {
        $response['data'] = $data;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
```

**Usage:**

```php
success('Kod başarıyla kaydedildi!', ['code' => '123456']);

// Output:
// {
//   "success": true,
//   "message": "Kod başarıyla kaydedildi!",
//   "data": { "code": "123456" }
// }
```

---

### `error($message, $code = 400)`

**Description:** Return error JSON response

```php
function error($message, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'code' => $code
    ]);
    exit;
}
```

**Usage:**

```php
error('Geçersiz kod!', 400);

// Output:
// HTTP 400
// {
//   "success": false,
//   "message": "Geçersiz kod!",
//   "code": 400
// }
```

---

## 🕐 TIME HELPERS

### `formatTimeAgo($timestamp)`

**Description:** Human-readable time ago

```php
function formatTimeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);

    if ($diff < 60) return $diff . ' saniye önce';
    if ($diff < 3600) return floor($diff / 60) . ' dakika önce';
    if ($diff < 86400) return floor($diff / 3600) . ' saat önce';
    return floor($diff / 86400) . ' gün önce';
}
```

**Usage:**

```php
echo formatTimeAgo('2025-01-13 12:30:00'); // "5 dakika önce"
```

---

### `formatDuration($seconds)`

**Description:** Format seconds to readable duration

```php
function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
    } elseif ($minutes > 0) {
        return sprintf('%dm %ds', $minutes, $secs);
    } else {
        return sprintf('%ds', $secs);
    }
}
```

**Usage:**

```php
echo formatDuration(3665); // "1h 1m 5s"
echo formatDuration(125);  // "2m 5s"
echo formatDuration(30);   // "30s"
```

---

## 📋 USAGE EXAMPLES

### Complete API Endpoint:

```php
<?php
require_once __DIR__ . '/../config/config.php';

// Auth check
requireLogin();

// Validate input
$validation = validateRequired($_POST, ['amount']);
if ($validation !== true) {
    error('Missing fields: ' . implode(', ', $validation));
}

// Sanitize
$amount = floatval(sanitizeInput($_POST['amount']));

// Get user
$userId = $_SESSION['user_id'];
$balance = calculateUserBalance($userId);

// Validate balance
if ($balance < $amount) {
    error('Yetersiz bakiye!');
}

// Process
// ... your logic ...

// Success
success('İşlem başarılı!', ['new_balance' => $balance - $amount]);
```

---

## 🎨 BEST PRACTICES

1. **Always sanitize user input:** Use `sanitizeInput()`
2. **Use prepared statements:** Via Supabase REST API (automatic)
3. **Cache expensive queries:** `getFileCache()` / `setFileCache()`
4. **Return JSON consistently:** Use `success()` / `error()`
5. **Validate early:** Check auth, required fields first
6. **Handle errors gracefully:** Try/catch, meaningful messages

---

**Next:** `MASTER.md` → Complete combined reference (all files in one!)
