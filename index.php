<?php
/**
 * Main Index Page
 * 
 * - Landing page for non-logged users
 * - Dashboard for logged users (viewer/streamer tabs)
 */

require_once __DIR__ . '/config/config.php';

$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getCurrentUser() : null;
$userBalance = $isLoggedIn ? (new Database())->getUserBalance($user['id']) : 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rumb - Twitch Code Reward System</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/landing.min.css'); ?>">
</head>
<body>
    
    <?php if (!$isLoggedIn): ?>
        <!-- LANDING PAGE -->
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <h1>🎮 Rumb</h1>
                </div>
                <div class="nav-links">
                    <a href="/streamers.php">Canlı Yayıncılar</a>
                    <a href="/api/auth.php" class="btn btn-primary">Twitch ile Giriş Yap</a>
                </div>
            </div>
        </nav>
        
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1 class="hero-title">Twitch İzlerken Para Kazan!</h1>
                <p class="hero-subtitle">Yayıncılar ekranda gösterdiği kodları gir, anında ödül kazan. Ücretsiz ve kolay!</p>
                <a href="/api/auth.php" class="btn btn-hero">🚀 Hemen Başla</a>
                
                <div class="stats-preview" id="public-stats">
                    <div class="stat-item">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Toplam Kullanıcı</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">0 TL</div>
                        <div class="stat-label">Dağıtılan Ödül</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Kod Kullanımı</div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- How it Works -->
        <section class="how-it-works">
            <div class="container">
                <h2>Nasıl Çalışır?</h2>
                
                <div class="tabs">
                    <button class="tab-btn active" data-tab="viewer">👁️ İzleyici İçin</button>
                    <button class="tab-btn" data-tab="streamer">🎙️ Yayıncı İçin</button>
                </div>
                
                <div class="tab-content active" id="viewer-tab">
                    <div class="steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h3>Twitch ile Giriş Yap</h3>
                            <p>Hesap oluştur, hiçbir ücret yok</p>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <h3>Yayın İzle</h3>
                            <p>Sistemdeki yayıncıları izle</p>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <h3>Kodu Gir</h3>
                            <p>Ekranda gözüken 6 haneli kodu gir</p>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <h3>Para Kazan</h3>
                            <p>Anında bakiyene eklenir, çek!</p>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="streamer-tab">
                    <div class="steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h3>Hesap Oluştur</h3>
                            <p>Twitch ile giriş yap</p>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <h3>Bakiye Yükle</h3>
                            <p>Dağıtacağın parayı yükle</p>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <h3>Ayarları Yap</h3>
                            <p>Kod sıklığı, ödül miktarı, tema</p>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <h3>OBS'e Ekle</h3>
                            <p>Overlay linkini OBS'e ekle, yayına başla</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Features -->
        <section class="features">
            <div class="container">
                <h2>Özellikler</h2>
                <div class="feature-grid">
                    <div class="feature">
                        <div class="feature-icon">⚡</div>
                        <h3>Anında Ödeme</h3>
                        <p>Kodları girdiğin anda bakiyene eklenir</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🎨</div>
                        <h3>20+ Tema</h3>
                        <p>Yayınınıza uygun temalar</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">📱</div>
                        <h3>Mobil Uyumlu</h3>
                        <p>Her cihazdan kullan</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🔒</div>
                        <h3>Güvenli</h3>
                        <p>Twitch OAuth ile güvenli giriş</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- CTA -->
        <section class="cta">
            <div class="container">
                <h2>Hemen Başla!</h2>
                <p>Twitch izlerken para kazanmaya başla</p>
                <a href="/api/auth.php" class="btn btn-hero">🎮 Twitch ile Giriş Yap</a>
            </div>
        </section>
        
    <?php else: ?>
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
                    <?php include __DIR__ . '/components/RewardSettings/RewardSettings.php'; ?>
                    <?php include __DIR__ . '/components/RandomReward/RandomReward.php'; ?>
                    <?php include __DIR__ . '/components/CodeSettings/CodeSettings.php'; ?>
                    <?php include __DIR__ . '/components/SoundSettings/SoundSettings.php'; ?>
                    <?php include __DIR__ . '/components/BudgetCalculator/BudgetCalculator.php'; ?>
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
        
    <?php endif; ?>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Rumb. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    
    <script src="<?php echo asset('js/main.min.js'); ?>"></script>
</body>
</html>

