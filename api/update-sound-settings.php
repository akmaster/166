<?php
/**
 * Update Sound Settings API
 * 
 * Update sound enabled/disabled and sound types
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$enabled = filter_var($_POST['sound_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
$soundType = sanitize($_POST['sound_type'] ?? 'threeTone');
$countdownSoundType = sanitize($_POST['countdown_sound_type'] ?? 'none');

// Valid sound types
$validSoundTypes = [
    'threeTone', 'successBell', 'gameCoin', 'digitalBlip', 'powerUp',
    'notification', 'cheerful', 'simple', 'epic', 'gentle'
];

$validCountdownTypes = [
    'none', 'tickTock', 'digitalBeep', 'drum', 'heartbeat',
    'countdown', 'arcade', 'tension', 'robot', 'lastThree'
];

if (!in_array($soundType, $validSoundTypes)) {
    jsonResponse(false, [], 'Geçersiz ses tipi');
}

if (!in_array($countdownSoundType, $validCountdownTypes)) {
    jsonResponse(false, [], 'Geçersiz countdown ses tipi');
}

$db = new Database();

$result = $db->update('users', [
    'sound_enabled' => $enabled,
    'sound_type' => $soundType,
    'countdown_sound_type' => $countdownSoundType
], ['id' => $userId]);

if (!$result['success']) {
    jsonResponse(false, [], 'Ses ayarları güncellenemedi');
}

logDebug('Sound settings updated', [
    'user_id' => $userId,
    'enabled' => $enabled,
    'sound_type' => $soundType,
    'countdown_sound_type' => $countdownSoundType
]);

jsonResponse(true, [
    'sound_enabled' => $enabled,
    'sound_type' => $soundType,
    'countdown_sound_type' => $countdownSoundType
], 'Ses ayarları güncellendi');

