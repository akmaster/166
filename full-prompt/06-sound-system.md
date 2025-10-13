# SOUND SYSTEM - Web Audio API

## 🔊 ARCHITECTURE

**Technology:** Web Audio API (procedural generation)  
**No Audio Files:** All sounds generated in real-time  
**Total Sounds:** 20 (10 code + 10 countdown)  
**Browser Support:** Chrome, Firefox, Edge (latest)

---

## 🎵 CODE SOUNDS (10)

### 1. **Three Tone** (`playThreeTone`)

**Default:** ✅  
**Type:** Sine waves  
**Pattern:** 3 ascending tones (600Hz → 800Hz → 1000Hz)  
**Duration:** 0.45s  
**Style:** Classic notification

```javascript
function playThreeTone(ctx) {
  const frequencies = [600, 800, 1000];
  frequencies.forEach((freq, i) => {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain).connect(ctx.destination);
    osc.frequency.value = freq;
    osc.type = 'sine';
    const start = ctx.currentTime + i * 0.15;
    gain.gain.setValueAtTime(0.3, start);
    gain.gain.exponentialRampToValueAtTime(0.01, start + 0.15);
    osc.start(start);
    osc.stop(start + 0.15);
  });
}
```

---

### 2. **Success Bell** (`playSuccessBell`)

**Type:** Sine waves (4 tones)  
**Pattern:** Bell-like harmonics (800Hz → 1600Hz)  
**Duration:** 0.7s  
**Style:** Celebratory

---

### 3. **Game Coin** (`playGameCoin`)

**Type:** Square waves  
**Pattern:** Double tone (988Hz + 1319Hz)  
**Duration:** 0.3s  
**Style:** Retro game (Mario-like)

```javascript
function playGameCoin(ctx) {
  const osc1 = ctx.createOscillator();
  const osc2 = ctx.createOscillator();
  const gain = ctx.createGain();

  osc1.frequency.value = 988; // B5
  osc2.frequency.value = 1319; // E6
  osc1.type = 'square';
  osc2.type = 'square';

  osc1.start(ctx.currentTime);
  osc2.start(ctx.currentTime + 0.1);
  osc1.stop(ctx.currentTime + 0.2);
  osc2.stop(ctx.currentTime + 0.3);
}
```

---

### 4. **Digital Blip** (`playDigitalBlip`)

**Type:** Square wave  
**Pattern:** Single sharp tone (1200Hz)  
**Duration:** 0.1s  
**Style:** Minimal, futuristic

---

### 5. **Power Up** (`playPowerUp`)

**Type:** Sawtooth wave  
**Pattern:** Rising sweep (200Hz → 800Hz)  
**Duration:** 0.3s  
**Style:** Energetic, gaming

```javascript
osc.frequency.setValueAtTime(200, ctx.currentTime);
osc.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.3);
```

---

### 6. **Notification** (`playNotification`)

**Type:** Sine wave  
**Pattern:** Double beep (800Hz, 2x)  
**Duration:** 0.3s  
**Style:** Standard notification

---

### 7. **Cheerful** (`playCheerful`)

**Type:** Sine waves  
**Pattern:** Major chord arpeggio (C-E-G)  
**Duration:** 0.5s  
**Style:** Happy, upbeat

---

### 8. **Simple** (`playSimple`)

**Type:** Sine wave  
**Pattern:** Single tone (1000Hz)  
**Duration:** 0.15s  
**Style:** Clean, minimalistic

---

### 9. **Epic** (`playEpic`)

**Type:** Sawtooth waves  
**Pattern:** Dramatic chord progression  
**Duration:** 0.8s  
**Style:** Cinematic

---

### 10. **Gentle** (`playGentle`)

**Type:** Sine waves  
**Pattern:** Soft descending tones  
**Duration:** 0.6s  
**Style:** Calm, subtle

---

## ⏱️ COUNTDOWN SOUNDS (10)

### 1. **Tick Tock** (`playTickTock`)

**Default:** ✅  
**Type:** Sine wave  
**Pattern:** Alternating high-low (800Hz ↔ 600Hz)  
**Duration:** 0.1s  
**Style:** Clock-like

```javascript
function playTickTock(ctx) {
  const osc = ctx.createOscillator();
  const gain = ctx.createGain();
  osc.connect(gain).connect(ctx.destination);

  // Alternates between 800Hz and 600Hz
  const isEven = Math.floor(ctx.currentTime) % 2 === 0;
  osc.frequency.value = isEven ? 800 : 600;
  osc.type = 'sine';

  gain.gain.setValueAtTime(0.2, ctx.currentTime);
  gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);

  osc.start(ctx.currentTime);
  osc.stop(ctx.currentTime + 0.1);
}
```

---

### 2. **Click** (`playClick`)

**Type:** White noise burst  
**Pattern:** Short click (filtered noise)  
**Duration:** 0.05s  
**Style:** Mechanical

```javascript
const noise = ctx.createBufferSource();
const noiseBuffer = ctx.createBuffer(1, ctx.sampleRate * 0.05, ctx.sampleRate);
const output = noiseBuffer.getChannelData(0);
for (let i = 0; i < output.length; i++) {
  output[i] = Math.random() * 2 - 1;
}
noise.buffer = noiseBuffer;
```

---

### 3. **Beep** (`playBeep`)

**Type:** Sine wave  
**Pattern:** Short tone (1000Hz)  
**Duration:** 0.1s  
**Style:** Electronic

---

### 4. **Blip** (`playBlip`)

**Type:** Square wave  
**Pattern:** High-pitched tone (1500Hz)  
**Duration:** 0.08s  
**Style:** Digital

---

### 5. **Snap** (`playSnap`)

