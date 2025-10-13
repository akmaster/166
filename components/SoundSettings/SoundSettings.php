<?php
/**
 * Sound Settings Component
 * Allows streamers to configure overlay sound settings
 */

require_once __DIR__ . '/../../config/config.php';

// Get user sound settings
$soundEnabled = $user['sound_enabled'] ?? true;
$codeSound = $user['code_sound'] ?? 'threeTone';
$countdownSound = $user['countdown_sound'] ?? 'tickTock';
$codeSoundEnabled = $user['code_sound_enabled'] ?? true;
$countdownSoundEnabled = $user['countdown_sound_enabled'] ?? true;
$countdownSoundStartAt = $user['countdown_sound_start_at'] ?? 0;

// Available sounds
$codeSounds = AVAILABLE_CODE_SOUNDS;
$countdownSounds = AVAILABLE_COUNTDOWN_SOUNDS;

// Sound display names
$codeSoundNames = [
    'threeTone' => '🎵 Three Tone (Klasik)',
    'successBell' => '🔔 Success Bell',
    'gameCoin' => '🪙 Game Coin',
    'digitalBlip' => '🤖 Digital Blip',
    'powerUp' => '⚡ Power Up',
    'notification' => '📢 Notification',
    'cheerful' => '😊 Cheerful',
    'simple' => '🔘 Simple',
    'epic' => '🎺 Epic',
    'gentle' => '🌸 Gentle'
];

$countdownSoundNames = [
    'tickTock' => '⏰ Tick Tock (Klasik)',
    'click' => '🖱️ Click',
    'beep' => '📡 Beep',
    'blip' => '🎮 Blip',
    'snap' => '🫰 Snap',
    'tap' => '👆 Tap',
    'ping' => '🔔 Ping',
    'chirp' => '🐦 Chirp',
    'pop' => '🫧 Pop',
    'tick' => '⚙️ Tick'
];
?>

