<?php
/**
 * User Dashboard
 * 
 * Main dashboard for logged-in users (viewer/streamer tabs)
 */

require_once __DIR__ . '/../config/config.php';

// Require login
requireLogin();

$user = getCurrentUser();
$userBalance = (new Database())->getUserBalance($user['id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rumb</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
</head>
<body>
    
    <!-- DASHBOARD -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🎮 Rumb</h1>
            </div>
            <div class="nav-user">
                <img src="<?php echo $user['twitch_avatar_url']; ?>" alt="Avatar" class="user-avatar">
                <span><?php echo $user['twitch_username']; ?></span>
                <a href="/streamers.php" class="btn btn-secondary btn-sm">Canlı Yayıncılar</a>
                <a href="/api/logout.php" class="btn btn-outline btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <!-- Tab System -->
        <div class="dashboard-tabs">
            <button class="dashboard-tab active" data-tab="viewer">👁️ İzleyici</button>
            <button class="dashboard-tab" data-tab="streamer">🎙️ Yayıncı</button>
        </div>
        
        <!-- VIEWER TAB -->
        <div class="tab-panel active" id="viewer-panel">
            <div class="container">
                <div class="dashboard-grid">
                    <!-- Code Entry Card -->
                    <div class="card">
                        <h3>🔢 Kod Gir</h3>
                        <form id="code-submit-form">
                            <input 
                                type="text" 
                                id="code-input" 
                                placeholder="6 haneli kod" 
                                maxlength="6" 
                                pattern="\d{6}"
                                class="code-input"
                                required
                            >
                            <button type="submit" class="btn btn-primary btn-block">Gönder</button>
                        </form>
                        <div id="code-result" class="result-message"></div>
                    </div>
                    
                    <!-- Balance Card -->
                    <div class="card balance-card">
                        <h3>💰 Bakiyeniz</h3>
                        <div class="balance-amount"><?php echo formatCurrency($userBalance); ?></div>
                        <?php
                        $db = new Database();
                        $threshold = floatval($db->getSetting('payout_threshold', DEFAULT_PAYOUT_THRESHOLD));
                        ?>
                        <?php if ($userBalance >= $threshold): ?>
                            <button id="request-payout-btn" class="btn btn-success btn-block">
                                💸 Ödeme Talep Et
                            </button>
                        <?php else: ?>
                            <p class="threshold-info">
                                Minimum ödeme: <?php echo formatCurrency($threshold); ?><br>
                                <small>Kalan: <?php echo formatCurrency($threshold - $userBalance); ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="card full-width">
                        <h3>📊 Son İşlemler</h3>
                        <div id="recent-activity">
                            <p class="loading">Yükleniyor...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- STREAMER TAB -->
        <div class="tab-panel" id="streamer-panel">
            <div class="container">
                <!-- Streamer Balance & Topup -->
                <div class="card streamer-balance">
                    <h3>💳 Yayıncı Bakiyesi</h3>
                    <div class="balance-amount"><?php echo formatCurrency($user['streamer_balance']); ?></div>
                    <button id="request-topup-btn" class="btn btn-primary">
                        ➕ Bakiye Yükle
                    </button>
                </div>
                
                <!-- Overlay Link -->
                <div class="card">
                    <h3>🎬 OBS Overlay Linki</h3>
                    <div class="overlay-link-box">
                        <input 
                            type="text" 
                            id="overlay-link" 
                            value="<?php echo APP_URL; ?>/overlay/?token=<?php echo $user['overlay_token']; ?>" 
                            readonly
                        >
                        <button class="btn btn-secondary" onclick="copyOverlayLink()">📋 Kopyala</button>
                    </div>
                    <small>OBS → Browser Source → Bu linki yapıştır (1920x1080)</small>
                </div>
                
                <!-- Theme Selector -->
                <div class="card">
                    <h3>🎨 Tema Seçimi</h3>
                    <div class="theme-grid" id="theme-selector">
                        <?php
                        $themes = [
                            ['id' => 'valorant', 'name' => 'Valorant', 'icon' => '🔴'],
                            ['id' => 'league', 'name' => 'League of Legends', 'icon' => '⚔️'],
                            ['id' => 'csgo', 'name' => 'CS:GO', 'icon' => '🔫'],
                            ['id' => 'dota2', 'name' => 'Dota 2', 'icon' => '🛡️'],
                            ['id' => 'pubg', 'name' => 'PUBG', 'icon' => '🎯'],
                            ['id' => 'fortnite', 'name' => 'Fortnite', 'icon' => '🏗️'],
                            ['id' => 'apex', 'name' => 'Apex Legends', 'icon' => '🏆'],
                            ['id' => 'minecraft', 'name' => 'Minecraft', 'icon' => '⛏️'],
                            ['id' => 'gta', 'name' => 'GTA V', 'icon' => '🚗'],
                            ['id' => 'fifa', 'name' => 'FIFA', 'icon' => '⚽'],
                            ['id' => 'neon', 'name' => 'Neon', 'icon' => '💜'],
                            ['id' => 'sunset', 'name' => 'Sunset', 'icon' => '🌅'],
                            ['id' => 'ocean', 'name' => 'Ocean', 'icon' => '🌊'],
                            ['id' => 'purple', 'name' => 'Purple', 'icon' => '💜'],
                            ['id' => 'cherry', 'name' => 'Cherry', 'icon' => '🍒'],
                            ['id' => 'minimal', 'name' => 'Minimal', 'icon' => '⚪'],
                            ['id' => 'dark', 'name' => 'Dark', 'icon' => '⚫'],
                            ['id' => 'sakura', 'name' => 'Sakura', 'icon' => '🌸'],
                            ['id' => 'cyber', 'name' => 'Cyber', 'icon' => '🤖'],
                            ['id' => 'arctic', 'name' => 'Arctic', 'icon' => '❄️']
                        ];
                        
                        foreach ($themes as $theme):
                        ?>
                            <div class="theme-item <?php echo $user['overlay_theme'] === $theme['id'] ? 'active' : ''; ?>" 
                                 data-theme="<?php echo $theme['id']; ?>">
                                <span class="theme-icon"><?php echo $theme['icon']; ?></span>
                                <span class="theme-name"><?php echo $theme['name']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Components -->
                <?php include __DIR__ . '/../components/RewardSettings/RewardSettings.php'; ?>
                <?php include __DIR__ . '/../components/RandomReward/RandomReward.php'; ?>
                <?php include __DIR__ . '/../components/CodeSettings/CodeSettings.php'; ?>
                <?php include __DIR__ . '/../components/SoundSettings/SoundSettings.php'; ?>
                <?php include __DIR__ . '/../components/BudgetCalculator/BudgetCalculator.php'; ?>
            </div>
        </div>
    </div>
    
    <!-- Topup Modal -->
    <div id="topup-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>💳 Bakiye Yükleme</h2>
            <form id="topup-form">
                <div class="form-group">
                    <label>Miktar (TL)</label>
                    <input type="number" id="topup-amount" min="1" max="10000" required>
                </div>
                <div class="form-group">
                    <label>Ödeme Dekontu URL</label>
                    <input type="url" id="payment-proof" placeholder="https://..." required>
                </div>
                <div class="form-group">
                    <label>Not (İsteğe Bağlı)</label>
                    <textarea id="topup-note" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Talep Gönder</button>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Rumb. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    
    <script src="<?php echo asset('js/main.min.js'); ?>"></script>
</body>
</html>

