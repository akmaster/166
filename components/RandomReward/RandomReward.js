/**
 * Random Reward Component JavaScript
 */

(function () {
  'use strict';

  const toggle = document.getElementById('random_reward_enabled');
  const settingsDiv = document.querySelector('.random-settings');

  // Toggle visibility
  toggle?.addEventListener('change', function () {
    settingsDiv.style.display = this.checked ? 'block' : 'none';
    document.querySelector('.toggle-label strong').textContent = this.checked ? 'Açık' : 'Kapalı';
  });

  // Save settings
  document.getElementById('save-random-reward')?.addEventListener('click', function () {
    const enabled = document.getElementById('random_reward_enabled').checked;
    const min = parseFloat(document.getElementById('random_min').value);
    const max = parseFloat(document.getElementById('random_max').value);

    // Validate if enabled
    if (enabled) {
      if (isNaN(min) || min < 0.05 || min > 10) {
        alert('Minimum: 0.05-10 TL arası olmalı');
        return;
      }

      if (isNaN(max) || max < 0.05 || max > 10) {
        alert('Maximum: 0.05-10 TL arası olmalı');
        return;
      }

      if (min >= max) {
        alert('Maximum değer, minimum değerden büyük olmalı');
        return;
      }
    }

    const formData = new FormData();
    formData.append('enabled', enabled);
    formData.append('min', enabled ? min : 0);
    formData.append('max', enabled ? max : 0);

    this.disabled = true;
    this.textContent = '💾 Kaydediliyor...';

    fetch('/api/update-random-reward.php', {
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
          this.textContent = '💾 Rastgele Ödül Ayarını Kaydet';
        }
      })
      .catch((err) => {
        alert('Bağlantı hatası');
        this.disabled = false;
        this.textContent = '💾 Rastgele Ödül Ayarını Kaydet';
      });
  });
})();
