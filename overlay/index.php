<?php
/**
 * OBS Overlay
 * 
 * Real-time code display with Supabase Realtime
 * - 3D card flip animation
 * - 20 theme support
 * - Sound system
 * - Debug panel
 */

// Minimal config (no session needed for overlay)
require_once __DIR__ . '/../config/config.php';

$token = $_GET['token'] ?? '';
$theme = $_GET['theme'] ?? '';

if (empty($token)) {
    die('Token required');
}

// Get user by token
$db = new Database();
$userResult = $db->getUserByToken($token);

if (!$userResult['success']) {
    die('Invalid token');
}

$streamer = $userResult['data'];
$effectiveTheme = !empty($theme) ? $theme : $streamer['overlay_theme'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1920, height=1080">
    <title>Code Overlay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            width: 1920px;
            height: 1080px;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
            background: transparent;
        }
        
        /* Theme Variables */
        :root {
            --theme-primary: #9147ff;
            --theme-secondary: #00b8d4;
            --theme-accent: #00d4aa;
        }
        
        /* Themes */
        <?php include __DIR__ . '/themes.css'; ?>
        
        /* Main Container */
        .overlay-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            padding: 40px;
        }
        
        /* Card Container */
        .card-container {
            perspective: 1000px;
            width: 700px;
            height: 400px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        .card-container.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .card-flipper {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            transform-style: preserve-3d;
        }
        
        .card-container.flipped .card-flipper {
            transform: rotateY(180deg);
        }
        
        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            padding: 40px;
        }
        
        .card-front {
            /* Countdown side */
        }
        
        .card-back {
            transform: rotateY(180deg);
            /* Code side */
        }
        
        .countdown-text {
            font-size: 120px;
            font-weight: 900;
            color: white;
            text-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            animation: pulse 1s infinite;
        }
        
        .countdown-label {
            font-size: 30px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .code-display {
            font-size: 100px;
            font-weight: 900;
            color: white;
            letter-spacing: 10px;
            text-shadow: 0 5px 30px rgba(0, 0, 0, 0.7);
        }
        
        .code-label {
            font-size: 30px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        @keyframes glow {
            0%, 100% {
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px var(--theme-accent);
            }
            50% {
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 80px var(--theme-accent);
            }
        }
        
        .card-face.active {
            animation: glow 2s infinite;
        }
        
        /* Debug Panel */
        .debug-panel {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.9);
            color: #0f0;
            padding: 15px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 14px;
            max-width: 400px;
            z-index: 1000;
            display: none;
        }
        
        .debug-panel h4 {
            margin-bottom: 10px;
            color: #0ff;
        }
        
        .debug-panel .debug-item {
            margin: 5px 0;
        }
        
        .debug-panel .status-ok {
            color: #0f0;
        }
        
        .debug-panel .status-error {
            color: #f00;
        }
        
        .debug-panel .status-warning {
            color: #ff0;
        }
    </style>
</head>
<body class="theme-<?php echo $effectiveTheme; ?>">
    
    <div class="overlay-container">
        <div class="card-container" id="codeCard">
            <div class="card-flipper">
                <!-- Front: Countdown -->
                <div class="card-face card-front">
                    <div class="countdown-text" id="countdownNumber">5</div>
                    <div class="countdown-label">Hazır Olun...</div>
                </div>
                
                <!-- Back: Code -->
                <div class="card-face card-back">
                    <div class="code-display" id="codeNumber">000000</div>
                    <div class="code-label">Kodu Gir!</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Debug Panel -->
    <div class="debug-panel" id="debugPanel">
        <h4>🔧 Debug Panel</h4>
        <div class="debug-item">Status: <span id="debug-status" class="status-ok">Initializing...</span></div>
        <div class="debug-item">Connection: <span id="debug-connection">-</span></div>
        <div class="debug-item">Theme: <span><?php echo $effectiveTheme; ?></span></div>
        <div class="debug-item">Sound: <span id="debug-sound">Enabled</span></div>
        <div class="debug-item">Countdown: <span id="debug-countdown">-</span></div>
        <div class="debug-item">Duration: <span id="debug-duration">-</span></div>
        <div class="debug-item">Next Code: <span id="debug-next">-</span></div>
        <div class="debug-item">Last Update: <span id="debug-lastupdate">-</span></div>
    </div>
    
    <!-- Supabase JS Client -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    
    <script>
        // Configuration
        const SUPABASE_URL = '<?php echo SUPABASE_URL; ?>';
        const SUPABASE_ANON_KEY = '<?php echo SUPABASE_ANON_KEY; ?>';
        const STREAMER_ID = '<?php echo $streamer['id']; ?>';
        const SOUND_ENABLED = <?php echo ($streamer['sound_enabled'] ?? true) ? 'true' : 'false'; ?>;
        const SOUND_TYPE = '<?php echo $streamer['code_sound'] ?? 'threeTone'; ?>';
        const COUNTDOWN_SOUND_TYPE = '<?php echo $streamer['countdown_sound'] ?? 'tickTock'; ?>';
        const CODE_SOUND_ENABLED = <?php echo ($streamer['code_sound_enabled'] ?? true) ? 'true' : 'false'; ?>;
        const COUNTDOWN_SOUND_ENABLED = <?php echo ($streamer['countdown_sound_enabled'] ?? true) ? 'true' : 'false'; ?>;
        const COUNTDOWN_SOUND_START_AT = <?php echo (int)($streamer['countdown_sound_start_at'] ?? 0); ?>;
        
        // Initialize Supabase
        const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
        
        // State
        let currentCode = null;
        let countdownInterval = null;
        let durationInterval = null;
        let audioContext = null;
        
        // Debug logging
        function debug(message, status = 'ok') {
            console.log('[Overlay]', message);
            const statusEl = document.getElementById('debug-status');
            if (statusEl) {
                statusEl.textContent = message;
                statusEl.className = 'status-' + status;
            }
            document.getElementById('debug-lastupdate').textContent = new Date().toLocaleTimeString();
        }
        
        // Initialize Audio Context
        function initAudio() {
            if (!audioContext && SOUND_ENABLED) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                debug('Audio initialized');
            }
        }
        
        // Sound Functions (Web Audio API)
        <?php include __DIR__ . '/sounds.js'; ?>
        
        // Play sound
        function playSound(type) {
            // Check master toggle AND code sound toggle
            if (!SOUND_ENABLED || !CODE_SOUND_ENABLED || !audioContext) return;
            
            switch(type) {
                case 'threeTone':
                    playThreeTone(audioContext);
                    break;
                case 'successBell':
                    playSuccessBell(audioContext);
                    break;
                case 'gameCoin':
                    playGameCoin(audioContext);
                    break;
                case 'digitalBlip':
                    playDigitalBlip(audioContext);
                    break;
                case 'powerUp':
                    playPowerUp(audioContext);
                    break;
                case 'notification':
                    playNotification(audioContext);
                    break;
                case 'cheerful':
                    playCheerful(audioContext);
                    break;
                case 'simple':
                    playSimple(audioContext);
                    break;
                case 'epic':
                    playEpic(audioContext);
                    break;
                case 'gentle':
                    playGentle(audioContext);
                    break;
            }
        }
        
        function playCountdownSound(type) {
            // Check master toggle AND countdown sound toggle
            if (!SOUND_ENABLED || !COUNTDOWN_SOUND_ENABLED || !audioContext) return;
            
            switch(type) {
                case 'tickTock':
                    playTickTock(audioContext);
                    break;
                case 'click':
                    playClick(audioContext);
                    break;
                case 'beep':
                    playBeep(audioContext);
                    break;
                case 'blip':
                    playBlip(audioContext);
                    break;
                case 'snap':
                    playSnap(audioContext);
                    break;
                case 'tap':
                    playTap(audioContext);
                    break;
                case 'ping':
                    playPing(audioContext);
                    break;
                case 'chirp':
                    playChirp(audioContext);
                    break;
                case 'pop':
                    playPop(audioContext);
                    break;
                case 'tick':
                    playTick(audioContext);
                    break;
            }
        }
        
        // Handle new code from Realtime
        function handleNewCode(code) {
            // Prevent duplicate processing
            if (lastCodeId === code.id) {
                return;
            }
            
            lastCodeId = code.id;
            debug('New code received: ' + code.code);
            currentCode = code;
            
            const countdownDuration = parseInt(code.countdown_duration);
            const codeDuration = parseInt(code.duration);
            
            document.getElementById('debug-countdown').textContent = countdownDuration + 's';
            document.getElementById('debug-duration').textContent = codeDuration + 's';
            
            // Show the card container
            const card = document.getElementById('codeCard');
            card.classList.add('visible');
            
            // Start countdown
            if (countdownDuration > 0) {
                startCountdown(countdownDuration, code.code, codeDuration);
            } else {
                // No countdown, show code immediately
                showCode(code.code, codeDuration);
            }
        }
        
        // Start countdown
        function startCountdown(duration, code, codeDuration) {
            // Clear any existing timers
            if (countdownInterval) clearInterval(countdownInterval);
            if (durationInterval) clearTimeout(durationInterval);
            
            let remaining = duration;
            const card = document.getElementById('codeCard');
            const countdownNum = document.getElementById('countdownNumber');
            
            // Reset to front
            card.classList.remove('flipped');
            countdownNum.textContent = remaining;
            
            initAudio();
            
            // Play sound at start (if should start immediately)
            if (COUNTDOWN_SOUND_START_AT === 0 || remaining <= COUNTDOWN_SOUND_START_AT) {
                playCountdownSound(COUNTDOWN_SOUND_TYPE);
            }
            
            countdownInterval = setInterval(() => {
                remaining--;
                countdownNum.textContent = remaining;
                
                // Play countdown sound based on start_at setting
                if (remaining > 0) {
                    // If start_at is 0, play every second
                    // If start_at > 0, only play when remaining <= start_at
                    if (COUNTDOWN_SOUND_START_AT === 0 || remaining <= COUNTDOWN_SOUND_START_AT) {
                        playCountdownSound(COUNTDOWN_SOUND_TYPE);
                    }
                }
                
                if (remaining <= 0) {
                    clearInterval(countdownInterval);
                    showCode(code, codeDuration);
                }
            }, 1000);
        }
        
        // Show code
        function showCode(code, duration) {
            // Clear any existing timers
            if (countdownInterval) clearInterval(countdownInterval);
            if (durationInterval) clearTimeout(durationInterval);
            
            const card = document.getElementById('codeCard');
            const codeNum = document.getElementById('codeNumber');
            
            codeNum.textContent = code;
            
            // Flip card
            setTimeout(() => {
                card.classList.add('flipped');
                playSound(SOUND_TYPE);
                debug('Code showing: ' + code);
            }, 100);
            
            // Hide after duration
            durationInterval = setTimeout(() => {
                card.classList.remove('flipped');
                debug('Code expired');
                
                // Hide the entire card container after a short delay
                setTimeout(() => {
                    card.classList.remove('visible');
                    debug('Overlay hidden');
                }, 1000);
            }, duration * 1000);
        }
        
        // Subscribe to Realtime
        function subscribeToRealtime() {
            debug('Subscribing to Realtime...');
            document.getElementById('debug-connection').textContent = 'Realtime';
            
            const channel = supabase
                .channel('codes-changes')
                .on(
                    'postgres_changes',
                    {
                        event: 'INSERT',
                        schema: 'public',
                        table: 'codes',
                        filter: `streamer_id=eq.${STREAMER_ID}`
                    },
                    (payload) => {
                        if (payload.new && payload.new.is_active) {
                            handleNewCode(payload.new);
                        }
                    }
                )
                .subscribe((status) => {
                    if (status === 'SUBSCRIBED') {
                        debug('Realtime connected', 'ok');
                    } else if (status === 'CHANNEL_ERROR') {
                        debug('Realtime error, falling back to polling', 'error');
                        startPolling();
                    }
                });
        }
        
        // Fallback: Polling
        let pollingInterval = null;
        let lastCodeId = null;
        let isProcessingCode = false;
        
        function startPolling() {
            document.getElementById('debug-connection').textContent = 'Polling';
            pollingInterval = setInterval(checkForCode, 5000);
        }
        
        async function checkForCode() {
            // Prevent multiple simultaneous checks
            if (isProcessingCode) return;
            
            try {
                const response = await fetch(`/api/get-active-code.php?user_id=${STREAMER_ID}&_t=${Date.now()}`);
                const data = await response.json();
                
                if (data.success && data.data && data.data.has_code) {
                    const code = data.data;
                    
                    // Check if it's a new code or resuming existing code
                    if (!lastCodeId || lastCodeId !== code.id) {
                        isProcessingCode = true;
                        lastCodeId = code.id;
                        
                        // Use time_since_created from API (already calculated server-side)
                        const elapsedSeconds = parseInt(code.time_since_created);
                        const countdownDuration = parseInt(code.countdown_duration);
                        const codeDuration = parseInt(code.duration);
                        const totalDuration = countdownDuration + codeDuration;
                        
                        // Check if code is still valid
                        if (elapsedSeconds >= 0 && elapsedSeconds < totalDuration) {
                            debug('Code found, resuming from ' + elapsedSeconds + 's');
                            resumeCode(code, elapsedSeconds);
                        } else {
                            debug('Code expired or invalid time, ignoring');
                        }
                        
                        isProcessingCode = false;
                    }
                }
            } catch (error) {
                debug('Polling error', 'error');
                isProcessingCode = false;
            }
        }
        
        // Resume code from specific time
        function resumeCode(code, elapsedSeconds) {
            currentCode = code;
            
            const countdownDuration = parseInt(code.countdown_duration);
            const codeDuration = parseInt(code.duration);
            
            document.getElementById('debug-countdown').textContent = countdownDuration + 's';
            document.getElementById('debug-duration').textContent = codeDuration + 's';
            
            const card = document.getElementById('codeCard');
            card.classList.add('visible');
            
            // Still in countdown phase
            if (elapsedSeconds < countdownDuration) {
                const remainingCountdown = countdownDuration - elapsedSeconds;
                debug('Resuming countdown: ' + remainingCountdown + 's left');
                startCountdown(remainingCountdown, code.code, codeDuration);
            } 
            // In code display phase
            else if (elapsedSeconds < (countdownDuration + codeDuration)) {
                const remainingCodeTime = (countdownDuration + codeDuration) - elapsedSeconds;
                debug('Resuming code display: ' + remainingCodeTime + 's left');
                showCode(code.code, remainingCodeTime);
            }
        }
        
        // Initialize
        debug('Overlay initialized');
        subscribeToRealtime();
        
        // Initial check for active code
        checkForCode();
    </script>
</body>
</html>

