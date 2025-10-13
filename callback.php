<?php
/**
 * Twitch OAuth Callback Handler
 * 
 * Handles the redirect from Twitch OAuth
 */

require_once __DIR__ . '/config/config.php';

// Check for authorization code
if (!isset($_GET['code'])) {
    die('Authorization failed. No code provided.');
}

$code = $_GET['code'];

// Exchange code for access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, TWITCH_OAUTH_URL . '/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => TWITCH_CLIENT_ID,
    'client_secret' => TWITCH_CLIENT_SECRET,
    'code' => $code,
    'grant_type' => 'authorization_code',
    'redirect_uri' => TWITCH_REDIRECT_URI
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);

if (!isset($tokenData['access_token'])) {
    die('Failed to obtain access token.');
}

$accessToken = $tokenData['access_token'];

// Get user info from Twitch
$userData = callTwitchAPI('users', [], $accessToken);

if (!$userData || empty($userData['data'])) {
    die('Failed to get user information.');
}

$twitchUser = $userData['data'][0];

// Check if user exists in database
$db = new Database();
$existingUser = $db->getUserByTwitchId($twitchUser['id']);

if ($existingUser['success']) {
    // User exists, update info
    $userId = $existingUser['data']['id'];
    
    $db->update('users', [
        'twitch_username' => $twitchUser['login'],
        'twitch_email' => $twitchUser['email'] ?? null,
        'twitch_avatar_url' => $twitchUser['profile_image_url']
    ], ['id' => $userId]);
    
    logDebug('User logged in', ['user_id' => $userId, 'username' => $twitchUser['login']]);
} else {
    // New user, create account
    $overlayToken = generateToken(64);
    
    $result = $db->insert('users', [
        'twitch_user_id' => $twitchUser['id'],
        'twitch_username' => $twitchUser['login'],
        'twitch_email' => $twitchUser['email'] ?? null,
        'twitch_avatar_url' => $twitchUser['profile_image_url'],
        'overlay_token' => $overlayToken
    ]);
    
    if (!$result['success']) {
        die('Failed to create user account.');
    }
    
    $userId = $result['data'][0]['id'];
    
    logDebug('New user registered', ['user_id' => $userId, 'username' => $twitchUser['login']]);
}

// Set session
$_SESSION['user_id'] = $userId;
$_SESSION['twitch_username'] = $twitchUser['login'];
$_SESSION['twitch_avatar'] = $twitchUser['profile_image_url'];

// Redirect to dashboard
header('Location: ' . APP_URL);
exit;

