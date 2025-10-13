<?php
/**
 * Apply Budget Settings API
 * 
 * Apply calculated budget settings to user account
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$rewardAmount = floatval($_POST['reward_amount'] ?? 0);
$codeInterval = intval($_POST['code_interval'] ?? 0);
$codeDuration = intval($_POST['code_duration'] ?? 30);
$countdownDuration = intval($_POST['countdown_duration'] ?? 5);

// Validate
if (!isValidDecimal($rewardAmount, 0.01, 100)) {
    jsonResponse(false, [], 'Geçersiz ödül miktarı');
}

if (!isValidInt($codeInterval, 60, 9999999)) {
    jsonResponse(false, [], 'Geçersiz interval');
}

if (!isValidInt($codeDuration, 10, 9999999)) {
    jsonResponse(false, [], 'Geçersiz duration');
}

if (!isValidInt($countdownDuration, 0, 300)) {
    jsonResponse(false, [], 'Geçersiz countdown');
}

$db = new Database();

$result = $db->update('users', [
    'custom_reward_amount' => $rewardAmount,
    'custom_code_interval' => $codeInterval,
    'custom_code_duration' => $codeDuration,
    'custom_countdown_duration' => $countdownDuration,
    'use_random_reward' => false
], ['id' => $userId]);

if (!$result['success']) {
    jsonResponse(false, [], 'Ayarlar uygulanamadı');
}

// Expire current code and clear cache
$db->update('codes', ['is_active' => false], [
    'streamer_id' => $userId,
    'is_active' => 'true'
]);
clearFileCache('active_code_' . $userId);
$db->update('users', ['next_code_time' => date('c')], ['id' => $userId]);

logDebug('Budget settings applied', [
    'user_id' => $userId,
    'reward' => $rewardAmount,
    'interval' => $codeInterval
]);

jsonResponse(true, [], 'Bütçe ayarları uygulandı');

