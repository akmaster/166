<?php
/**
 * TWITCH CODE REWARD SYSTEM - Main Configuration
 * 
 * This file handles:
 * - Environment variable loading
 * - Constants definition
 * - Session initialization
 * - Error handling
 */

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
session_start();

// Load environment variables
function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        die('Error: .env file not found. Please copy .env.example to .env and configure it.');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnv();

// Set timezone
$timezone = getenv('TIMEZONE') ?: 'Europe/Istanbul';
date_default_timezone_set($timezone);
define('TIMEZONE', $timezone);

// Error handling based on DEBUG_MODE
if (getenv('DEBUG_MODE') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../error.log');
}

// Define constants
define('SUPABASE_URL', getenv('SUPABASE_URL'));
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY'));
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY'));

define('TWITCH_CLIENT_ID', getenv('TWITCH_CLIENT_ID'));
define('TWITCH_CLIENT_SECRET', getenv('TWITCH_CLIENT_SECRET'));
define('TWITCH_REDIRECT_URI', getenv('TWITCH_REDIRECT_URI'));

define('ADMIN_USERNAME', getenv('ADMIN_USERNAME'));
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASSWORD_HASH'));

define('APP_URL', getenv('APP_URL'));
define('SESSION_LIFETIME', (int)getenv('SESSION_LIFETIME') ?: 3600);
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');

define('CRON_SECRET_KEY', getenv('CRON_SECRET_KEY'));
define('CACHE_ENABLED', getenv('CACHE_ENABLED') === 'true');
define('CACHE_TTL', (int)getenv('CACHE_TTL') ?: 2);

// API URLs
define('TWITCH_OAUTH_URL', 'https://id.twitch.tv/oauth2');
define('TWITCH_API_URL', 'https://api.twitch.tv/helix');

// Default system values
define('DEFAULT_REWARD_AMOUNT', 0.10);
define('DEFAULT_CODE_DURATION', 30);
define('DEFAULT_CODE_INTERVAL', 600); // 10 minutes
define('DEFAULT_COUNTDOWN_DURATION', 5);
define('DEFAULT_PAYOUT_THRESHOLD', 5.00);

// Minimum limits
define('MIN_CODE_INTERVAL', 60); // 1 minute (cron frequency)
define('MIN_CODE_DURATION', 1);
define('MIN_COUNTDOWN_DURATION', 0);

// Maximum limits (professional & realistic)
define('MAX_CODE_DURATION', 3600);    // 1 hour (60 minutes)
define('MAX_CODE_INTERVAL', 86400);   // 1 day (24 hours)
define('MAX_COUNTDOWN_DURATION', 300); // 5 minutes

// Available sounds
define('AVAILABLE_CODE_SOUNDS', ['threeTone', 'successBell', 'gameCoin', 'digitalBlip', 'powerUp', 'notification', 'cheerful', 'simple', 'epic', 'gentle']);
define('AVAILABLE_COUNTDOWN_SOUNDS', ['tickTock', 'click', 'beep', 'blip', 'snap', 'tap', 'ping', 'chirp', 'pop', 'tick']);

// Asset version for cache busting
define('ASSET_VERSION', '6.2.1');

// Sound settings limits
define('MIN_COUNTDOWN_SOUND_START_AT', 0);    // 0 = play every second
define('MAX_COUNTDOWN_SOUND_START_AT', 300);  // Max 5 minutes (same as max countdown duration)

// Cache directory
define('CACHE_DIR', __DIR__ . '/../cache');

// Create cache directory if it doesn't exist
if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

// Session timeout check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Include database and helpers
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

// Include language configuration (i18n)
require_once __DIR__ . '/../languages/config.php';

