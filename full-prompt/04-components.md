# COMPONENTS - Reusable UI Modules

## 📦 COMPONENT ARCHITECTURE

Her component aynı pattern'i izler:

```
ComponentName/
├── ComponentName.php       # HTML + PHP data binding
├── ComponentName.js        # Logic, events, API calls
└── ComponentName.css       # Styles, animations
```

**Ortak Özellikler:**

- ✅ Session-aware (user data)
- ✅ Cache busting (`?v=<?php echo ASSET_VERSION; ?>`)
- ✅ Form validation (client + server)
- ✅ Status messages (success/error/warning)
- ✅ Responsive design
- ✅ Modern gradient UI

---

## 🔊 1. SOUNDSETTINGS COMPONENT

**Purpose:** Overlay ses kontrol sistemi (master toggle, ses seçimi, timing)

### PHP Structure (`SoundSettings.php`):

```php
<?php
// Load user sound settings
$soundEnabled = $user['sound_enabled'] ?? true;
$codeSound = $user['code_sound'] ?? 'threeTone';
$countdownSound = $user['countdown_sound'] ?? 'tickTock';
$codeSoundEnabled = $user['code_sound_enabled'] ?? true;
$countdownSoundEnabled = $user['countdown_sound_enabled'] ?? true;
$countdownSoundStartAt = $user['countdown_sound_start_at'] ?? 0;

// Available sounds from config
$codeSounds = AVAILABLE_CODE_SOUNDS; // 10 sounds
$countdownSounds = AVAILABLE_COUNTDOWN_SOUNDS; // 10 sounds

// Display names with emojis
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
    <!-- Master Toggle -->
    <input type="checkbox" id="soundEnabled" <?php echo $soundEnabled ? 'checked' : ''; ?>>

    <div id="soundOptions">
        <!-- Code Sound Section -->
        <div class="sound-type-section">
            <input type="checkbox" id="codeSoundEnabled" <?php echo $codeSoundEnabled ? 'checked' : ''; ?>>
            <select id="codeSound">
                <?php foreach ($codeSounds as $sound): ?>
                    <option value="<?php echo $sound; ?>"
                        <?php echo $codeSound === $sound ? 'selected' : ''; ?>>
                        <?php echo $codeSoundNames[$sound]; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn-preview" data-sound-type="code">▶️ Dinle</button>
        </div>

        <!-- Countdown Sound Section -->
        <div class="sound-type-section">
            <input type="checkbox" id="countdownSoundEnabled" <?php echo $countdownSoundEnabled ? 'checked' : ''; ?>>
            <select id="countdownSound">
                <?php foreach ($countdownSounds as $sound): ?>
                    <option value="<?php echo $sound; ?>"
                        <?php echo $countdownSound === $sound ? 'selected' : ''; ?>>
                        <?php echo $countdownSoundNames[$sound]; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn-preview" data-sound-type="countdown">▶️ Dinle</button>

            <!-- Start At Input -->
            <input type="number" id="countdownSoundStartAt"
                min="0" max="300" value="<?php echo $countdownSoundStartAt; ?>">
            <small>0 = Her saniyede | 10 = Son 10 saniyede</small>
        </div>
    </div>

    <button type="submit" id="saveSoundBtn">💾 Kaydet</button>
    <button type="button" id="resetSoundBtn">↺ Varsayılan</button>
</div>
```

### JavaScript Logic (`SoundSettings.js`):

