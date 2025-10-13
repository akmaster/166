<?php
/**
 * Request Payout API
 * 
 * Create payout request for viewer
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$db = new Database();

// Get user balance
$balance = $db->getUserBalance($userId);

// Get payout threshold
$threshold = floatval($db->getSetting('payout_threshold', DEFAULT_PAYOUT_THRESHOLD));

if ($balance < $threshold) {
    jsonResponse(false, [], "Minimum ödeme tutarı: " . formatCurrency($threshold));
}

// Check if there's already a pending request
$pendingExists = $db->exists('payout_requests', [
    'user_id' => $userId,
    'status' => 'pending'
]);

if ($pendingExists) {
    jsonResponse(false, [], 'Zaten bekleyen bir ödeme talebiniz var');
}

// Create payout request
$result = $db->insert('payout_requests', [
    'user_id' => $userId,
    'amount' => $balance
]);

if (!$result['success']) {
    jsonResponse(false, [], 'Ödeme talebi oluşturulamadı');
}

logDebug('Payout requested', ['user_id' => $userId, 'amount' => $balance]);

jsonResponse(true, [
    'amount' => $balance,
    'formatted_amount' => formatCurrency($balance)
], 'Ödeme talebiniz alındı. Onay sonrası hesabınıza yatırılacak.');