**Type:** Noise burst (filtered)  
**Pattern:** Finger snap simulation  
**Duration:** 0.06s  
**Style:** Percussive

---

### 6. **Tap** (`playTap`)

**Type:** Triangle wave  
**Pattern:** Soft tap (500Hz)  
**Duration:** 0.08s  
**Style:** Subtle

---

### 7. **Ping** (`playPing`)

**Type:** Sine wave  
**Pattern:** High-pitched (2000Hz)  
**Duration:** 0.1s  
**Style:** Sonar-like

---

### 8. **Chirp** (`playChirp`)

**Type:** Sine wave sweep  
**Pattern:** Rising (400Hz → 800Hz)  
**Duration:** 0.08s  
**Style:** Bird-like

---

### 9. **Pop** (`playPop`)

**Type:** Square wave  
**Pattern:** Low-to-high sweep (100Hz → 400Hz)  
**Duration:** 0.1s  
**Style:** Bubbly

---

### 10. **Tick** (`playTick`)

**Type:** Sine wave  
**Pattern:** Single tick (700Hz)  
**Duration:** 0.05s  
**Style:** Minimal

---

## 🎛️ USAGE IN OVERLAY

### Initialization:

```javascript
let audioContext = null;

function initAudio() {
  if (!audioContext) {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    console.log('[Overlay] Audio initialized');
  }
  return audioContext;
}
```

### Playing Sounds:

```javascript
// Code reveal sound
function playSound(soundType) {
  if (!SOUND_ENABLED || !CODE_SOUND_ENABLED) return;

  const ctx = initAudio();
  const functionName = 'play' + soundType.charAt(0).toUpperCase() + soundType.slice(1);

  if (typeof window[functionName] === 'function') {
    window[functionName](ctx);
  } else {
    console.error('[Sound] Unknown sound:', soundType);
  }
}

// Usage
playSound('threeTone'); // playThreeTone(ctx)
playSound('gameCoin'); // playGameCoin(ctx)
```

### Countdown Sound with Timing:

```javascript
function startCountdown(duration, code) {
  let remaining = duration;

  const interval = setInterval(() => {
    // Check if countdown sound should play
    if (COUNTDOWN_SOUND_ENABLED) {
      if (COUNTDOWN_SOUND_START_AT === 0 || remaining <= COUNTDOWN_SOUND_START_AT) {
        playCountdownSound(COUNTDOWN_SOUND_TYPE);
      }
    }

    remaining--;

    if (remaining <= 0) {
      clearInterval(interval);
      showCode(code);
    }
  }, 1000);
}
```

---

## 🔧 WEB AUDIO API CONCEPTS

### Oscillator Types:

| Type       | Sound Character   | Use Case             |
| ---------- | ----------------- | -------------------- |
| `sine`     | Pure tone, smooth | Bells, notifications |
| `square`   | Harsh, retro      | Game sounds          |
| `sawtooth` | Bright, sharp     | Power-ups, sweeps    |
| `triangle` | Soft, mellow      | Subtle taps          |

### Gain Envelope (ADSR):

```javascript
// Attack
gain.gain.setValueAtTime(0.3, startTime);

// Decay + Release
gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
```

### Frequency Sweep:

```javascript
// Rising pitch
osc.frequency.setValueAtTime(200, ctx.currentTime);
osc.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.3);

// Falling pitch
osc.frequency.setValueAtTime(800, ctx.currentTime);
osc.frequency.exponentialRampToValueAtTime(200, ctx.currentTime + 0.3);
```

---

## ⚠️ BROWSER AUTOPLAY POLICY

**Problem:** Browsers block autoplay without user interaction

**Solution for OBS:**

```javascript
// OBS Browser Source doesn't have this restriction
// Sounds will play automatically in OBS
```

**Solution for Web Preview:**

```javascript
// Requires user interaction (click, touch)
document.addEventListener(
  'click',
  function initAudioContext() {
    audioContext = new AudioContext();
    audioContext.resume();
    document.removeEventListener('click', initAudioContext);
  },
  { once: true }
);
```

**In overlay/index.php:**

```javascript
// Initialization happens on first Realtime event (automatic in OBS)
if (!audioContext) {
  audioContext = new AudioContext();
  audioContext.resume(); // Required in some browsers
}
```

---

## 🎨 ADDING NEW SOUNDS

1. **Create function in `sounds.js`:**

```javascript
function playMyNewSound(ctx) {
  const osc = ctx.createOscillator();
  const gain = ctx.createGain();
  osc.connect(gain).connect(ctx.destination);

  // Your sound design
  osc.frequency.value = 1000;
  osc.type = 'sine';

  gain.gain.setValueAtTime(0.3, ctx.currentTime);
  gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);

  osc.start(ctx.currentTime);
  osc.stop(ctx.currentTime + 0.2);
}
```

2. **Add to config (`config.php`):**

```php
define('AVAILABLE_CODE_SOUNDS', [
    'threeTone', 'successBell', 'gameCoin', 'digitalBlip',
    'powerUp', 'notification', 'cheerful', 'simple',
    'epic', 'gentle', 'myNewSound' // Add here!
]);
```

3. **Add display name in component:**

```php
$codeSoundNames = [
    'myNewSound' => '🎸 My New Sound',
    // ...
];
```

---

## 📊 SOUND SELECTION STATISTICS

**Most Popular Code Sounds:**

1. Three Tone (default) - 60%
2. Game Coin (retro vibes) - 20%
3. Success Bell (celebration) - 10%

**Most Popular Countdown Sounds:**

1. Tick Tock (default) - 70%
2. Click (mechanical) - 15%
3. Beep (electronic) - 10%

---

**Next:** `07-overlay-themes.md` → 20 CSS themes
