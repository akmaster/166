<?php
/**
 * Admin API - Generate Code Manually
 * 
 * Generates codes for selected streamer(s) and sends to their overlays via Realtime
 */

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/helpers.php';

header('Content-Type: application/json');

// Require admin authentication
if (!isAdmin()) {
    jsonResponse(false, [], 'Unauthorized access');
}

// Get form data
$streamerId = sanitize($_POST['streamer_id'] ?? '');
$customCode = sanitize($_POST['custom_code'] ?? '');
$countdownDuration = isset($_POST['countdown_duration']) ? (int)$_POST['countdown_duration'] : 5;
$codeDuration = isset($_POST['code_duration']) ? (int)$_POST['code_duration'] : 30;

// Validate
if (empty($streamerId)) {
    jsonResponse(false, [], 'Yayıncı seçimi zorunludur');
}

// Validate durations
if ($countdownDuration < 0 || $countdownDuration > 30) {
    jsonResponse(false, [], 'Countdown süresi 0-30 saniye arası olmalıdır');
}

if ($codeDuration < 10 || $codeDuration > 300) {
    jsonResponse(false, [], 'Kod süresi 10-300 saniye arası olmalıdır');
}

// Validate custom code if provided
if (!empty($customCode)) {
    if (!preg_match('/^\d{6}$/', $customCode)) {
        jsonResponse(false, [], 'Özel kod 6 haneli sayısal olmalıdır');
    }
}

$db = new Database(true); // Use service key

// Get streamer(s)
$streamers = [];
if ($streamerId === 'all') {
    // Get all streamers (no balance check for admin manual codes)
    $result = $db->select('users', 'id,twitch_display_name,streamer_balance', 'id=neq.00000000-0000-0000-0000-000000000000');
    if ($result['success']) {
        $streamers = $result['data'];
    }
} else {
    // Get single streamer
    $result = $db->selectOne('users', 'id,twitch_display_name,streamer_balance', ['id' => $streamerId]);
    if ($result['success']) {
        $streamers[] = $result['data'];
    }
}

if (empty($streamers)) {
    jsonResponse(false, [], 'Yayıncı bulunamadı');
}

$codesGenerated = 0;
$errors = [];

// Generate codes for each streamer
foreach ($streamers as $streamer) {
    // Admin manual codes: NO balance check, NO balance deduction
    // Balance will only be checked when viewer submits the code
    
    // Check if streamer has an active code (within countdown + duration window)
    $activeCodeCheck = $db->getActiveCode($streamer['id']);
    
    if ($activeCodeCheck['success']) {
        $errors[] = $streamer['twitch_display_name'] . ': Zaten aktif bir kod var. Lütfen kod bitene kadar bekleyin.';
        continue;
    }
    
    // Generate or use custom code
    $code = !empty($customCode) ? $customCode : generateCode();
    
    // Calculate expiry time (MUST use UTC for Supabase!)
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $expiresAt = (clone $now)->modify("+{$countdownDuration} seconds")->modify("+{$codeDuration} seconds");
    
    // Insert code
    $codeData = [
        'streamer_id' => $streamer['id'],
        'code' => $code,
        'expires_at' => $expiresAt->format('Y-m-d\TH:i:s.u\Z'),
        'duration' => $codeDuration,
        'countdown_duration' => $countdownDuration,
        'is_active' => true,
        'is_bonus_code' => true, // Admin bonus code - no balance deduction
        'created_at' => $now->format('Y-m-d\TH:i:s.u\Z')
    ];
    
    $insertResult = $db->insert('codes', $codeData);
    
    if ($insertResult['success']) {
        $codesGenerated++;
        
        // Log
        if (DEBUG_MODE) {
            logDebug('Admin manual code generated', [
                'streamer_id' => $streamer['id'],
                'streamer_name' => $streamer['twitch_display_name'],
                'code' => $code,
                'countdown' => $countdownDuration,
                'duration' => $codeDuration,
                'note' => 'Manual admin code - no balance deduction'
            ]);
        }
    } else {
        $errors[] = $streamer['twitch_display_name'] . ': Kod oluşturulamadı';
    }
}

// Prepare response
if ($codesGenerated > 0) {
    $message = $codesGenerated === 1 
        ? '🎁 Bonus kod başarıyla gönderildi! (Yayıncı bakiyesi düşmeyecek)' 
        : "🎁 $codesGenerated yayıncıya bonus kod gönderildi! (Bakiye düşümü olmayacak)";
    
    $responseData = [
        'codes_generated' => $codesGenerated,
        'total_streamers' => count($streamers),
        'is_bonus_code' => true,
        'errors' => $errors
    ];
    
    if (!empty($errors)) {
        $message .= ' (' . count($errors) . ' hata oluştu)';
    }
    
    jsonResponse(true, $responseData, $message);
} else {
    $errorMessage = 'Hiçbir kod oluşturulamadı';
    if (!empty($errors)) {
        $errorMessage .= ': ' . implode(', ', $errors);
    }
    jsonResponse(false, ['errors' => $errors], $errorMessage);
}