```javascript
(function () {
  // Elements
  const form = document.getElementById('soundSettingsForm');
  const soundEnabledToggle = document.getElementById('soundEnabled');
  const codeSoundEnabled = document.getElementById('codeSoundEnabled');
  const countdownSoundEnabled = document.getElementById('countdownSoundEnabled');
  const codeSoundSelect = document.getElementById('codeSound');
  const countdownSoundSelect = document.getElementById('countdownSound');
  const countdownSoundStartAtInput = document.getElementById('countdownSoundStartAt');

  let audioContext = null;

  // Load sounds.js dynamically
  const soundsScript = document.createElement('script');
  soundsScript.src = '/overlay/sounds.js';
  document.head.appendChild(soundsScript);

  // Toggle handlers
  soundEnabledToggle.addEventListener('change', function () {
    soundOptions.classList.toggle('disabled-section', !this.checked);
  });

  codeSoundEnabled.addEventListener('change', function () {
    codeSoundOptions.classList.toggle('disabled-section', !this.checked);
  });

  countdownSoundEnabled.addEventListener('change', function () {
    countdownSoundOptions.classList.toggle('disabled-section', !this.checked);
  });

  // Preview button handlers
  document.querySelectorAll('.btn-preview').forEach((btn) => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const soundType = this.dataset.soundType;
      const sound = soundType === 'code' ? codeSoundSelect.value : countdownSoundSelect.value;
      playPreviewSound(sound, soundType);
    });
  });

  // Preview sound
  function playPreviewSound(sound, type) {
    const ctx = initAudio();
    const functionName = 'play' + sound.charAt(0).toUpperCase() + sound.slice(1);
    if (typeof window[functionName] === 'function') {
      window[functionName](ctx);
    }
  }

  // Form submission
  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = {
      sound_enabled: soundEnabledToggle.checked,
      code_sound: codeSoundSelect.value,
      countdown_sound: countdownSoundSelect.value,
      code_sound_enabled: codeSoundEnabled.checked,
      countdown_sound_enabled: countdownSoundEnabled.checked,
      countdown_sound_start_at: parseInt(countdownSoundStartAtInput.value) || 0,
    };

    try {
      const response = await fetch('/api/update-sound-settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (result.success) {
        showStatus('✅ Ses ayarları başarıyla kaydedildi!', 'success');
      } else {
        showStatus('❌ Hata: ' + (result.message || 'Bilinmeyen hata'), 'error');
      }
    } catch (error) {
      showStatus('❌ Bağlantı hatası!', 'error');
    }
  });

  // Reset to defaults
  resetBtn.addEventListener('click', function () {
    if (confirm('Varsayılanlara dön?')) {
      soundEnabledToggle.checked = true;
      codeSoundEnabled.checked = true;
      countdownSoundEnabled.checked = true;
      codeSoundSelect.value = 'threeTone';
      countdownSoundSelect.value = 'tickTock';
      countdownSoundStartAtInput.value = '0';
    }
  });
})();
```

### CSS Design (`SoundSettings.css`):

```css
.sound-settings-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.sound-type-section {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 15px;
  padding: 20px;
  margin: 20px 0;
}

/* Toggle Switch */
.toggle-slider {
  width: 60px;
  height: 30px;
  background: #ccc;
  border-radius: 30px;
  transition: all 0.3s ease;
}

.toggle-input:checked + .toggle-slider {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.toggle-slider::before {
  content: '';
  width: 24px;
  height: 24px;
  background: white;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.toggle-input:checked + .toggle-slider::before {
  transform: translateX(30px);
}

/* Disabled state */
.disabled-section {
  opacity: 0.5;
  pointer-events: none;
}
```

---

## ⏱️ 2. CODESETTINGS COMPONENT

**Purpose:** Kod zamanlama ayarları (countdown, duration, interval + presets + timing info)

### Key Features:

```php
// PHP - User settings with defaults
$countdown = getEffectiveSetting($user, 'countdown_duration'); // 0-300s
$duration = getEffectiveSetting($user, 'code_duration'); // 1-3600s
$interval = getEffectiveSetting($user, 'code_interval'); // 60-86400s
```

```javascript
// JS - Preset buttons
const presets = [
  { name: 'Hızlı', countdown: 3, duration: 20, interval: 120 },
  { name: 'Normal', countdown: 5, duration: 30, interval: 300 },
  { name: 'Yavaş', countdown: 10, duration: 60, interval: 600 },
];

// Real-time timing info calculation
function updateTimingInfo() {
  const countdown = parseInt(countdownInput.value);
  const duration = parseInt(durationInput.value);
  const interval = parseInt(intervalInput.value);

  const visibleTime = countdown + duration;
  const idleTime = interval - visibleTime;
  const totalCycle = interval;

  // Display: "Kod 35s görünür, 265s boşta, 300s toplam döngü"
}
```

**Validation:**

- Min/max checks (client + server)
- Warning messages for unusual values
- Timing info box (real-time calculation)

---

## 💰 3. RANDOMREWARD COMPONENT

**Purpose:** Rastgele ödül sistemi (min/max range)

