<?php
/**
 * Random Reward Component
 * 
 * Allows streamers to enable random reward with min/max range
 */

$user = getCurrentUser();
if (!$user) return;

$useRandom = $user['use_random_reward'];
$minReward = $user['random_reward_min'];
$maxReward = $user['random_reward_max'];
?>

<link rel="stylesheet" href="<?php echo baseUrl('components/RandomReward/RandomReward.min.css'); ?>">

<div class="random-reward-component card">
    <h3>🎲 Rastgele Ödül Sistemi</h3>
    
    <div class="toggle-section">
        <label class="switch">
            <input type="checkbox" id="random_reward_enabled" <?php echo $useRandom ? 'checked' : ''; ?>>
            <span class="slider"></span>
        </label>
        <span class="toggle-label">
            Rastgele ödül <strong><?php echo $useRandom ? 'Açık' : 'Kapalı'; ?></strong>
        </span>
    </div>
    
    <div class="random-settings" style="display: <?php echo $useRandom ? 'block' : 'none'; ?>;">
        <div class="range-inputs">
            <div class="input-group">
                <label for="random_min">Minimum (₺)</label>
                <input 
                    type="number" 
                    id="random_min" 
                    min="0.05" 
                    max="10" 
                    step="0.01"
                    value="<?php echo $minReward ?? '0.10'; ?>"
                >
            </div>
            
            <div class="range-separator">-</div>
            
            <div class="input-group">
                <label for="random_max">Maximum (₺)</label>
                <input 
                    type="number" 
                    id="random_max" 
                    min="0.05" 
                    max="10" 
                    step="0.01"
                    value="<?php echo $maxReward ?? '0.20'; ?>"
                >
            </div>
        </div>
        
        <div class="info-box">
            <p>Her kullanıcı kod girdiğinde <strong>min-max</strong> arasında rastgele bir miktar kazanır.</p>
            <p><small>Örnek: 0.10-0.20 ₺ arası → Her kullanıcı farklı miktar alır</small></p>
        </div>
    </div>
    
    <button id="save-random-reward" class="btn btn-primary btn-block">
        💾 Rastgele Ödül Ayarını Kaydet
    </button>
</div>

<script src="<?php echo baseUrl('components/RandomReward/RandomReward.min.js'); ?>"></script>

