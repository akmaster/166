<?php
/**
 * Cron Job - Automatic Code Generator
 * 
 * Runs every minute to generate codes for active streamers
 * 
 * Setup:
 * - cPanel Cron: * * * * * /usr/bin/php /path/to/cron.php?key=YOUR_SECRET_KEY
 * - Or use cron-job.org: https://yourdomain.com/cron.php?key=YOUR_SECRET_KEY
 */

require_once __DIR__ . '/config/config.php';

// Security check
$providedKey = $_GET['key'] ?? '';
if ($providedKey !== CRON_SECRET_KEY) {
    http_response_code(401);
    die('Unauthorized');
}

// Start execution
$startTime = microtime(true);
$log = [];
$log[] = '===== CRON JOB START: ' . date('Y-m-d H:i:s') . ' =====';

// Database connection
$db = new Database(true); // Use service key for admin operations

// Get all users who need new codes (UTC time)
$now = new DateTime('now', new DateTimeZone('UTC'));
$nowFormatted = $now->format('Y-m-d\TH:i:s.u\Z');

// Use Supabase query string format
$usersResult = $db->query("users?select=*&next_code_time=lte.$nowFormatted&order=twitch_username.asc");

if (!$usersResult['success']) {
    $log[] = 'ERROR: Failed to fetch users';
    logCron($log);
    die('Error fetching users');
}

$users = $usersResult['data'];
$log[] = 'Found ' . count($users) . ' user(s) ready for new code';

// Process each user
$codesGenerated = 0;
foreach ($users as $user) {
    $userId = $user['id'];
    $username = $user['twitch_username'];
    
    $log[] = "Processing user: $username ($userId)";
    
    // Get effective settings
    $countdownDuration = getEffectiveSetting($user, 'countdown_duration');
    $codeDuration = getEffectiveSetting($user, 'code_duration');
    $codeInterval = getEffectiveSetting($user, 'code_interval');
    
    $log[] = "  Settings: countdown={$countdownDuration}s, duration={$codeDuration}s, interval={$codeInterval}s";
    
    // Check if user has sufficient balance
    $streamerBalance = floatval($user['streamer_balance']);
    if ($streamerBalance <= 0) {
        $log[] = "  SKIP: Insufficient balance (0 TL)";
        
        // Set next code time to far future (don't keep trying) - UTC
        $futureTime = (new DateTime('now', new DateTimeZone('UTC')))->modify('+1 day');
        $db->update('users', [
            'next_code_time' => $futureTime->format('Y-m-d\TH:i:s.u\Z')
        ], ['id' => $userId]);
        
        continue;
    }
    
    // Expire any old active codes for this streamer
    $db->update('codes', ['is_active' => false], [
        'streamer_id' => $userId,
        'is_active' => 'true'
    ]);
    
    // Generate new code (MUST use UTC!)
    $code = generateCode();
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $expiresAt = (clone $now)->modify("+{$countdownDuration} seconds")->modify("+{$codeDuration} seconds");
    
    $codeResult = $db->insert('codes', [
        'streamer_id' => $userId,
        'code' => $code,
        'is_active' => true,
        'is_bonus_code' => false, // Automatic cron codes are NOT bonus (balance will be deducted)
        'expires_at' => $expiresAt->format('Y-m-d\TH:i:s.u\Z'),
        'duration' => $codeDuration,
        'countdown_duration' => $countdownDuration,
        'created_at' => $now->format('Y-m-d\TH:i:s.u\Z')
    ]);
    
    if ($codeResult['success']) {
        $log[] = "  SUCCESS: Code generated: $code (expires: " . $expiresAt->format('Y-m-d H:i:s') . ")";
        $codesGenerated++;
        
        // Update next_code_time (UTC)
        $nextCodeTime = (new DateTime('now', new DateTimeZone('UTC')))->modify("+{$codeInterval} seconds");
        $db->update('users', [
            'next_code_time' => $nextCodeTime->format('Y-m-d\TH:i:s.u\Z')
        ], ['id' => $userId]);
        
        $log[] = "  Next code time: " . $nextCodeTime->format('Y-m-d H:i:s');
        
        // Clear cache
        clearFileCache('active_code_' . $userId);
    } else {
        $log[] = "  ERROR: Failed to insert code";
    }
}

// Cleanup: Mark expired codes as inactive
$expiredResult = $db->query("codes?is_active=eq.true&expires_at=lt.$nowFormatted");
if ($expiredResult['success'] && !empty($expiredResult['data'])) {
    foreach ($expiredResult['data'] as $expiredCode) {
        $db->update('codes', ['is_active' => false], ['id' => $expiredCode['id']]);
    }
}

if ($expiredResult['success']) {
    $log[] = 'Expired codes cleaned up';
}

// End execution
$endTime = microtime(true);
$duration = round(($endTime - $startTime) * 1000, 2);

$log[] = "===== CRON JOB END: {$codesGenerated} code(s) generated in {$duration}ms =====";

// Log to file if debug mode
logCron($log);

// Output
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'codes_generated' => $codesGenerated,
    'duration_ms' => $duration,
    'timestamp' => date('Y-m-d H:i:s')
]);

/**
 * Log cron execution
 */
function logCron($logArray) {
    if (!DEBUG_MODE) return;
    
    $logFile = __DIR__ . '/cron.log';
    $logText = implode("\n", $logArray) . "\n\n";
    file_put_contents($logFile, $logText, FILE_APPEND);
}

