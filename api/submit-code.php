<?php
/**
 * Submit Code API
 * 
 * Handles code submission from viewers
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

$code = sanitize($_POST['code'] ?? '');

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$userId = $isLoggedIn ? getCurrentUserId() : null;

// Validate code format
if (!isValidCode($code)) {
    jsonResponse(false, [], 'Geçersiz kod formatı (6 haneli rakam olmalı)');
}

$db = new Database();

// Find active code
$codeResult = $db->select('codes', '*', [
    'code' => $code,
    'is_active' => 'true'
]);

if (!$codeResult['success'] || empty($codeResult['data'])) {
    jsonResponse(false, [], 'Kod bulunamadı', 200, 'code_not_found');
}

$codeData = $codeResult['data'][0];
$streamerId = $codeData['streamer_id'];
$isBonusCode = isset($codeData['is_bonus_code']) && $codeData['is_bonus_code'] === true;

// Check if code is still valid (using UTC time)
$now = new DateTime('now', new DateTimeZone('UTC'));
$createdAt = new DateTime($codeData['created_at'], new DateTimeZone('UTC'));
$countdownDuration = intval($codeData['countdown_duration']);
$codeDuration = intval($codeData['duration']);
$timeSinceCreated = $now->getTimestamp() - $createdAt->getTimestamp();

// Check if code expired (countdown + duration)
$totalDuration = $countdownDuration + $codeDuration;
if ($timeSinceCreated >= $totalDuration) {
    jsonResponse(false, [], 'Kodun süresi dolmuş', 200, 'expired_code');
}

if ($timeSinceCreated < $countdownDuration) {
    $remaining = $countdownDuration - $timeSinceCreated;
    jsonResponse(false, [], "Kod henüz aktif değil. $remaining saniye bekleyin.", 200, 'invalid_code');
}

// If user is not logged in, return login required message
if (!$isLoggedIn) {
    jsonResponse(false, [], 'Giriş gerekli', 200, 'login_required');
}

// Check if user already submitted this code
$alreadySubmitted = $db->exists('submissions', [
    'user_id' => $userId,
    'code_id' => $codeData['id']
]);

if ($alreadySubmitted) {
    jsonResponse(false, [], 'Bu kodu zaten kullandınız', 200, 'used_code');
}

// Get streamer data
$streamerResult = $db->getUserById($streamerId);
if (!$streamerResult['success']) {
    jsonResponse(false, [], 'Yayıncı bulunamadı');
}

$streamer = $streamerResult['data'];

// Calculate reward
$rewardAmount = calculateReward($streamer);

// For BONUS codes (admin sent), skip balance check and deduction
if (!$isBonusCode) {
    // Regular code: Check and deduct streamer balance
    if (floatval($streamer['streamer_balance']) < $rewardAmount) {
        jsonResponse(false, [], 'Yayıncının bakiyesi yetersiz');
    }
    
    // Deduct from streamer balance
    $newBalance = floatval($streamer['streamer_balance']) - $rewardAmount;
    $updateResult = $db->update('users', ['streamer_balance' => $newBalance], ['id' => $streamerId]);
    
    if (!$updateResult['success']) {
        jsonResponse(false, [], 'Bakiye güncellenemedi');
    }
}

// Create submission
$submissionResult = $db->insert('submissions', [
    'user_id' => $userId,
    'code_id' => $codeData['id'],
    'streamer_id' => $streamerId,
    'reward_amount' => $rewardAmount
]);

if (!$submissionResult['success']) {
    // Rollback streamer balance (only if not a bonus code)
    if (!$isBonusCode) {
        $db->update('users', ['streamer_balance' => $streamer['streamer_balance']], ['id' => $streamerId]);
    }
    jsonResponse(false, [], 'Kod kaydedilemedi');
}

logDebug('Code submitted', [
    'user_id' => $userId,
    'code' => $code,
    'reward' => $rewardAmount,
    'is_bonus_code' => $isBonusCode,
    'balance_deducted' => !$isBonusCode,
    'time_since_created' => $timeSinceCreated,
    'total_duration' => $totalDuration,
    'remaining_time' => $totalDuration - $timeSinceCreated
]);

jsonResponse(true, [
    'reward_amount' => $rewardAmount,
    'formatted_amount' => formatCurrency($rewardAmount)
], 'Tebrikler! ' . formatCurrency($rewardAmount) . ' kazandınız!');

