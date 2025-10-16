<?php
/**
 * Update Reward Amount API
 * 
 * Update streamer's custom reward amount
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$amount = $_POST['amount'] ?? null;

// NULL means use system default
if ($amount === '' || $amount === null) {
    $amount = null;
} else {
    $amount = floatval($amount);
    if (!isValidDecimal($amount, 0.01, 100)) {
        jsonResponse(false, [], 'Geçersiz miktar (0.01-100 ₺ arası)');
    }
}

$db = new Database();

$result = $db->update('users', ['custom_reward_amount' => $amount], ['id' => $userId]);

if (!$result['success']) {
    jsonResponse(false, [], 'Ödül miktarı güncellenemedi');
}

// Clear cache
clearFileCache('active_code_' . $userId);

logDebug('Reward amount updated', ['user_id' => $userId, 'amount' => $amount]);

jsonResponse(true, ['amount' => $amount], 'Ödül miktarı güncellendi');

