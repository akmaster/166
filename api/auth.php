<?php
/**
 * Twitch OAuth Authentication - Redirect to Twitch
 */

require_once __DIR__ . '/../config/config.php';

$authUrl = TWITCH_OAUTH_URL . '/authorize?' . http_build_query([
    'client_id' => TWITCH_CLIENT_ID,
    'redirect_uri' => TWITCH_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'user:read:email'
]);

header('Location: ' . $authUrl);
exit;

