<?php
/**
 * Calculate Budget API
 * 
 * Calculate optimal settings based on budget and stream duration
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$totalBudget = floatval($_POST['total_budget'] ?? 0);
$streamHours = floatval($_POST['stream_hours'] ?? 0);
$estimatedViewers = intval($_POST['estimated_viewers'] ?? 0);
$participationRate = floatval($_POST['participation_rate'] ?? 30) / 100; // Default 30%

// Validate inputs
if (!isValidDecimal($totalBudget, 1, 100000)) {
    jsonResponse(false, [], 'Bütçe: 1-100,000 TL arası');
}

if (!isValidDecimal($streamHours, 0.5, 24)) {
    jsonResponse(false, [], 'Yayın süresi: 0.5-24 saat arası');
}

if (!isValidInt($estimatedViewers, 1, 100000)) {
    jsonResponse(false, [], 'İzleyici sayısı: 1-100,000 arası');
}

// Calculate
$streamSeconds = $streamHours * 3600;

// Try different intervals to find optimal settings
$bestConfig = null;
$intervals = [60, 120, 180, 300, 600, 900, 1200, 1800]; // 1min to 30min

foreach ($intervals as $interval) {
    $codesPerStream = floor($streamSeconds / $interval);
    if ($codesPerStream < 1) continue;
    
    $expectedParticipants = $estimatedViewers * $participationRate;
    $totalClaims = $codesPerStream * $expectedParticipants;
    
    if ($totalClaims < 1) continue;
    
    $rewardPerCode = $totalBudget / $totalClaims;
    
    // Skip if reward is too small or too large
    if ($rewardPerCode < 0.05 || $rewardPerCode > 10) continue;
    
    $actualCost = $totalClaims * $rewardPerCode;
    $efficiency = ($actualCost / $totalBudget) * 100;
    
    if (!$bestConfig || abs($efficiency - 100) < abs($bestConfig['efficiency'] - 100)) {
        $bestConfig = [
            'interval' => $interval,
            'reward_amount' => round($rewardPerCode, 2),
            'codes_per_stream' => $codesPerStream,
            'expected_participants' => round($expectedParticipants),
            'total_claims' => round($totalClaims),
            'expected_cost' => round($actualCost, 2),
            'efficiency' => round($efficiency, 1)
        ];
    }
}

if (!$bestConfig) {
    jsonResponse(false, [], 'Uygun ayar bulunamadı. Parametreleri kontrol edin.');
}

// Add formatted values
$bestConfig['formatted_interval'] = formatSeconds($bestConfig['interval']);
$bestConfig['formatted_reward'] = formatCurrency($bestConfig['reward_amount']);
$bestConfig['formatted_cost'] = formatCurrency($bestConfig['expected_cost']);
$bestConfig['suggested_countdown'] = 5;
$bestConfig['suggested_duration'] = 30;

jsonResponse(true, $bestConfig, 'Bütçe hesaplandı');

