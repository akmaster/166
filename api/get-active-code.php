<?php
/**
 * Get Active Code API
 * 
 * Returns active code for a streamer (cached)
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$userId = sanitize($_GET['user_id'] ?? '');

if (empty($userId)) {
    jsonResponse(false, [], 'User ID gerekli');
}

// Check cache first
$cacheKey = 'active_code_' . $userId;
$cached = getFileCache($cacheKey, 2);

if ($cached !== null) {
    jsonResponse(true, $cached);
}

$db = new Database();

// Get active code
$result = $db->getActiveCode($userId);

if (!$result['success']) {
    $data = ['has_code' => false];
    setFileCache($cacheKey, $data, 2);
    jsonResponse(true, $data);
}

$code = $result['data'];

// Check if still in countdown or active period
// Parse UTC timestamps correctly and convert to UTC
$createdAt = new DateTime($code['created_at'], new DateTimeZone('UTC'));
$expiresAt = new DateTime($code['expires_at'], new DateTimeZone('UTC'));
$now = new DateTime('now', new DateTimeZone('UTC'));

$data = [
    'has_code' => true,
    'id' => $code['id'],
    'code' => $code['code'],
    'created_at' => $code['created_at'],
    'expires_at' => $code['expires_at'],
    'duration' => intval($code['duration']),
    'countdown_duration' => intval($code['countdown_duration']),
    'time_since_created' => $now->getTimestamp() - $createdAt->getTimestamp(),
    'time_until_expiry' => $expiresAt->getTimestamp() - $now->getTimestamp()
];

setFileCache($cacheKey, $data, 2);
jsonResponse(true, $data);

