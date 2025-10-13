<?php
/**
 * Get Live Streamers API
 * 
 * Returns list of streamers who are currently live on Twitch
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$db = new Database();

// Get all users
$usersResult = $db->select('users', 'id,twitch_user_id,twitch_username,twitch_avatar_url');

if (!$usersResult['success']) {
    jsonResponse(false, [], 'Kullanıcılar alınamadı');
}

$users = $usersResult['data'];
$liveStreamers = [];

// Get Twitch app token
$token = getTwitchAppToken();

if (!$token) {
    jsonResponse(false, [], 'Twitch bağlantısı başarısız');
}

// Get stream info for all users (batch request)
$userIds = array_map(function($user) { return $user['twitch_user_id']; }, $users);

// Twitch API allows max 100 IDs per request
$batches = array_chunk($userIds, 100);

foreach ($batches as $batch) {
    $streamData = callTwitchAPI('streams', ['user_id' => $batch], $token);
    
    if ($streamData && isset($streamData['data'])) {
        foreach ($streamData['data'] as $stream) {
            // Find user in our database
            $user = array_filter($users, function($u) use ($stream) {
                return $u['twitch_user_id'] === $stream['user_id'];
            });
            
            if (!empty($user)) {
                $user = array_values($user)[0];
                
                $liveStreamers[] = [
                    'id' => $user['id'],
                    'twitch_user_id' => $user['twitch_user_id'],
                    'username' => $stream['user_name'],
                    'display_name' => $stream['user_name'],
                    'avatar_url' => $user['twitch_avatar_url'],
                    'is_live' => true,
                    'stream_title' => $stream['title'],
                    'game_name' => $stream['game_name'],
                    'viewer_count' => $stream['viewer_count'],
                    'thumbnail_url' => str_replace(['{width}', '{height}'], ['440', '248'], $stream['thumbnail_url']),
                    'started_at' => $stream['started_at']
                ];
            }
        }
    }
}

// Sort by viewer count
usort($liveStreamers, function($a, $b) {
    return $b['viewer_count'] - $a['viewer_count'];
});

jsonResponse(true, $liveStreamers);