```php
// PHP
$useRandom = $user['use_random_reward'] ?? false;
$minReward = $user['random_reward_min'] ?? 0.05;
$maxReward = $user['random_reward_max'] ?? 0.50;
```

```javascript
// JS - Toggle between fixed/random
randomToggle.addEventListener('change', function () {
  if (this.checked) {
    randomOptions.classList.remove('hidden');
    fixedRewardInput.disabled = true;
  } else {
    randomOptions.classList.add('hidden');
    fixedRewardInput.disabled = false;
  }
});

// Validation
if (minReward >= maxReward) {
  error('Min değer max değerden küçük olmalı!');
}
```

---

## 💵 4. REWARDSETTINGS COMPONENT

**Purpose:** Sabit ödül miktarı ayarlama

```php
// PHP
$rewardAmount = $user['custom_reward_amount'] ?? getSetting('reward_per_code');
```

```javascript
// JS - Simple update
form.addEventListener('submit', async function (e) {
  e.preventDefault();

  const data = { reward_amount: parseFloat(rewardInput.value) };

  const response = await fetch('/api/update-reward-amount.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });

  const result = await response.json();
  showStatus(result.success ? '✅ Kaydedildi' : '❌ Hata');
});
```

---

## 📊 5. BUDGETCALCULATOR COMPONENT

**Purpose:** Bütçe hesaplama (kaç kod, ne kadar süre, toplam maliyet)

```javascript
// JS - Real-time calculation
function calculate() {
  const rewardAmount = parseFloat(rewardInput.value);
  const duration = parseFloat(durationInput.value); // saat
  const interval = parseFloat(intervalInput.value); // dakika

  // Calculations
  const codesPerHour = 60 / interval;
  const totalCodes = codesPerHour * duration;
  const totalCost = totalCodes * rewardAmount;

  // Display
  resultsDiv.innerHTML = `
    <div class="result-item">
      <span class="result-label">Toplam Kod:</span>
      <span class="result-value">${totalCodes.toFixed(0)} adet</span>
    </div>
    <div class="result-item">
      <span class="result-label">Saatlik Kod:</span>
      <span class="result-value">${codesPerHour.toFixed(1)} adet/saat</span>
    </div>
    <div class="result-item highlight">
      <span class="result-label">Toplam Maliyet:</span>
      <span class="result-value">${totalCost.toFixed(2)} TL</span>
    </div>
  `;
}

// Apply to settings
applyBtn.addEventListener('click', function () {
  const intervalSeconds = parseFloat(intervalInput.value) * 60;

  // Update code settings
  fetch('/api/apply-budget-settings.php', {
    method: 'POST',
    body: JSON.stringify({ code_interval: intervalSeconds }),
  });
});
```

---

## 🔄 COMPONENT LOADING

**Dashboard'da nasıl kullanılır:**

```php
// index.php (Yayıncı tab)
<div class="streamer-panel">
  <?php include __DIR__ . '/components/RewardSettings/RewardSettings.php'; ?>
  <?php include __DIR__ . '/components/RandomReward/RandomReward.php'; ?>
  <?php include __DIR__ . '/components/CodeSettings/CodeSettings.php'; ?>
  <?php include __DIR__ . '/components/SoundSettings/SoundSettings.php'; ?>
  <?php include __DIR__ . '/components/BudgetCalculator/BudgetCalculator.php'; ?>
</div>
```

**Cache Busting:**

```php
// Her component kendi JS/CSS'ini yükler
<script src="/components/SoundSettings/SoundSettings.js?v=<?php echo ASSET_VERSION; ?>"></script>
<link rel="stylesheet" href="/components/SoundSettings/SoundSettings.css?v=<?php echo ASSET_VERSION; ?>">
```

---

## 📋 COMPONENT CHECKLIST

Yeni component oluştururken:

- [ ] PHP: User data binding
- [ ] PHP: Default değerler (`??` operator)
- [ ] JS: Form validation
- [ ] JS: API call (`/api/update-*.php`)
- [ ] JS: Status messages (success/error)
- [ ] CSS: Gradient design (brand colors)
- [ ] CSS: Responsive (mobile)
- [ ] Cache busting (`?v=ASSET_VERSION`)
- [ ] Error handling (try/catch)
- [ ] Reset/default button

---

**Next:** `05-api-endpoints.md` → 16 API endpoint detayları
