<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../config/config.php';

// If already logged in as admin
if (isAdmin()) {
    redirect(APP_URL . '/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_username'] = $username;
        redirect(APP_URL . '/admin/');
    } else {
        $error = 'Kullanıcı adı veya şifre hatalı';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş - Rumb</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <h2>🔐 Admin Girişi</h2>
            
            <?php if ($error): ?>
                <div class="result-message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="<?php echo APP_URL; ?>">← Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
</body>
</html>

