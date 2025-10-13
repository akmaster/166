<?php
/**
 * Get Public Stats API
 * 
 * Returns public statistics for landing page
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$db = new Database();

// Total users
$usersResult = $db->count('users');
$totalUsers = $usersResult['success'] ? $usersResult['count'] : 0;

// Total codes distributed
$codesResult = $db->count('codes');
$totalCodes = $codesResult['success'] ? $codesResult['count'] : 0;

// Total submissions
$submissionsResult = $db->select('submissions', 'reward_amount');
$totalSubmissions = 0;
$totalRewardsDistributed = 0;

if ($submissionsResult['success']) {
    $totalSubmissions = count($submissionsResult['data']);
    foreach ($submissionsResult['data'] as $sub) {
        $totalRewardsDistributed += floatval($sub['reward_amount']);
    }
}

// Active streamers (who have codes)
$activeStreamersResult = $db->select('codes', 'DISTINCT streamer_id');
$activeStreamers = $activeStreamersResult['success'] ? count($activeStreamersResult['data']) : 0;

$stats = [
    'total_users' => $totalUsers,
    'total_codes' => $totalCodes,
    'total_submissions' => $totalSubmissions,
    'total_rewards_distributed' => round($totalRewardsDistributed, 2),
    'formatted_rewards' => formatCurrency($totalRewardsDistributed),
    'active_streamers' => $activeStreamers
];

jsonResponse(true, $stats);

