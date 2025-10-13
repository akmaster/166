<?php
/**
 * Update Sound Settings API
 * Allows streamers to update their overlay sound preferences
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/helpers.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Config yükleme hatası: ' . $e->getMessage()]);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check JSON decode error
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON parse hatası: ' . json_last_error_msg()]);
    exit;
}

// Debug mode logging
if (DEBUG_MODE) {
    error_log('Sound Settings Update - Received data: ' . print_r($data, true));
}

// Validate input - check each parameter separately for better error messages
// countdown_sound_start_at is optional for backward compatibility
$requiredParams = ['sound_enabled', 'code_sound', 'countdown_sound', 'code_sound_enabled', 'countdown_sound_enabled'];
$missingParams = [];

foreach ($requiredParams as $param) {
    if (!isset($data[$param])) {
        $missingParams[] = $param;
    }
}

if (!empty($missingParams)) {
    http_response_code(400);
    
    // Debug: log what was received
    error_log('Missing params: ' . implode(', ', $missingParams));
    error_log('Received params: ' . implode(', ', array_keys($data ?? [])));
    error_log('Full data: ' . print_r($data, true));
    
    echo json_encode([
        'success' => false, 
        'message' => 'Eksik parametreler: ' . implode(', ', $missingParams),
        'received' => array_keys($data ?? []),
        'debug_data' => DEBUG_MODE ? $data : null
    ]);
    exit;
}

$soundEnabled = (bool) $data['sound_enabled'];
$codeSound = $data['code_sound'];
$countdownSound = $data['countdown_sound'];
$codeSoundEnabled = (bool) $data['code_sound_enabled'];
$countdownSoundEnabled = (bool) $data['countdown_sound_enabled'];
// Backward compatible: if not provided, default to 0
$countdownSoundStartAt = isset($data['countdown_sound_start_at']) ? (int) $data['countdown_sound_start_at'] : 0;

// Check if sound constants are defined
if (!defined('AVAILABLE_CODE_SOUNDS') || !defined('AVAILABLE_COUNTDOWN_SOUNDS')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ses sabitleri tanımlı değil']);
    exit;
}

// Validate sound types
if (!in_array($codeSound, AVAILABLE_CODE_SOUNDS)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Geçersiz kod sesi: ' . $codeSound,
        'available' => AVAILABLE_CODE_SOUNDS
    ]);
    exit;
}

if (!in_array($countdownSound, AVAILABLE_COUNTDOWN_SOUNDS)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Geçersiz geri sayım sesi: ' . $countdownSound,
        'available' => AVAILABLE_COUNTDOWN_SOUNDS
    ]);
    exit;
}

// Validate countdown sound start at range (only if provided)
if (isset($data['countdown_sound_start_at'])) {
    if ($countdownSoundStartAt < MIN_COUNTDOWN_SOUND_START_AT || $countdownSoundStartAt > MAX_COUNTDOWN_SOUND_START_AT) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Geçersiz ses başlama süresi. ' . MIN_COUNTDOWN_SOUND_START_AT . '-' . MAX_COUNTDOWN_SOUND_START_AT . ' saniye arasında olmalı.'
        ]);
        exit;
    }
}

// Update database
$db = new Database();

try {
    $result = $db->update('users', [
        'sound_enabled' => $soundEnabled,
        'code_sound' => $codeSound,
        'countdown_sound' => $countdownSound,
        'code_sound_enabled' => $codeSoundEnabled,
        'countdown_sound_enabled' => $countdownSoundEnabled,
        'countdown_sound_start_at' => $countdownSoundStartAt
    ], ['id' => $userId]);
    
    if (!$result) {
        throw new Exception('Veritabanı güncelleme hatası');
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Ses ayarları başarıyla güncellendi',
        'data' => [
            'sound_enabled' => $soundEnabled,
            'code_sound' => $codeSound,
            'countdown_sound' => $countdownSound,
            'code_sound_enabled' => $codeSoundEnabled,
            'countdown_sound_enabled' => $countdownSoundEnabled,
            'countdown_sound_start_at' => $countdownSoundStartAt
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ses ayarları güncellenirken hata oluştu: ' . $e->getMessage()
    ]);
}
