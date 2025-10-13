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
// Add 45 second tolerance to catch codes scheduled within next 45s
// This prevents 1-minute delays when cron runs at :59 seconds
$nowPlusTolerance = (clone $now)->modify('+45 seconds');
$nowFormatted = $nowPlusTolerance->format('Y-m-d\TH:i:s.u\Z');

$log[] = "Checking codes with 45s tolerance (effective time: {$nowFormatted})";

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
    $codeInterval = getEffectiveSetting($user, 'code_interval'); // minimum 60s enforced
    
    $log[] = "  Settings: countdown={$countdownDuration}s, duration={$codeDuration}s, interval={$codeInterval}s (min: " . MIN_CODE_INTERVAL . "s)";
    
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
        $log[] = "  SUCCESS: Code generated: $code";
        $log[] = "    - Created at: " . $now->format('Y-m-d H:i:s');
        $log[] = "    - Expires at: " . $expiresAt->format('Y-m-d H:i:s');
        $log[] = "    - Countdown: {$countdownDuration}s, Duration: {$codeDuration}s";
        $codesGenerated++;
        
        // Update next_code_time (UTC)
        $nextCodeTime = (new DateTime('now', new DateTimeZone('UTC')))->modify("+{$codeInterval} seconds");
        $db->update('users', [
            'next_code_time' => $nextCodeTime->format('Y-m-d\TH:i:s.u\Z')
        ], ['id' => $userId]);
        
        $log[] = "  Next code time: " . $nextCodeTime->format('Y-m-d H:i:s');
        
        // Clear cache
        clearFileCache('active_code_' . $userId);
        $log[] = "  Cache cleared for user: $userId";
    } else {
        $log[] = "  ERROR: Failed to insert code";
    }
}

// Cleanup: Mark expired codes as inactive (use REAL time, not tolerance!)
$nowRealFormatted = $now->format('Y-m-d\TH:i:s.u\Z');
$expiredResult = $db->query("codes?is_active=eq.true&expires_at=lt.$nowRealFormatted");
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
$output = [
    'success' => true,
    'codes_generated' => $codesGenerated,
    'users_processed' => count($users),
    'duration_ms' => $duration,
    'timestamp' => date('Y-m-d H:i:s')
];

// Include log only in DEBUG mode (security)
if (DEBUG_MODE) {
    $output['log'] = $log;
}

// Check if requesting JSON or HTML
$format = $_GET['format'] ?? 'json';

// HTML format only allowed in DEBUG mode (security)
if ($format === 'html' && !DEBUG_MODE) {
    $format = 'json'; // Force JSON in production
}

if ($format === 'html') {
    // HTML output for browser viewing (DEBUG MODE ONLY)
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cron Job Status</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 40px 20px;
            }
            
            .container {
                max-width: 800px;
                margin: 0 auto;
            }
            
            .card {
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                overflow: hidden;
                animation: slideUp 0.5s ease;
            }
            
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            
            .header h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }
            
            .header p {
                opacity: 0.9;
                font-size: 14px;
            }
            
            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 20px;
                padding: 30px;
                background: #f8f9fa;
            }
            
            .stat {
                text-align: center;
                padding: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            
            .stat-value {
                font-size: 32px;
                font-weight: bold;
                color: #667eea;
                margin-bottom: 8px;
            }
            
            .stat-label {
                font-size: 12px;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .success {
                color: #28a745;
            }
            
            .warning {
                color: #ffc107;
            }
            
            .log {
                padding: 30px;
            }
            
            .log h3 {
                margin-bottom: 15px;
                color: #333;
            }
            
            .log-item {
                padding: 8px 12px;
                margin-bottom: 5px;
                background: #f8f9fa;
                border-left: 3px solid #667eea;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 13px;
                color: #495057;
            }
            
            .log-item.error {
                border-left-color: #dc3545;
                background: #fff5f5;
            }
            
            .log-item.success {
                border-left-color: #28a745;
                background: #f0fff4;
            }
            
            .footer {
                text-align: center;
                padding: 20px;
                color: #6c757d;
                font-size: 12px;
            }
            
            .badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .badge-success {
                background: #d4edda;
                color: #155724;
            }
            
            .badge-info {
                background: #d1ecf1;
                color: #0c5460;
            }
            
            .info-box {
                background: #e7f3ff;
                border: 1px solid #b3d9ff;
                border-radius: 8px;
                padding: 15px;
                margin: 20px 0;
                color: #004085;
                font-size: 13px;
                line-height: 1.6;
            }
            
            .info-box strong {
                color: #002752;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="header">
                    <h1>🤖 Cron Job Status</h1>
                    <p>Automatic Code Generator</p>
                    <p style="margin-top: 10px; padding: 8px 16px; background: rgba(255,255,255,0.2); border-radius: 20px; display: inline-block; font-size: 11px;">
                        ⚠️ DEBUG MODE - This view is disabled in production
                    </p>
                </div>
                
                <div class="stats">
                    <div class="stat">
                        <div class="stat-value success">✓</div>
                        <div class="stat-label">Status</div>
                    </div>
                    
                    <div class="stat">
                        <div class="stat-value <?php echo $codesGenerated > 0 ? 'success' : 'warning'; ?>">
                            <?php echo $codesGenerated; ?>
                        </div>
                        <div class="stat-label">Codes Generated</div>
                    </div>
                    
                    <div class="stat">
                        <div class="stat-value"><?php echo count($users); ?></div>
                        <div class="stat-label">Users Processed</div>
                    </div>
                    
                    <div class="stat">
                        <div class="stat-value"><?php echo number_format($duration, 2); ?> ms</div>
                        <div class="stat-label">Duration</div>
                    </div>
                </div>
                
                <div class="info-box">
                    <strong>ℹ️ Minimum Code Interval:</strong> <?php echo MIN_CODE_INTERVAL; ?> saniye (1 dakika)<br>
                    <strong>📌 Sebep:</strong> Cron job 1 dakikada bir çalışıyor. Kullanıcılar daha kısa interval ayarlasa bile sistem minimum <?php echo MIN_CODE_INTERVAL; ?> saniye kullanır.
                </div>
                
                <div class="log">
                    <h3>📋 Execution Log</h3>
                    <?php foreach ($log as $logItem): ?>
                        <?php 
                            $class = '';
                            if (strpos($logItem, 'ERROR') !== false) $class = 'error';
                            elseif (strpos($logItem, 'SUCCESS') !== false) $class = 'success';
                        ?>
                        <div class="log-item <?php echo $class; ?>"><?php echo htmlspecialchars($logItem); ?></div>
                    <?php endforeach; ?>
                </div>
                
                <div class="footer">
                    <span class="badge badge-info"><?php echo date('Y-m-d H:i:s'); ?></span>
                    <span class="badge badge-success">Rumb Code Reward System</span>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // JSON output (default)
    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
}


/**
 * Log cron execution
 */
function logCron($logArray) {
    if (!DEBUG_MODE) return;
    
    $logFile = __DIR__ . '/cron.log';
    $logText = implode("\n", $logArray) . "\n\n";
    file_put_contents($logFile, $logText, FILE_APPEND);
}

