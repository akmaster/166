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
                min="<?php echo MIN_COUNTDOWN_DURATION; ?>" 
                max="<?php echo MAX_COUNTDOWN_DURATION; ?>" 
                value="<?php echo $countdownDuration ?? ''; ?>"
                placeholder="Varsayılan: <?php echo $defaultCountdown; ?>s"
            >
            <small>Kod gösterilmeden önce hazırlık süresi (0-<?php echo MAX_COUNTDOWN_DURATION; ?> saniye / Maks: 5 dk)</small>
        </div>
        
        <!-- Code Duration -->
        <div class="setting-group">
            <label for="code_duration">Kod Süresi (saniye)</label>
            <input 
                type="number" 
                id="code_duration" 
                name="code_duration" 
                min="<?php echo MIN_CODE_DURATION; ?>" 
                max="<?php echo MAX_CODE_DURATION; ?>" 
                value="<?php echo $codeDuration ?? ''; ?>"
                placeholder="Varsayılan: <?php echo $defaultDuration; ?>s"
            >
            <small>Kod ekranda kalma süresi (<?php echo MIN_CODE_DURATION; ?>-<?php echo MAX_CODE_DURATION; ?> saniye / Maks: 1 saat)</small>
        </div>
        
        <!-- Code Interval -->
        <div class="setting-group">
            <label for="code_interval">Kod Aralığı (saniye)</label>
            <input 
                type="number" 
                id="code_interval" 
                name="code_interval" 
                min="<?php echo MIN_CODE_INTERVAL; ?>" 
                max="<?php echo MAX_CODE_INTERVAL; ?>" 
                value="<?php echo $codeInterval ?? ''; ?>"
                placeholder="Varsayılan: <?php echo $defaultInterval; ?>s"
            >
            <small class="text-warning">⏱️ Min: <?php echo MIN_CODE_INTERVAL; ?>s (1 dk) | Maks: <?php echo MAX_CODE_INTERVAL; ?>s (1 gün)</small>
        </div>
    </div>
    
    <!-- Presets -->
    <div class="presets">
        <h4>Hızlı Ayarlar:</h4>
        <div class="preset-buttons">
            <button class="btn-preset" data-preset="fast">
                ⚡ Hızlı<br>
                <small>3s / 15s / <?php echo MIN_CODE_INTERVAL; ?>s</small>
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
    
    <!-- Timing Info Box -->
    <div class="timing-info-box" id="timing-info" style="display: none;">
        <h4>⏱️ Kod Döngüsü Zaman Detayları</h4>
        <div class="timing-grid">
            <div class="timing-item">
                <span class="label">🟡 Countdown Süresi:</span>
                <span class="value" id="display-countdown">-</span>
            </div>
            <div class="timing-item">
                <span class="label">🟢 Kod Gösterim Süresi:</span>
                <span class="value" id="display-duration">-</span>
            </div>
            <div class="timing-item highlight">
                <span class="label">📺 Ekranda Görünür Süre:</span>
                <span class="value success" id="visible-time">-</span>
            </div>
            <div class="timing-item highlight">
                <span class="label">⚫ Boş Bekleme Süresi:</span>
                <span class="value warning" id="idle-time">-</span>
            </div>
            <div class="timing-item total">
                <span class="label">🔄 Toplam Döngü Süresi:</span>
                <span class="value" id="total-cycle">-</span>
            </div>
        </div>
        <div class="timing-explanation">
            <small>
                💡 <strong>Açıklama:</strong> Overlay, countdown + kod süresi boyunca ekranda görünür. 
                Kalan süre boyunca overlay gizli/boş olur ve bir sonraki kod için bekler.
            </small>
        </div>
    </div>
    
    <div class="info-box">
        <strong>ℹ️ Kurallar:</strong>
        <ul>
            <li>Duration ≥ Countdown + 10 saniye</li>
            <li><strong>Interval ≥ <?php echo MIN_CODE_INTERVAL; ?> saniye (1 dakika)</strong> - Cron job kısıtlaması</li>
            <li>Ayarlar değişince mevcut kod expire edilir</li>
            <li>Yeni ayarlar ~1 dakika içinde aktif olur</li>
        </ul>
    </div>
</div>

<script src="<?php echo baseUrl('components/CodeSettings/CodeSettings.min.js'); ?>"></script>

