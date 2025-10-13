<?php
/**
 * Admin API - Get Code Details
 * 
 * Fetch detailed information about a specific code including submissions
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/helpers.php';

header('Content-Type: application/json');

// Require admin authentication
if (!isAdmin()) {
    jsonResponse(false, [], 'Unauthorized access');
}

// Get code ID
$codeId = isset($_GET['code_id']) ? sanitize($_GET['code_id']) : '';

if (!$codeId) {
    jsonResponse(false, [], 'Code ID is required');
}

$db = new Database(true); // Use service key

// Get code details with streamer info
$codeResult = $db->query(
    "codes?select=*,users!codes_streamer_id_fkey(twitch_username,twitch_display_name)&id=eq.$codeId&limit=1"
);

if (!$codeResult['success'] || empty($codeResult['data'])) {
    jsonResponse(false, [], 'Code not found');
}

$code = $codeResult['data'][0];
$streamerName = $code['users']['twitch_display_name'] ?? 'Unknown';

// Get submissions for this code
$submissionsResult = $db->query(
    "submissions?select=*,users!submissions_user_id_fkey(twitch_username,twitch_display_name)&code_id=eq.$codeId&order=submitted_at.desc"
);

$submissions = [];
if ($submissionsResult['success']) {
    foreach ($submissionsResult['data'] as $sub) {
        $submissions[] = [
            'user_display_name' => $sub['users']['twitch_display_name'] ?? 'Unknown',
            'user_username' => $sub['users']['twitch_username'] ?? 'unknown',
            'formatted_reward' => formatCurrency($sub['reward_amount']),
            'submitted_at' => $sub['submitted_at'],
            'time_ago' => timeAgo($sub['submitted_at'])
        ];
    }
}

// Format code data
$responseData = [
    'code' => [
        'id' => $code['id'],
        'code' => $code['code'],
        'streamer_id' => $code['streamer_id'],
        'streamer_name' => $streamerName,
        'created_at' => date('Y-m-d H:i:s', strtotime($code['created_at'])),
        'expires_at' => date('Y-m-d H:i:s', strtotime($code['expires_at'])),
        'is_active' => $code['is_active'],
        'created_ago' => timeAgo($code['created_at']),
        'expires_ago' => timeAgo($code['expires_at'])
    ],
    'submissions' => $submissions,
    'submission_count' => count($submissions),
    'total_reward_distributed' => array_sum(array_column($submissionsResult['data'] ?? [], 'reward_amount'))
];

jsonResponse(true, $responseData, 'Code details retrieved successfully');

