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
<html lang="<?php echo $GLOBALS['CURRENT_LANG']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('site.name'); ?> - <?php echo __('site.description'); ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/landing.min.css'); ?>">
</head>
<body>
    
    <!-- LANDING PAGE -->
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <h1>🎮 <?php echo __('site.name'); ?></h1>
                </div>
                <div class="nav-links">
                    <?php echo getLanguageSwitcher(); ?>
                    <a href="/streamers.php"><?php echo __('nav.streamers'); ?></a>
                    <a href="/api/auth.php" class="btn btn-primary"><?php echo __('nav.login'); ?></a>
                </div>
            </div>
        </nav>
        
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1 class="hero-title"><?php echo __('landing.hero_title'); ?></h1>
                <p class="hero-subtitle"><?php echo __('landing.hero_subtitle'); ?></p>
                <a href="/api/auth.php" class="btn btn-hero"><?php echo __('landing.cta_button'); ?></a>
                
                <div class="stats-preview" id="public-stats">
                    <div class="stat-item">
                        <div class="stat-value">0</div>
                        <div class="stat-label"><?php echo __('landing.stats_users'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">0 TL</div>
                        <div class="stat-label"><?php echo __('landing.stats_rewards'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">0</div>
                        <div class="stat-label"><?php echo __('landing.stats_codes'); ?></div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- How it Works -->
        <section class="how-it-works">
            <div class="container">
                <h2><?php echo __('landing.how_it_works'); ?></h2>
                
                <div class="tabs">
                    <button class="tab-btn active" data-tab="viewer"><?php echo __('landing.tab_viewer'); ?></button>
                    <button class="tab-btn" data-tab="streamer"><?php echo __('landing.tab_streamer'); ?></button>
                </div>
                
                <div class="tab-content active" id="viewer-tab">
                    <div class="steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h3><?php echo __('landing.viewer_step1_title'); ?></h3>
                            <p><?php echo __('landing.viewer_step1_desc'); ?></p>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <h3><?php echo __('landing.viewer_step2_title'); ?></h3>
                            <p><?php echo __('landing.viewer_step2_desc'); ?></p>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <h3><?php echo __('landing.viewer_step3_title'); ?></h3>
                            <p><?php echo __('landing.viewer_step3_desc'); ?></p>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <h3><?php echo __('landing.viewer_step4_title'); ?></h3>
                            <p><?php echo __('landing.viewer_step4_desc'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="streamer-tab">
                    <div class="steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h3><?php echo __('landing.streamer_step1_title'); ?></h3>
                            <p><?php echo __('landing.streamer_step1_desc'); ?></p>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <h3><?php echo __('landing.streamer_step2_title'); ?></h3>
                            <p><?php echo __('landing.streamer_step2_desc'); ?></p>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <h3><?php echo __('landing.streamer_step3_title'); ?></h3>
                            <p><?php echo __('landing.streamer_step3_desc'); ?></p>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <h3><?php echo __('landing.streamer_step4_title'); ?></h3>
                            <p><?php echo __('landing.streamer_step4_desc'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Features -->
        <section class="features">
            <div class="container"></div>
                <h2><?php echo __('landing.features_title'); ?></h2>
                <div class="feature-grid">
                    <div class="feature">
                        <div class="feature-icon">⚡</div>
                        <h3><?php echo __('landing.feature1_title'); ?></h3>
                        <p><?php echo __('landing.feature1_desc'); ?></p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🎨</div>
                        <h3><?php echo __('landing.feature2_title'); ?></h3>
                        <p><?php echo __('landing.feature2_desc'); ?></p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">📱</div>
                        <h3><?php echo __('landing.feature3_title'); ?></h3>
                        <p><?php echo __('landing.feature3_desc'); ?></p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">🔒</div>
                        <h3><?php echo __('landing.feature4_title'); ?></h3>
                        <p><?php echo __('landing.feature4_desc'); ?></p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- CTA -->
        <section class="cta">
            <div class="container">
                <h2><?php echo __('landing.cta_final_title'); ?></h2>
                <p><?php echo __('landing.cta_final_subtitle'); ?></p>
                <a href="/api/auth.php" class="btn btn-hero">🎮 <?php echo __('nav.login'); ?></a>
            </div>
        </section>
    
    <footer class="footer">
        <div class="container">
            <p><?php echo __('site.footer'); ?></p>
        </div>
    </footer>
    
    <script src="<?php echo asset('js/main.min.js'); ?>"></script>
</body>
</html>

