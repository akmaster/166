<?php
/**
 * Main Index Page
 * 
 * - Landing page for non-logged users
 * - Redirects logged users to dashboard
 */

require_once __DIR__ . '/config/config.php';

// Redirect logged users to dashboard
if (isLoggedIn()) {
    header('Location: /dashboard/');
    exit;
}
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
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Rumb. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    
    <script src="<?php echo asset('js/main.min.js'); ?>"></script>
</body>
</html>

