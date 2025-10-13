<?php
/**
 * Update Code Settings API
 * 
 * Update countdown, duration, interval settings
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$countdown = $_POST['countdown_duration'] ?? null;
$duration = $_POST['code_duration'] ?? null;
$interval = $_POST['code_interval'] ?? null;

$updateData = [];

// Validate and prepare countdown
if ($countdown === '' || $countdown === null) {
    $updateData['custom_countdown_duration'] = null;
    $countdown = DEFAULT_COUNTDOWN_DURATION;
} else {
    $countdown = intval($countdown);
    if (!isValidInt($countdown, 0, 300)) {
        jsonResponse(false, [], 'Countdown: 0-300 saniye arası');
    }
    $updateData['custom_countdown_duration'] = $countdown;
}

// Validate and prepare duration
if ($duration === '' || $duration === null) {
    $updateData['custom_code_duration'] = null;
    $duration = DEFAULT_CODE_DURATION;
} else {
    $duration = intval($duration);
    if (!isValidInt($duration, 1, 9999999)) {
        jsonResponse(false, [], 'Duration: 1-9,999,999 saniye arası');
    }
    $updateData['custom_code_duration'] = $duration;
}

// Validate and prepare interval
if ($interval === '' || $interval === null) {
    $updateData['custom_code_interval'] = null;
    $interval = DEFAULT_CODE_INTERVAL;
} else {
    $interval = intval($interval);
    if (!isValidInt($interval, 1, 9999999)) {
        jsonResponse(false, [], 'Interval: 1-9,999,999 saniye arası');
    }
    $updateData['custom_code_interval'] = $interval;
}

// Validation rules
if ($duration < ($countdown + 10)) {
    jsonResponse(false, [], 'Duration en az countdown + 10 saniye olmalı');
}

if ($interval < ($duration + 30)) {
    jsonResponse(false, [], 'Interval en az duration + 30 saniye olmalı');
}

$db = new Database();

// Update settings
$result = $db->update('users', $updateData, ['id' => $userId]);

if (!$result['success']) {
    jsonResponse(false, [], 'Ayarlar güncellenemedi');
}

// Expire current active code (if any) for instant apply
$db->update('codes', ['is_active' => false], [
    'streamer_id' => $userId,
    'is_active' => 'true'
]);

// Clear cache
clearFileCache('active_code_' . $userId);

// Update next_code_time to now (so cron will create new code immediately)
$db->update('users', ['next_code_time' => date('c')], ['id' => $userId]);

logDebug('Code settings updated', [
    'user_id' => $userId,
    'countdown' => $countdown,
    'duration' => $duration,
    'interval' => $interval
]);

jsonResponse(true, [
    'countdown_duration' => $updateData['custom_countdown_duration'],
    'code_duration' => $updateData['custom_code_duration'],
    'code_interval' => $updateData['custom_code_interval']
], 'Kod ayarları güncellendi. Yeni ayarlar ~1 dakika içinde aktif olacak.');

