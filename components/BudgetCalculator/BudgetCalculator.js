/**
 * Budget Calculator Component JavaScript
 */

(function () {
  'use strict';

  let calculationData = null;

  // Calculate budget
  document.getElementById('calculate-budget')?.addEventListener('click', function () {
    const totalBudget = parseFloat(document.getElementById('total_budget').value);
    const streamHours = parseFloat(document.getElementById('stream_hours').value);
    const estimatedViewers = parseInt(document.getElementById('estimated_viewers').value);
    const participationRate = parseFloat(document.getElementById('participation_rate').value);

    // Validate
    if (isNaN(totalBudget) || totalBudget < 1 || totalBudget > 100000) {
      alert('Bütçe: 1-100,000 TL arası olmalı');
      return;
    }

    if (isNaN(streamHours) || streamHours < 0.5 || streamHours > 24) {
      alert('Yayın süresi: 0.5-24 saat arası olmalı');
      return;
    }

    if (isNaN(estimatedViewers) || estimatedViewers < 1 || estimatedViewers > 100000) {
      alert('İzleyici sayısı: 1-100,000 arası olmalı');
      return;
    }

    if (isNaN(participationRate) || participationRate < 1 || participationRate > 100) {
      alert('Katılım oranı: 1-100% arası olmalı');
      return;
    }

    const formData = new FormData();
    formData.append('total_budget', totalBudget);
    formData.append('stream_hours', streamHours);
    formData.append('estimated_viewers', estimatedViewers);
    formData.append('participation_rate', participationRate);

    this.disabled = true;
    this.textContent = '🧮 Hesaplanıyor...';

    fetch('/api/calculate-budget.php', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          calculationData = data.data;
          displayResults(data.data);
        } else {
          alert('Hata: ' + data.message);
        }
        this.disabled = false;
        this.textContent = '🧮 Hesapla';
      })
      .catch((err) => {
        alert('Bağlantı hatası');
        this.disabled = false;
        this.textContent = '🧮 Hesapla';
      });
  });

  // Display results
  function displayResults(data) {
    document.getElementById('result-reward').textContent = data.formatted_reward;
    document.getElementById('result-interval').textContent = data.formatted_interval;
    document.getElementById('result-codes').textContent = data.codes_per_stream;
    document.getElementById('result-participants').textContent = data.expected_participants;
    document.getElementById('result-cost').textContent = data.formatted_cost;
    document.getElementById('result-efficiency').textContent = data.efficiency + '%';

    document.getElementById('calculation-results').style.display = 'block';
  }

  // Apply settings
  document.getElementById('apply-budget-settings')?.addEventListener('click', function () {
    if (!calculationData) {
      alert('Önce hesaplama yapın');
      return;
    }

    const formData = new FormData();
    formData.append('reward_amount', calculationData.reward_amount);
    formData.append('code_interval', calculationData.interval);
    formData.append('code_duration', calculationData.suggested_duration);
    formData.append('countdown_duration', calculationData.suggested_countdown);

    this.disabled = true;
    this.textContent = '✅ Uygulanıyor...';

    fetch('/api/apply-budget-settings.php', {
      method: 'POST',
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert('Ayarlar başarıyla uygulandı! Sayfa yenilenecek.');
          location.reload();
        } else {
          alert('Hata: ' + data.message);
          this.disabled = false;
          this.textContent = '✅ Bu Ayarları Uygula';
        }
      })
      .catch((err) => {
        alert('Bağlantı hatası');
        this.disabled = false;
        this.textContent = '✅ Bu Ayarları Uygula';
      });
  });
})();
