<?php
/**
 * Admin Settings Page
 */

require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = new Database(true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->updateSetting('payout_threshold', $_POST['payout_threshold']);
    $db->updateSetting('reward_per_code', $_POST['reward_per_code']);
    $db->updateSetting('code_duration', $_POST['code_duration']);
    $db->updateSetting('code_interval', $_POST['code_interval']);
    $db->updateSetting('countdown_duration', $_POST['countdown_duration']);
    
    $success = 'Ayarlar kaydedildi!';
}

// Get current settings
$payoutThreshold = $db->getSetting('payout_threshold', DEFAULT_PAYOUT_THRESHOLD);
$rewardPerCode = $db->getSetting('reward_per_code', DEFAULT_REWARD_AMOUNT);
$codeDuration = $db->getSetting('code_duration', DEFAULT_CODE_DURATION);
$codeInterval = $db->getSetting('code_interval', DEFAULT_CODE_INTERVAL);
$countdownDuration = $db->getSetting('countdown_duration', DEFAULT_COUNTDOWN_DURATION);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Admin</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container" style="padding: 2rem 0;">
        <h1>⚙️ Sistem Ayarları</h1>
        
        <?php if (isset($success)): ?>
            <div class="result-message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card" style="margin-top: 2rem;">
            <form method="POST">
                <div class="form-group">
                    <label>Minimum Ödeme Eşiği (₺)</label>
                    <input type="number" name="payout_threshold" step="0.01" value="<?php echo $payoutThreshold; ?>" required>
                    <small>İzleyicilerin ödeme talep edebilmesi için minimum bakiye</small>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Ödül Miktarı (₺)</label>
                    <input type="number" name="reward_per_code" step="0.01" value="<?php echo $rewardPerCode; ?>" required>
                    <small>Yayıncı özelleştirmezse kullanılan ödül miktarı</small>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Countdown Süresi (saniye)</label>
                    <input type="number" name="countdown_duration" min="<?php echo MIN_COUNTDOWN_DURATION; ?>" max="<?php echo MAX_COUNTDOWN_DURATION; ?>" value="<?php echo $countdownDuration; ?>" required>
                    <small style="color: #6c757d;">Hazırlık süresi: 0-<?php echo MAX_COUNTDOWN_DURATION; ?> saniye (Maks: 5 dakika)</small>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Kod Süresi (saniye)</label>
                    <input type="number" name="code_duration" min="<?php echo MIN_CODE_DURATION; ?>" max="<?php echo MAX_CODE_DURATION; ?>" value="<?php echo $codeDuration; ?>" required>
                    <small style="color: #6c757d;">Kod gösterim süresi: <?php echo MIN_CODE_DURATION; ?>-<?php echo MAX_CODE_DURATION; ?> saniye (Maks: 1 saat)</small>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Kod Aralığı (saniye)</label>
                    <input type="number" name="code_interval" min="<?php echo MIN_CODE_INTERVAL; ?>" max="<?php echo MAX_CODE_INTERVAL; ?>" value="<?php echo $codeInterval; ?>" required>
                    <small style="color: #f39c12;">⚠️ Min: <?php echo MIN_CODE_INTERVAL; ?>s (1 dk) | Maks: <?php echo MAX_CODE_INTERVAL; ?>s (1 gün) - Cron job kısıtlaması</small>
                </div>
                
                <div class="alert alert-info" style="margin: 20px 0; padding: 15px; background: #e7f3ff; border-left: 4px solid #0099ff; border-radius: 4px;">
                    <strong>ℹ️ Zaman Hesaplama Örneği:</strong><br>
                    <small style="color: #333; line-height: 1.6;">
                        Countdown: <?php echo $countdownDuration; ?>s | Duration: <?php echo $codeDuration; ?>s | Interval: <?php echo $codeInterval; ?>s<br>
                        <strong>→ Ekranda Görünür:</strong> <?php echo ($countdownDuration + $codeDuration); ?> saniye<br>
                        <strong>→ Boş Bekleme:</strong> <?php echo max(0, $codeInterval - ($countdownDuration + $codeDuration)); ?> saniye<br>
                        <strong>→ Toplam Döngü:</strong> <?php echo $codeInterval; ?> saniye<br>
                        <em style="color: #666;">* Kullanıcılar bu değerleri kendi panellerinde özelleştirebilir</em>
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">💾 Kaydet</button>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

