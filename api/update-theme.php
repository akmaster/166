<?php
/**
 * Update Theme API
 * 
 * Update streamer's overlay theme
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method', 405);
}

requireLogin();

$userId = getCurrentUserId();
$theme = sanitize($_POST['theme'] ?? 'neon');

// Valid themes
$validThemes = [
    // Game themes
    'valorant', 'league', 'csgo', 'dota2', 'pubg',
    'fortnite', 'apex', 'minecraft', 'gta', 'fifa',
    // Color themes
    'neon', 'sunset', 'ocean', 'purple', 'cherry',
    'minimal', 'dark', 'sakura', 'cyber', 'arctic'
];

if (!in_array($theme, $validThemes)) {
    jsonResponse(false, [], 'Geçersiz tema');
}

$db = new Database();

$result = $db->update('users', ['overlay_theme' => $theme], ['id' => $userId]);

if (!$result['success']) {
    jsonResponse(false, [], 'Tema güncellenemedi');
}

logDebug('Theme updated', ['user_id' => $userId, 'theme' => $theme]);

jsonResponse(true, ['theme' => $theme], 'Tema güncellendi');

