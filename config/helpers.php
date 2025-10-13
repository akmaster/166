<?php
/**
 * TWITCH CODE REWARD SYSTEM - Helper Functions
 * 
 * 30+ utility functions for common operations
 */

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL);
        exit;
    }
}

/**
 * Require admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = new Database();
    $result = $db->getUserById(getCurrentUserId());
    
    return $result['success'] ? $result['data'] : null;
}

/**
 * JSON response
 */
function jsonResponse($success, $data = [], $message = '', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Generate random code (6 digits)
 */
function generateCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Generate random token
 */
function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate code format
 */
function isValidCode($code) {
    return preg_match('/^\d{6}$/', $code);
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'TL') {
    return number_format($amount, 2, ',', '.') . ' ' . $currency;
}

/**
 * Format date
 */
function formatDate($date, $format = 'd.m.Y H:i') {
    if (empty($date)) return '-';
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' saniye önce';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' dakika önce';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' saat önce';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' gün önce';
    } else {
        return formatDate($datetime);
    }
}

/**
 * File cache - Set
 */
function setFileCache($key, $data, $ttl = CACHE_TTL) {
    if (!CACHE_ENABLED) return false;
    
    $cacheFile = CACHE_DIR . '/' . md5($key) . '.cache';
    $cacheData = [
        'expires' => time() + $ttl,
        'data' => $data
    ];
    
    return file_put_contents($cacheFile, serialize($cacheData)) !== false;
}

/**
 * File cache - Get
 */
function getFileCache($key, $ttl = CACHE_TTL) {
    if (!CACHE_ENABLED) return null;
    
    $cacheFile = CACHE_DIR . '/' . md5($key) . '.cache';
    
    if (!file_exists($cacheFile)) {
        return null;
    }
    
    $cacheData = unserialize(file_get_contents($cacheFile));
    
    if (time() > $cacheData['expires']) {
        unlink($cacheFile);
        return null;
    }
    
    return $cacheData['data'];
}

/**
 * File cache - Clear
 */
function clearFileCache($key = null) {
    if ($key === null) {
        // Clear all cache
        $files = glob(CACHE_DIR . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    $cacheFile = CACHE_DIR . '/' . md5($key) . '.cache';
    if (file_exists($cacheFile)) {
        return unlink($cacheFile);
    }
    
    return false;
}

/**
 * Get Twitch App Access Token (cached)
 */
function getTwitchAppToken() {
    $cached = getFileCache('twitch_app_token', 3600);
    if ($cached) {
        return $cached;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, TWITCH_OAUTH_URL . '/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => TWITCH_CLIENT_ID,
        'client_secret' => TWITCH_CLIENT_SECRET,
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        setFileCache('twitch_app_token', $data['access_token'], 3600);
        return $data['access_token'];
    }
    
    return null;
}

/**
 * Call Twitch API
 */
function callTwitchAPI($endpoint, $params = [], $token = null) {
    if (!$token) {
        $token = getTwitchAppToken();
    }
    
    $url = TWITCH_API_URL . '/' . $endpoint;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Client-ID: ' . TWITCH_CLIENT_ID,
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

/**
 * Check if streamer is live
 */
function isStreamerLive($twitchUserId) {
    $data = callTwitchAPI('streams', ['user_id' => $twitchUserId]);
    return !empty($data['data']);
}

/**
 * Get streamer info
 */
function getStreamerInfo($twitchUserId) {
    return callTwitchAPI('users', ['id' => $twitchUserId]);
}

/**
 * Get stream info
 */
function getStreamInfo($twitchUserId) {
    $data = callTwitchAPI('streams', ['user_id' => $twitchUserId]);
    return $data['data'][0] ?? null;
}

/**
 * Get effective setting for user
 * Returns custom value if set, otherwise system default
 */
function getEffectiveSetting($user, $settingName) {
    $customField = 'custom_' . $settingName;
    
    if (isset($user[$customField]) && $user[$customField] !== null) {
        return $user[$customField];
    }
    
    // Return system default
    $db = new Database();
    switch ($settingName) {
        case 'reward_amount':
            return floatval($db->getSetting('reward_per_code', DEFAULT_REWARD_AMOUNT));
        case 'code_duration':
            return intval($db->getSetting('code_duration', DEFAULT_CODE_DURATION));
        case 'code_interval':
            return intval($db->getSetting('code_interval', DEFAULT_CODE_INTERVAL));
        case 'countdown_duration':
            return intval($db->getSetting('countdown_duration', DEFAULT_COUNTDOWN_DURATION));
        default:
            return null;
    }
}

/**
 * Calculate random reward
 */
function calculateReward($user) {
    if ($user['use_random_reward'] && $user['random_reward_min'] && $user['random_reward_max']) {
        $min = floatval($user['random_reward_min']) * 100; // Convert to cents
        $max = floatval($user['random_reward_max']) * 100;
        $random = random_int($min, $max);
        return $random / 100; // Convert back to TL
    }
    
    return getEffectiveSetting($user, 'reward_amount');
}

/**
 * Format seconds to human readable
 */
function formatSeconds($seconds) {
    if ($seconds < 60) {
        return $seconds . ' saniye';
    } elseif ($seconds < 3600) {
        return floor($seconds / 60) . ' dakika';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return $hours . ' saat' . ($minutes > 0 ? ' ' . $minutes . ' dakika' : '');
    }
}

/**
 * Validate decimal number
 */
function isValidDecimal($value, $min = 0, $max = null) {
    if (!is_numeric($value)) return false;
    $value = floatval($value);
    if ($value < $min) return false;
    if ($max !== null && $value > $max) return false;
    return true;
}

/**
 * Validate integer
 */
function isValidInt($value, $min = 0, $max = null) {
    if (!is_numeric($value)) return false;
    $value = intval($value);
    if ($value < $min) return false;
    if ($max !== null && $value > $max) return false;
    return true;
}

/**
 * Redirect helper
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Get base URL
 */
function baseUrl($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Asset URL helper
 */
function asset($path) {
    return baseUrl('assets/' . ltrim($path, '/'));
}

/**
 * Component include helper
 */
function component($name) {
    $path = __DIR__ . '/../components/' . $name . '/' . $name . '.php';
    if (file_exists($path)) {
        include $path;
    } else {
        echo "<!-- Component $name not found -->";
    }
}

/**
 * Get client IP
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Log debug message
 */
function logDebug($message, $data = null) {
    if (DEBUG_MODE) {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        if ($data !== null) {
            $logMessage .= ' | Data: ' . json_encode($data);
        }
        error_log($logMessage);
    }
}

