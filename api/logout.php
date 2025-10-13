<?php
/**
 * Logout API
 * 
 * Destroy user session
 */

require_once __DIR__ . '/../config/config.php';

session_unset();
session_destroy();

header('Location: ' . APP_URL);
exit;

