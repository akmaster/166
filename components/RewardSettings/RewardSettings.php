<?php
/**
 * Reward Settings Component
 * 
 * Allows streamers to set fixed reward amount
 */

$user = getCurrentUser();
if (!$user) return;

$customReward = $user['custom_reward_amount'];

// Get system default
$db = new Database();
$defaultReward = floatval($db->getSetting('reward_per_code', DEFAULT_REWARD_AMOUNT));
?>

<link rel="stylesheet" href="<?php echo baseUrl('components/RewardSettings/RewardSettings.min.css'); ?>">

<div class="reward-settings-component card">
    <h3>💰 Ödül Miktarı</h3>
    
    <div class="reward-input-group">
        <label for="reward_amount">Her Kod Başına Ödül (TL)</label>
        <div class="input-wrapper">
            <input 
                type="number" 
                id="reward_amount" 
                name="reward_amount" 
                min="0.01" 
                max="100" 
                step="0.01"
                value="<?php echo $customReward ?? ''; ?>"
                placeholder="Varsayılan: <?php echo number_format($defaultReward, 2); ?> TL"
            >
            <span class="currency-symbol">TL</span>
        </div>
        <small>İzleyiciler her kod girişinde bu miktarı kazanır (0.01-100 TL)</small>
    </div>
    
    <button id="save-reward-amount" class="btn btn-primary btn-block">
        💾 Ödül Miktarını Kaydet
    </button>
    
    <div class="current-balance">
        <strong>Mevcut Bakiyeniz:</strong>
        <span class="balance-amount"><?php echo formatCurrency($user['streamer_balance']); ?></span>
    </div>
</div>

<script src="<?php echo baseUrl('components/RewardSettings/RewardSettings.min.js'); ?>"></script>