<div class="sound-settings-card">
    <div class="card-header">
        <h2>🔊 Ses Ayarları</h2>
        <p>Overlay ses efektlerini özelleştirin</p>
    </div>
    
    <div class="card-body">
        <form id="soundSettingsForm">
            
            <!-- Master Sound Toggle -->
            <div class="form-group">
                <label class="toggle-label">
                    <input 
                        type="checkbox" 
                        id="soundEnabled" 
                        name="sound_enabled" 
                        <?php echo $soundEnabled ? 'checked' : ''; ?>
                        class="toggle-input"
                    >
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">Overlay Seslerini Aktif Et</span>
                </label>
                <small class="form-help">Tüm overlay seslerini açar/kapatır</small>
            </div>
            
            <div id="soundOptions" class="<?php echo !$soundEnabled ? 'disabled-section' : ''; ?>">
                
                <!-- Code Sound Settings -->
                <div class="sound-type-section">
                    <div class="sound-type-header">
                        <h3>🎵 Kod Gösterim Sesi</h3>
                        <label class="toggle-label-small">
                            <input 
                                type="checkbox" 
                                id="codeSoundEnabled" 
                                name="code_sound_enabled" 
                                <?php echo $codeSoundEnabled ? 'checked' : ''; ?>
                                class="toggle-input"
                            >
                            <span class="toggle-slider-small"></span>
                            <span class="toggle-text-small">Aktif</span>
                        </label>
                    </div>
                    
                    <div id="codeSoundOptions" class="<?php echo !$codeSoundEnabled ? 'disabled-section' : ''; ?>">
                        <div class="form-group">
                            <label for="codeSound">Ses Seçimi</label>
                            <div class="sound-select-container">
                                <select id="codeSound" name="code_sound" class="form-select">
                                    <?php foreach ($codeSounds as $sound): ?>
                                        <option 
                                            value="<?php echo $sound; ?>"
                                            <?php echo $codeSound === $sound ? 'selected' : ''; ?>
                                        >
                                            <?php echo $codeSoundNames[$sound] ?? $sound; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn-preview" data-sound-type="code" data-sound="<?php echo $codeSound; ?>">
                                    ▶️ Dinle
                                </button>
                            </div>
                            <small class="form-help">Kod ekranda göründüğünde 1 kez çalacak ses</small>
                        </div>
                    </div>
                </div>
                
                <!-- Countdown Sound Settings -->
                <div class="sound-type-section">
                    <div class="sound-type-header">
                        <h3>⏱️ Geri Sayım Sesi</h3>
                        <label class="toggle-label-small">
                            <input 
                                type="checkbox" 
                                id="countdownSoundEnabled" 
                                name="countdown_sound_enabled" 
                                <?php echo $countdownSoundEnabled ? 'checked' : ''; ?>
                                class="toggle-input"
                            >
                            <span class="toggle-slider-small"></span>
                            <span class="toggle-text-small">Aktif</span>
                        </label>
                    </div>
                    
                    <div id="countdownSoundOptions" class="<?php echo !$countdownSoundEnabled ? 'disabled-section' : ''; ?>">
                        <div class="form-group">
                            <label for="countdownSound">Ses Seçimi</label>
                            <div class="sound-select-container">
                                <select id="countdownSound" name="countdown_sound" class="form-select">
                                    <?php foreach ($countdownSounds as $sound): ?>
                                        <option 
                                            value="<?php echo $sound; ?>"
                                            <?php echo $countdownSound === $sound ? 'selected' : ''; ?>
                                        >
                                            <?php echo $countdownSoundNames[$sound] ?? $sound; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn-preview" data-sound-type="countdown" data-sound="<?php echo $countdownSound; ?>">
                                    ▶️ Dinle
                                </button>
                            </div>
                            <small class="form-help">Geri sayım sırasında her saniyede çalacak ses</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="countdownSoundStartAt">
                                ⏰ Ses Kaç Saniye Kala Başlasın?
                            </label>
                            <input 
                                type="number" 
                                id="countdownSoundStartAt" 
                                name="countdown_sound_start_at"
                                class="form-input"
                                min="<?php echo MIN_COUNTDOWN_SOUND_START_AT; ?>" 
                                max="<?php echo MAX_COUNTDOWN_SOUND_START_AT; ?>"
                                value="<?php echo $countdownSoundStartAt; ?>"
                            >
                            <small class="form-help">
                                <strong>0 = Her saniyede</strong> ses çalar (varsayılan)<br>
                                <strong>10 = Son 10 saniyede</strong> ses başlar<br>
                                💡 Uzun geri sayımlarda sadece son saniyeler için kullanışlı
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <div class="info-icon">💡</div>
                    <div class="info-content">
                        <strong>Ses Kontrolü Nasıl Çalışır?</strong>
                        <ul>
                            <li><strong>Master Toggle:</strong> Kapalıysa hiçbir ses çalmaz</li>
                            <li><strong>Kod Sesi:</strong> Açıksa kod göründüğünde 1 kez çalar</li>
                            <li><strong>Geri Sayım Sesi:</strong> Açıksa her saniyede tekrarlanır</li>
                            <li><strong>Bağımsız Kontrol:</strong> İstediğiniz sesi kapatabilirsiniz</li>
                            <li>Ses değişiklikleri anında overlay'e yansır</li>
                        </ul>
                    </div>
                </div>
                
            </div>
            
            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn-primary" id="saveSoundBtn">
                    💾 Değişiklikleri Kaydet
                </button>
                <button type="button" class="btn-secondary" id="resetSoundBtn">
                    ↺ Varsayılanlara Dön
                </button>
            </div>
            
            <!-- Status Message -->
            <div id="soundSettingsStatus" class="status-message" style="display: none;"></div>
            
        </form>
    </div>
</div>

<script src="/components/SoundSettings/SoundSettings.js?v=<?php echo ASSET_VERSION; ?>"></script>
<link rel="stylesheet" href="/components/SoundSettings/SoundSettings.css?v=<?php echo ASSET_VERSION; ?>">

