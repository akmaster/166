<?php
/**
 * Request Balance Top-up API
 * 
 * Streamer requests balance top-up
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$amount = floatval($_POST['amount'] ?? 0);
$paymentProof = sanitize($_POST['payment_proof'] ?? '');
$note = sanitize($_POST['note'] ?? '');

// Validate amount
if (!isValidDecimal($amount, 1, 10000)) {
    jsonResponse(false, [], 'Geçersiz miktar (1-10,000 ₺ arası)');
}

// Payment proof is required
if (empty($paymentProof)) {
    jsonResponse(false, [], 'Ödeme dekontu URL\'si gerekli');
}

$db = new Database();

// Create topup request
$result = $db->insert('balance_topups', [
    'streamer_id' => $userId,
    'amount' => $amount,
    'payment_proof' => $paymentProof,
    'note' => $note
]);

if (!$result['success']) {
    jsonResponse(false, [], 'Bakiye yükleme talebi oluşturulamadı');
}

logDebug('Topup requested', ['user_id' => $userId, 'amount' => $amount]);

jsonResponse(true, [
    'amount' => $amount,
    'formatted_amount' => formatCurrency($amount)
], 'Bakiye yükleme talebiniz alındı. Onay sonrası hesabınıza eklenecek.');

