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
                    <label>Minimum Ödeme Eşiği (TL)</label>
                    <input type="number" name="payout_threshold" step="0.01" value="<?php echo $payoutThreshold; ?>" required>
                    <small>İzleyicilerin ödeme talep edebilmesi için minimum bakiye</small>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Ödül Miktarı (TL)</label>
                    <input type="number" name="reward_per_code" step="0.01" value="<?php echo $rewardPerCode; ?>" required>
                    <small>Yayıncı özelleştirmezse kullanılan ödül miktarı</small>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Kod Süresi (saniye)</label>
                    <input type="number" name="code_duration" value="<?php echo $codeDuration; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Kod Aralığı (saniye)</label>
                    <input type="number" name="code_interval" value="<?php echo $codeInterval; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Varsayılan Countdown Süresi (saniye)</label>
                    <input type="number" name="countdown_duration" value="<?php echo $countdownDuration; ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">💾 Kaydet</button>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

