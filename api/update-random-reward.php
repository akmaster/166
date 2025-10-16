<?php
/**
 * Update Random Reward API
 * 
 * Enable/disable random reward and set min/max
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
$min = floatval($_POST['min'] ?? 0);
$max = floatval($_POST['max'] ?? 0);

$updateData = ['use_random_reward' => $enabled];

if ($enabled) {
    // Validate min/max
    if (!isValidDecimal($min, 0.05, 10)) {
        jsonResponse(false, [], 'Min: 0.05-10 ₺ arası');
    }
    
    if (!isValidDecimal($max, 0.05, 10)) {
        jsonResponse(false, [], 'Max: 0.05-10 ₺ arası');
    }
    
    if ($min >= $max) {
        jsonResponse(false, [], 'Max değer, min değerden büyük olmalı');
    }
    
    $updateData['random_reward_min'] = $min;
    $updateData['random_reward_max'] = $max;
} else {
    $updateData['random_reward_min'] = null;
    $updateData['random_reward_max'] = null;
}

$db = new Database();

$result = $db->update('users', $updateData, ['id' => $userId]);

if (!$result['success']) {
    jsonResponse(false, [], 'Rastgele ödül ayarları güncellenemedi');
}

logDebug('Random reward updated', [
    'user_id' => $userId,
    'enabled' => $enabled,
    'min' => $min,
    'max' => $max
]);

jsonResponse(true, [
    'enabled' => $enabled,
    'min' => $enabled ? $min : null,
    'max' => $enabled ? $max : null
], 'Rastgele ödül ayarları güncellendi');

