<?php
/**
 * Code Settings Component
 * 
 * Allows streamers to configure countdown, duration, and interval
 */

$user = getCurrentUser();
if (!$user) return;

$countdownDuration = $user['custom_countdown_duration'] ?? null;
$codeDuration = $user['custom_code_duration'] ?? null;
$codeInterval = $user['custom_code_interval'] ?? null;

// Get system defaults
$db = new Database();
$defaultCountdown = intval($db->getSetting('countdown_duration', DEFAULT_COUNTDOWN_DURATION));
$defaultDuration = intval($db->getSetting('code_duration', DEFAULT_CODE_DURATION));
$defaultInterval = intval($db->getSetting('code_interval', DEFAULT_CODE_INTERVAL));
?>

<link rel="stylesheet" href="<?php echo baseUrl('components/CodeSettings/CodeSettings.min.css'); ?>">

<div class="code-settings-component card">
    <h3>⏱️ Kod Ayarları</h3>
    
    <div class="settings-grid">
        <!-- Countdown Duration -->
        <div class="setting-group">
            <label for="countdown_duration">Countdown Süresi (saniye)</label>
            <input 
                type="number" 
                id="countdown_duration" 
                name="countdown_duration" 
                min="0" 
                max="300" 
                value="<?php echo $countdownDuration ?? ''; ?>"
                placeholder="Varsayılan: <?php echo $defaultCountdown; ?>s"
            >
            <small>Kod gösterilmeden önce hazırlık süresi (0-300 saniye)</small>
        </div>
        
        <!-- Code Duration -->
        <div class="setting-group">
            <label for="code_duration">Kod Süresi (saniye)</label>
            <input 
                type="number" 
                id="code_duration" 
                name="code_duration" 
                min="1" 
                max="9999999" 
                value="<?php echo $codeDuration ?? ''; ?>"
                placeholder="Varsayılan: <?php echo $defaultDuration; ?>s"
            >
            <small>Kod ekranda kalma süresi (1-9,999,999 saniye)</small>
        </div>
        
        <!-- Code Interval -->
        <div class="setting-group">
            <label for="code_interval">Kod Aralığı (saniye)</label>
            <input 
                type="number" 
                id="code_interval" 
                name="code_interval" 
                min="1" 
                max="9999999" 
                value="<?php echo $codeInterval ?? ''; ?>"
                placeholder="Varsayılan: <?php echo $defaultInterval; ?>s"
            >
            <small>Kodlar arası bekleme süresi (1-9,999,999 saniye)</small>
        </div>
    </div>
    
    <!-- Presets -->
    <div class="presets">
        <h4>Hızlı Ayarlar:</h4>
        <div class="preset-buttons">
            <button class="btn-preset" data-preset="fast">
                ⚡ Hızlı<br>
                <small>3s / 15s / 60s</small>
            </button>
            <button class="btn-preset" data-preset="normal">
                ⏰ Normal<br>
                <small>5s / 30s / 300s</small>
            </button>
            <button class="btn-preset" data-preset="relaxed">
                🏖️ Rahat<br>
                <small>10s / 60s / 600s</small>
            </button>
        </div>
    </div>
    
    <!-- Save Button -->
    <button id="save-code-settings" class="btn btn-primary btn-block">
        💾 Ayarları Kaydet
    </button>
    
    <div class="info-box">
        <strong>ℹ️ Kurallar:</strong>
        <ul>
            <li>Duration ≥ Countdown + 10 saniye</li>
            <li>Interval ≥ Duration + 30 saniye</li>
            <li>Ayarlar değişince mevcut kod expire edilir</li>
            <li>Yeni ayarlar ~1 dakika içinde aktif olur</li>
        </ul>
    </div>
</div>

<script src="<?php echo baseUrl('components/CodeSettings/CodeSettings.min.js'); ?>"></script>

