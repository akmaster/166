/**
 * Code Settings Component JavaScript
 */

(function () {
  'use strict';

  // Preset configurations
  const presets = {
    fast: { countdown: 3, duration: 15, interval: 60 },
    normal: { countdown: 5, duration: 30, interval: 300 },
    relaxed: { countdown: 10, duration: 60, interval: 600 },
  };

  // Apply preset
  document.querySelectorAll('.btn-preset').forEach((btn) => {
    btn.addEventListener('click', function () {
      const preset = this.dataset.preset;
      if (presets[preset]) {
        document.getElementById('countdown_duration').value = presets[preset].countdown;
        document.getElementById('code_duration').value = presets[preset].duration;
        document.getElementById('code_interval').value = presets[preset].interval;
      }
    });
  });

  // Save settings
  document.getElementById('save-code-settings')?.addEventListener('click', function () {
    const countdown = document.getElementById('countdown_duration').value;
    const duration = document.getElementById('code_duration').value;
    const interval = document.getElementById('code_interval').value;

    // Client-side validation
    const countdownVal = countdown === '' ? 5 : parseInt(countdown);
    const durationVal = duration === '' ? 30 : parseInt(duration);
    const intervalVal = interval === '' ? 600 : parseInt(interval);

    if (durationVal < countdownVal + 10) {
      alert('Duration en az countdown + 10 saniye olmalı');
      return;
    }

    if (intervalVal < durationVal + 30) {
      alert('Interval en az duration + 30 saniye olmalı');
      return;
    }

    // Send to server
    const formData = new FormData();
    formData.append('countdown_duration', countdown);
    formData.append('code_duration', duration);
    formData.append('code_interval', interval);

    this.disabled = true;
    this.textContent = '💾 Kaydediliyor...';

    fetch('/api/update-code-settings.php', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert(data.message);
          location.reload();
        } else {
          alert('Hata: ' + data.message);
          this.disabled = false;
          this.textContent = '💾 Ayarları Kaydet';
        }
      })
      .catch((err) => {
        alert('Bağlantı hatası');
        this.disabled = false;
        this.textContent = '💾 Ayarları Kaydet';
      });
  });
})();
