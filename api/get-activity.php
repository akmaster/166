<?php
/**
 * Get User Activity API
 * 
 * Returns user's recent submissions
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();

$userId = getCurrentUserId();
$limit = intval($_GET['limit'] ?? 10);

$db = new Database();

// Get recent submissions
$result = $db->select('submissions', '*', ['user_id' => $userId], 'submitted_at.desc', $limit);

if (!$result['success']) {
    jsonResponse(false, [], 'Aktiviteler alınamadı');
}

$activities = [];

foreach ($result['data'] as $submission) {
    // Get streamer info
    $streamerResult = $db->getUserById($submission['streamer_id']);
    $streamer = $streamerResult['success'] ? $streamerResult['data'] : null;
    
    $activities[] = [
        'id' => $submission['id'],
        'reward_amount' => floatval($submission['reward_amount']),
        'formatted_amount' => formatCurrency($submission['reward_amount']),
        'submitted_at' => $submission['submitted_at'],
        'formatted_date' => formatDate($submission['submitted_at']),
        'time_ago' => timeAgo($submission['submitted_at']),
        'streamer_name' => $streamer ? $streamer['twitch_username'] : 'Bilinmeyen'
    ];
}

jsonResponse(true, $activities);

