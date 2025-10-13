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

  // Calculate and display timing information
  function updateTimingInfo() {
    const countdownInput = document.getElementById('countdown_duration');
    const durationInput = document.getElementById('code_duration');
    const intervalInput = document.getElementById('code_interval');
    const timingInfo = document.getElementById('timing-info');

    if (!countdownInput || !durationInput || !intervalInput || !timingInfo) return;

    const countdown = parseInt(countdownInput.value) || 0;
    const duration = parseInt(durationInput.value) || 0;
    const interval = parseInt(intervalInput.value) || 0;

    // Only show if all values are filled
    if (countdown === 0 && duration === 0 && interval === 0) {
      timingInfo.style.display = 'none';
      return;
    }

    // Calculate timings
    const visibleTime = countdown + duration; // Time overlay is visible
    const idleTime = interval - visibleTime; // Time overlay is hidden/idle
    const totalCycle = interval; // Total cycle time

    // Update display
    document.getElementById('display-countdown').textContent = countdown + ' saniye';
    document.getElementById('display-duration').textContent = duration + ' saniye';
    document.getElementById('visible-time').textContent = visibleTime + ' saniye';

    // Highlight idle time if significant
    const idleElement = document.getElementById('idle-time');
    if (idleTime < 0) {
      idleElement.textContent = '⚠️ HATA: Negatif! (Interval çok kısa)';
      idleElement.className = 'value danger';
    } else if (idleTime === 0) {
      idleElement.textContent = '0 saniye (Hiç boşluk yok)';
      idleElement.className = 'value success';
    } else if (idleTime > visibleTime) {
      idleElement.textContent = idleTime + ' saniye (⚠️ Görünür süreden uzun!)';
      idleElement.className = 'value warning';
    } else {
      idleElement.textContent = idleTime + ' saniye';
      idleElement.className = 'value';
    }

    document.getElementById('total-cycle').textContent = totalCycle + ' saniye';

    timingInfo.style.display = 'block';
  }

  // Add event listeners to inputs for real-time calculation
  document.getElementById('countdown_duration')?.addEventListener('input', updateTimingInfo);
  document.getElementById('code_duration')?.addEventListener('input', updateTimingInfo);
  document.getElementById('code_interval')?.addEventListener('input', updateTimingInfo);

  // Initial calculation on page load
  setTimeout(updateTimingInfo, 100);

  // Apply preset
  document.querySelectorAll('.btn-preset').forEach((btn) => {
    btn.addEventListener('click', function () {
      const preset = this.dataset.preset;
      if (presets[preset]) {
        document.getElementById('countdown_duration').value = presets[preset].countdown;
        document.getElementById('code_duration').value = presets[preset].duration;
        document.getElementById('code_interval').value = presets[preset].interval;
        updateTimingInfo(); // Update timing info after preset
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

    // Countdown validation (0-300)
    if (countdownVal < 0 || countdownVal > 300) {
      alert('Countdown süresi 0-300 saniye arası olmalıdır (Maks: 5 dakika)');
      return;
    }

    // Duration validation (1-3600)
    if (durationVal < 1 || durationVal > 3600) {
      alert('Kod süresi 1-3600 saniye arası olmalıdır (Maks: 1 saat)');
      return;
    }

    // Interval minimum check (60 seconds)
    if (intervalVal < 60) {
      alert(
        'Kod aralığı minimum 60 saniye (1 dakika) olmalıdır.\n\nSebep: Cron job 1 dakikada bir çalışıyor.'
      );
      return;
    }

    // Interval maximum check (86400 = 1 day)
    if (intervalVal > 86400) {
      alert('Kod aralığı maksimum 86400 saniye (1 gün) olabilir.');
      return;
    }

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
