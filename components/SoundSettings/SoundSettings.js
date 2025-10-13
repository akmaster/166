/**
 * Sound Settings Component JavaScript
 * Handles sound preview, form submission, and toggle behavior
 */

(function () {
  'use strict';

  // Elements
  const form = document.getElementById('soundSettingsForm');
  const soundEnabledToggle = document.getElementById('soundEnabled');
  const soundOptions = document.getElementById('soundOptions');
  const codeSoundEnabled = document.getElementById('codeSoundEnabled');
  const countdownSoundEnabled = document.getElementById('countdownSoundEnabled');
  const codeSoundOptions = document.getElementById('codeSoundOptions');
  const countdownSoundOptions = document.getElementById('countdownSoundOptions');
  const codeSoundSelect = document.getElementById('codeSound');
  const countdownSoundSelect = document.getElementById('countdownSound');
  const countdownSoundStartAtInput = document.getElementById('countdownSoundStartAt');
  const saveBtn = document.getElementById('saveSoundBtn');
  const resetBtn = document.getElementById('resetSoundBtn');
  const statusMsg = document.getElementById('soundSettingsStatus');

  // Audio context for previews
  let audioContext = null;

  // Initialize audio context
  function initAudio() {
    if (!audioContext) {
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioContext;
  }

  // Load sounds.js functions dynamically
  const soundsScript = document.createElement('script');
  soundsScript.src = '/overlay/sounds.js';
  document.head.appendChild(soundsScript);

  // Toggle sound options visibility
  soundEnabledToggle.addEventListener('change', function () {
    if (this.checked) {
      soundOptions.classList.remove('disabled-section');
    } else {
      soundOptions.classList.add('disabled-section');
    }
  });

  // Toggle code sound options
  codeSoundEnabled.addEventListener('change', function () {
    if (this.checked) {
      codeSoundOptions.classList.remove('disabled-section');
    } else {
      codeSoundOptions.classList.add('disabled-section');
    }
  });

  // Toggle countdown sound options
  countdownSoundEnabled.addEventListener('change', function () {
    if (this.checked) {
      countdownSoundOptions.classList.remove('disabled-section');
    } else {
      countdownSoundOptions.classList.add('disabled-section');
    }
  });

  // Preview button handlers
  document.querySelectorAll('.btn-preview').forEach((btn) => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const soundType = this.dataset.soundType;
      const soundValue = this.dataset.sound;

      // Get current selection
      let sound;
      if (soundType === 'code') {
        sound = codeSoundSelect.value;
      } else {
        sound = countdownSoundSelect.value;
      }

      playPreviewSound(sound, soundType);
    });
  });

  // Update preview button data when selection changes
  codeSoundSelect.addEventListener('change', function () {
    const previewBtn = this.parentElement.querySelector('.btn-preview');
    previewBtn.dataset.sound = this.value;
  });

  countdownSoundSelect.addEventListener('change', function () {
    const previewBtn = this.parentElement.querySelector('.btn-preview');
    previewBtn.dataset.sound = this.value;
  });

  // Play preview sound
  function playPreviewSound(sound, type) {
    const ctx = initAudio();

    // Map sound name to function
    const functionName = 'play' + sound.charAt(0).toUpperCase() + sound.slice(1);

    if (typeof window[functionName] === 'function') {
      window[functionName](ctx);
    } else {
      console.error('Sound function not found:', functionName);
    }
  }

  // Form submission
  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    saveBtn.disabled = true;
    saveBtn.innerHTML = '⏳ Kaydediliyor...';

    // Get countdown_sound_start_at with fallback
    let countdownStartAt = 0;
    if (countdownSoundStartAtInput && countdownSoundStartAtInput.value) {
      countdownStartAt = parseInt(countdownSoundStartAtInput.value) || 0;
    }

    const formData = {
      sound_enabled: soundEnabledToggle.checked,
      code_sound: codeSoundSelect.value,
      countdown_sound: countdownSoundSelect.value,
      code_sound_enabled: codeSoundEnabled.checked,
      countdown_sound_enabled: countdownSoundEnabled.checked,
      countdown_sound_start_at: countdownStartAt,
    };

    // Debug: log form data
    console.log('Sending form data:', formData);

    try {
      const response = await fetch('/api/update-sound-settings.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (result.success) {
        showStatus('✅ Ses ayarları başarıyla kaydedildi!', 'success');
      } else {
        showStatus('❌ Hata: ' + (result.message || 'Bilinmeyen hata'), 'error');
      }
    } catch (error) {
      console.error('Save error:', error);
      showStatus('❌ Bağlantı hatası!', 'error');
    } finally {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '💾 Değişiklikleri Kaydet';
    }
  });

  // Reset to defaults
  resetBtn.addEventListener('click', function () {
    if (confirm('Tüm ses ayarlarını varsayılanlara döndürmek istediğinizden emin misiniz?')) {
      soundEnabledToggle.checked = true;
      codeSoundEnabled.checked = true;
      countdownSoundEnabled.checked = true;
      codeSoundSelect.value = 'threeTone';
      countdownSoundSelect.value = 'tickTock';
      countdownSoundStartAtInput.value = '0';
      soundOptions.classList.remove('disabled-section');
      codeSoundOptions.classList.remove('disabled-section');
      countdownSoundOptions.classList.remove('disabled-section');

      // Update preview buttons
      document.querySelector('[data-sound-type="code"]').dataset.sound = 'threeTone';
      document.querySelector('[data-sound-type="countdown"]').dataset.sound = 'tickTock';

      showStatus(
        '⚠️ Varsayılan ayarlar yüklendi. Kaydetmek için "Değişiklikleri Kaydet" butonuna tıklayın.',
        'warning'
      );
    }
  });

  // Show status message
  function showStatus(message, type) {
    statusMsg.textContent = message;
    statusMsg.className = 'status-message status-' + type;
    statusMsg.style.display = 'block';

    setTimeout(() => {
      statusMsg.style.display = 'none';
    }, 5000);
  }
})();
