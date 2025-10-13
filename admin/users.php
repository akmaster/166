<?php
/**
 * Admin Users Page
 */

require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = new Database(true);
$usersResult = $db->select('users', '*', [], 'created_at.desc', 100);
$users = $usersResult['success'] ? $usersResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar - Admin</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container" style="padding: 2rem 0;">
        <h1>👥 Kullanıcılar</h1>
        
        <div class="card" style="margin-top: 2rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--primary);">
                        <th style="padding: 12px; text-align: left;">Kullanıcı</th>
                        <th style="padding: 12px; text-align: left;">Email</th>
                        <th style="padding: 12px; text-align: right;">Yayıncı Bakiyesi</th>
                        <th style="padding: 12px; text-align: left;">Kayıt Tarihi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <td style="padding: 12px;">
                                <img src="<?php echo $user['twitch_avatar_url']; ?>" alt="" style="width: 30px; height: 30px; border-radius: 50%; vertical-align: middle; margin-right: 8px;">
                                <?php echo $user['twitch_username']; ?>
                            </td>
                            <td style="padding: 12px;"><?php echo $user['twitch_email'] ?? '-'; ?></td>
                            <td style="padding: 12px; text-align: right; color: var(--success); font-weight: 700;">
                                <?php echo formatCurrency($user['streamer_balance']); ?>
                            </td>
                            <td style="padding: 12px;"><?php echo formatDate($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

