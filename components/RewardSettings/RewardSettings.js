/**
 * Reward Settings Component JavaScript
 */

(function () {
  'use strict';

  document.getElementById('save-reward-amount')?.addEventListener('click', function () {
    const amount = document.getElementById('reward_amount').value;

    // Validate
    if (amount !== '' && (parseFloat(amount) < 0.01 || parseFloat(amount) > 100)) {
      alert('Ödül miktarı 0.01-100 TL arası olmalı');
      return;
    }

    const formData = new FormData();
    formData.append('amount', amount);

    this.disabled = true;
    this.textContent = '💾 Kaydediliyor...';

    fetch('/api/update-reward-amount.php', {
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
          this.textContent = '💾 Ödül Miktarını Kaydet';
        }
      })
      .catch((err) => {
        alert('Bağlantı hatası');
        this.disabled = false;
        this.textContent = '💾 Ödül Miktarını Kaydet';
      });
  });
})();
