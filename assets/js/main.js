/**
 * Main JavaScript
 *
 * Handles all interactive functionality
 */

(function () {
  'use strict';

  // ===== TAB SYSTEM =====
  document.querySelectorAll('.tab-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const tabName = this.dataset.tab;

      // Update buttons
      document.querySelectorAll('.tab-btn').forEach((b) => b.classList.remove('active'));
      this.classList.add('active');

      // Update content
      document.querySelectorAll('.tab-content').forEach((content) => {
        content.classList.remove('active');
      });
      document.getElementById(tabName + '-tab')?.classList.add('active');
    });
  });

  // Dashboard tabs
  document.querySelectorAll('.dashboard-tab').forEach((tab) => {
    tab.addEventListener('click', function () {
      const tabName = this.dataset.tab;

      // Update tabs
      document.querySelectorAll('.dashboard-tab').forEach((t) => t.classList.remove('active'));
      this.classList.add('active');

      // Update panels
      document.querySelectorAll('.tab-panel').forEach((panel) => {
        panel.classList.remove('active');
      });
      document.getElementById(tabName + '-panel')?.classList.add('active');
    });
  });

  // ===== CODE SUBMISSION =====
  document.getElementById('code-submit-form')?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const codeInput = document.getElementById('code-input');
    const code = codeInput.value.trim();
    const resultDiv = document.getElementById('code-result');

    if (!code || code.length !== 6 || !/^\d{6}$/.test(code)) {
      showResult(resultDiv, 'Lütfen 6 haneli bir kod girin', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('code', code);

    try {
      const response = await fetch('/api/submit-code.php', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        showResult(resultDiv, data.message, 'success');
        codeInput.value = '';

        // Reload page after 2 seconds to update balance
        setTimeout(() => location.reload(), 2000);
      } else {
        showResult(resultDiv, data.message, 'error');
      }
    } catch (error) {
      showResult(resultDiv, 'Bağlantı hatası', 'error');
    }
  });

  // ===== PAYOUT REQUEST =====
  document.getElementById('request-payout-btn')?.addEventListener('click', async function () {
    if (!confirm('Ödeme talebinde bulunmak istediğinize emin misiniz?')) {
      return;
    }

    this.disabled = true;
    this.textContent = 'İşleniyor...';

    try {
      const response = await fetch('/api/request-payout.php', {
        method: 'POST',
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        location.reload();
      } else {
        alert('Hata: ' + data.message);
        this.disabled = false;
        this.textContent = '💸 Ödeme Talep Et';
      }
    } catch (error) {
      alert('Bağlantı hatası');
      this.disabled = false;
      this.textContent = '💸 Ödeme Talep Et';
    }
  });

  // ===== BALANCE TOPUP MODAL =====
  const topupModal = document.getElementById('topup-modal');
  const topupBtn = document.getElementById('request-topup-btn');
  const closeBtn = topupModal?.querySelector('.close');

  topupBtn?.addEventListener('click', () => {
    topupModal.classList.add('active');
    topupModal.style.display = 'flex';
  });

  closeBtn?.addEventListener('click', () => {
    topupModal.classList.remove('active');
    topupModal.style.display = 'none';
  });

  window.addEventListener('click', (e) => {
    if (e.target === topupModal) {
      topupModal.classList.remove('active');
      topupModal.style.display = 'none';
    }
  });

  // Topup form submission
  document.getElementById('topup-form')?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const amount = document.getElementById('topup-amount').value;
    const proof = document.getElementById('payment-proof').value;
    const note = document.getElementById('topup-note').value;

    const formData = new FormData();
    formData.append('amount', amount);
    formData.append('payment_proof', proof);
    formData.append('note', note);

    try {
      const response = await fetch('/api/request-topup.php', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        location.reload();
      } else {
        alert('Hata: ' + data.message);
      }
    } catch (error) {
      alert('Bağlantı hatası');
    }
  });

  // ===== THEME SELECTOR =====
  document.querySelectorAll('.theme-item').forEach((item) => {
    item.addEventListener('click', async function () {
      const theme = this.dataset.theme;

      // Visual update
      document.querySelectorAll('.theme-item').forEach((t) => t.classList.remove('active'));
      this.classList.add('active');

      // Save to server
      const formData = new FormData();
      formData.append('theme', theme);

      try {
        await fetch('/api/update-theme.php', {
          method: 'POST',
          body: formData,
        });
      } catch (error) {
        console.error('Failed to save theme');
      }
    });
  });

  // ===== COPY OVERLAY LINK =====
  window.copyOverlayLink = function () {
    const input = document.getElementById('overlay-link');
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    alert('Link kopyalandı!');
  };

  // ===== LOAD RECENT ACTIVITY =====
  async function loadRecentActivity() {
    const container = document.getElementById('recent-activity');
    if (!container) return;

    try {
      const response = await fetch('/api/get-activity.php?limit=10');
      const data = await response.json();

      if (data.success && data.data.length > 0) {
        const html =
          '<ul class="activity-list">' +
          data.data
            .map(
              (activity) => `
                        <li class="activity-item">
                            <div>
                                <div class="amount">${activity.formatted_amount}</div>
                                <div class="details">${activity.streamer_name} • ${activity.time_ago}</div>
                            </div>
                        </li>
                    `
            )
            .join('') +
          '</ul>';
        container.innerHTML = html;
      } else {
        container.innerHTML = '<p class="loading">Henüz işlem yok</p>';
      }
    } catch (error) {
      container.innerHTML = '<p class="loading">Yüklenemedi</p>';
    }
  }

  // ===== LOAD PUBLIC STATS =====
  async function loadPublicStats() {
    const container = document.getElementById('public-stats');
    if (!container) return;

    try {
      const response = await fetch('/api/get-public-stats.php');
      const data = await response.json();

      if (data.success) {
        const stats = data.data;
        container.innerHTML = `
                    <div class="stat-item">
                        <div class="stat-value">${stats.total_users}</div>
                        <div class="stat-label">Toplam Kullanıcı</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${stats.formatted_rewards}</div>
                        <div class="stat-label">Dağıtılan Ödül</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${stats.total_submissions}</div>
                        <div class="stat-label">Kod Kullanımı</div>
                    </div>
                `;
      }
    } catch (error) {
      console.error('Failed to load stats');
    }
  }

  // ===== HELPER FUNCTIONS =====
  function showResult(element, message, type) {
    element.textContent = message;
    element.className = 'result-message ' + type;
    element.style.display = 'block';

    setTimeout(() => {
      element.style.display = 'none';
    }, 5000);
  }

  // ===== INIT =====
  document.addEventListener('DOMContentLoaded', () => {
    loadRecentActivity();
    loadPublicStats();
  });
})();
