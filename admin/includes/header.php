<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Rumb Admin Panel</title>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="/admin/assets/admin.css">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎮</text></svg>">
</head>
<body class="admin-body">

<nav class="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="/admin/">
                <h1>🎮 Rumb Admin</h1>
            </a>
        </div>
        <div class="nav-links">
            <a href="/admin/" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                📊 Dashboard
            </a>
            <a href="/admin/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                👥 Kullanıcılar
            </a>
            <a href="/admin/codes.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'codes.php' ? 'active' : ''; ?>">
                💎 Kodlar
            </a>
            <a href="/admin/payouts.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'payouts.php' ? 'active' : ''; ?>">
                💸 Ödemeler
            </a>
            <a href="/admin/balance-topups.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'balance-topups.php' ? 'active' : ''; ?>">
                💰 Bakiye Yüklemeleri
            </a>
            <a href="/admin/settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                ⚙️ Ayarlar
            </a>
            <a href="/admin/logout.php" class="btn btn-outline btn-sm">
                🚪 Çıkış
            </a>
        </div>
    </div>
</nav>

<div class="admin-container">
    <div class="container">
