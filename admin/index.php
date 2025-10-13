<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Require admin authentication
requireAdmin();

$db = new Database(true);

// Get stats
$totalUsers = $db->count('users')['count'] ?? 0;
$totalCodes = $db->count('codes')['count'] ?? 0;

$submissionsResult = $db->select('submissions', 'reward_amount');
$totalRewards = 0;
if ($submissionsResult['success']) {
    foreach ($submissionsResult['data'] as $sub) {
        $totalRewards += floatval($sub['reward_amount']);
    }
}

$pendingPayouts = $db->count('payout_requests', 'status=eq.pending')['count'] ?? 0;
$pendingTopups = $db->count('balance_topups', 'status=eq.pending')['count'] ?? 0;

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1>📊 Dashboard</h1>
        <p>Sistem genel görünümü ve istatistikler</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
            <div class="stat-label">Toplam Kullanıcı</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">💎</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalCodes); ?></div>
            <div class="stat-label">Toplam Kod</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo formatCurrency($totalRewards); ?></div>
            <div class="stat-label">Dağıtılan Ödül</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($pendingPayouts); ?></div>
            <div class="stat-label">Bekleyen Ödemeler</div>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));">
    <!-- Pending Payouts Card -->
    <div class="card">
        <div class="card-header">
            <h3>💸 Bekleyen Ödemeler</h3>
            <span class="badge badge-warning"><?php echo $pendingPayouts; ?></span>
        </div>
        
        <?php if ($pendingPayouts > 0): ?>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                <?php echo $pendingPayouts; ?> ödeme talebi onay bekliyor.
            </p>
            <a href="/admin/payouts.php" class="btn btn-primary">Ödeme Taleplerini Görüntüle</a>
        <?php else: ?>
            <div class="empty-state" style="padding: 40px 20px;">
                <div class="empty-icon" style="font-size: 3rem;">✅</div>
                <p style="color: var(--text-secondary);">Bekleyen ödeme talebi yok</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pending Topups Card -->
    <div class="card">
        <div class="card-header">
            <h3>📥 Bekleyen Bakiye Yüklemeleri</h3>
            <span class="badge badge-warning"><?php echo $pendingTopups; ?></span>
        </div>
        
        <?php if ($pendingTopups > 0): ?>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                <?php echo $pendingTopups; ?> bakiye yükleme talebi onay bekliyor.
            </p>
            <a href="/admin/balance-topups.php" class="btn btn-primary">Bakiye Taleplerini Görüntüle</a>
        <?php else: ?>
            <div class="empty-state" style="padding: 40px 20px;">
                <div class="empty-icon" style="font-size: 3rem;">✅</div>
                <p style="color: var(--text-secondary);">Bekleyen bakiye yükleme yok</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <h3>⚡ Hızlı İşlemler</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
        <a href="/admin/users.php" class="btn btn-secondary">
            👥 Kullanıcıları Yönet
        </a>
        <a href="/admin/codes.php" class="btn btn-secondary">
            💎 Kodları Görüntüle
        </a>
        <a href="/admin/payouts.php" class="btn btn-secondary">
            💸 Ödemeleri İncele
        </a>
        <a href="/admin/balance-topups.php" class="btn btn-secondary">
            💰 Bakiye Yüklemeleri
        </a>
        <a href="/admin/settings.php" class="btn btn-secondary">
            ⚙️ Sistem Ayarları
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
