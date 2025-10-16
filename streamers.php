<?php
/**
 * Live Streamers Page
 * 
 * Shows all streamers in the system, with live ones at the top
 */

require_once __DIR__ . '/config/config.php';

$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="<?php echo $GLOBALS['CURRENT_LANG']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('streamers.title'); ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>
    
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="/">🎮 <?php echo __('site.name'); ?></a>
            </div>
            <div class="nav-links">
                <?php echo getLanguageSwitcher(); ?>
                <?php if ($isLoggedIn): ?>
                    <img src="<?php echo $user['twitch_avatar_url']; ?>" alt="Avatar" class="user-avatar">
                    <span><?php echo $user['twitch_username']; ?></span>
                    <a href="/dashboard/" class="btn btn-secondary btn-sm"><?php echo __('nav.dashboard'); ?></a>
                    <a href="/api/logout.php" class="btn btn-outline btn-sm"><?php echo __('nav.logout'); ?></a>
                <?php else: ?>
                    <a href="/"><?php echo __('nav.home'); ?></a>
                    <a href="/api/auth.php" class="btn btn-primary"><?php echo __('nav.login'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="streamers-page">
        <div class="container">
            <div class="page-header">
                <h1><?php echo __('streamers.page_title'); ?></h1>
                <p><?php echo __('streamers.page_subtitle'); ?></p>
            </div>
            
            <div id="streamers-loading" class="loading-state">
                <div class="spinner"></div>
                <p><?php echo __('streamers.loading'); ?></p>
            </div>
            
            <div id="streamers-grid" class="streamers-grid" style="display: none;">
                <!-- Will be populated by JavaScript -->
            </div>
            
            <div id="no-streamers" class="empty-state" style="display: none;">
                <div class="empty-icon">😔</div>
                <h3><?php echo __('streamers.no_streamers_title'); ?></h3>
                <p><?php echo __('streamers.no_streamers_subtitle'); ?></p>
                <a href="/" class="btn btn-primary"><?php echo __('streamers.back_home'); ?></a>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p><?php echo __('site.footer'); ?></p>
        </div>
    </footer>
    
    <script>
        // Translation variables
        const LANG = {
            liveBadge: '<?php echo __('streamers.live_badge'); ?>',
            watchButton: '<?php echo __('streamers.watch_button'); ?>'
        };
        
        // Show skeleton cards during loading
        function showSkeletonCards() {
            const grid = document.getElementById('streamers-grid');
            grid.style.display = 'grid';
            grid.innerHTML = '';
            
            for (let i = 0; i < 6; i++) {
                const skeleton = document.createElement('div');
                skeleton.className = 'streamer-card skeleton-card';
                skeleton.innerHTML = `
                    <div class="skeleton-thumbnail"></div>
                    <div class="streamer-info">
                        <div class="skeleton-text skeleton-title"></div>
                        <div class="skeleton-text skeleton-subtitle"></div>
                        <div class="skeleton-button"></div>
                    </div>
                `;
                grid.appendChild(skeleton);
            }
        }
        
        // Fetch and display live streamers
        async function loadStreamers() {
            try {
                // Show skeleton loading immediately
                showSkeletonCards();
                
                const response = await fetch('/api/get-live-streamers.php');
                const data = await response.json();
                
                document.getElementById('streamers-loading').style.display = 'none';
                
                if (data.success && data.data.length > 0) {
                    displayStreamers(data.data);
                } else {
                    document.getElementById('streamers-grid').style.display = 'none';
                    document.getElementById('no-streamers').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('streamers-loading').style.display = 'none';
                document.getElementById('streamers-grid').style.display = 'none';
                document.getElementById('no-streamers').style.display = 'block';
            }
        }
        
        function displayStreamers(streamers) {
            const grid = document.getElementById('streamers-grid');
            grid.style.display = 'grid';
            grid.innerHTML = '';
            
            streamers.forEach(streamer => {
                const card = document.createElement('div');
                card.className = 'streamer-card';
                card.innerHTML = `
                    <div class="stream-thumbnail">
                        <img src="${streamer.thumbnail_url}" alt="${streamer.username}" loading="lazy">
                        <div class="live-badge">${LANG.liveBadge}</div>
                        <div class="viewer-count">👁️ ${formatNumber(streamer.viewer_count)}</div>
                    </div>
                    <div class="streamer-info">
                        <div class="streamer-header">
                            <img src="${streamer.avatar_url}" alt="${streamer.username}" class="streamer-avatar">
                            <div class="streamer-details">
                                <h3>${streamer.display_name}</h3>
                                <p class="game-name">${streamer.game_name || 'Just Chatting'}</p>
                            </div>
                        </div>
                        <p class="stream-title">${streamer.stream_title}</p>
                        <a href="https://twitch.tv/${streamer.username}" target="_blank" class="btn btn-primary btn-block">
                            ${LANG.watchButton}
                        </a>
                    </div>
                `;
                grid.appendChild(card);
            });
        }
        
        function formatNumber(num) {
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'k';
            }
            return num.toString();
        }
        
        // Load on page ready
        document.addEventListener('DOMContentLoaded', loadStreamers);
    </script>
</body>
</html>

