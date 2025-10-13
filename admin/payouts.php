<?php
/**
 * Admin Payouts Page
 */

require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = new Database(true);

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $db->update('payout_requests', [
            'status' => 'completed',
            'processed_at' => date('c')
        ], ['id' => $requestId]);
    } elseif ($action === 'reject') {
        $db->update('payout_requests', [
            'status' => 'rejected',
            'processed_at' => date('c')
        ], ['id' => $requestId]);
    }
    
    redirect('/admin/payouts.php');
}

$payoutsResult = $db->select('payout_requests', '*', [], 'requested_at.desc', 100);
$payouts = $payoutsResult['success'] ? $payoutsResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Talepleri - Admin</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container" style="padding: 2rem 0;">
        <h1>💸 Ödeme Talepleri</h1>
        
        <div class="card" style="margin-top: 2rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--primary);">
                        <th style="padding: 12px; text-align: left;">Kullanıcı ID</th>
                        <th style="padding: 12px; text-align: right;">Miktar</th>
                        <th style="padding: 12px; text-align: center;">Durum</th>
                        <th style="padding: 12px; text-align: left;">Tarih</th>
                        <th style="padding: 12px; text-align: center;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payouts as $payout): ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <td style="padding: 12px;"><?php echo substr($payout['user_id'], 0, 8); ?>...</td>
                            <td style="padding: 12px; text-align: right; font-weight: 700;">
                                <?php echo formatCurrency($payout['amount']); ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <?php
                                $statusColors = [
                                    'pending' => 'var(--warning)',
                                    'completed' => 'var(--success)',
                                    'rejected' => 'var(--danger)'
                                ];
                                $statusLabels = [
                                    'pending' => 'Bekliyor',
                                    'completed' => 'Tamamlandı',
                                    'rejected' => 'Reddedildi'
                                ];
                                ?>
                                <span style="color: <?php echo $statusColors[$payout['status']]; ?>; font-weight: 700;">
                                    <?php echo $statusLabels[$payout['status']]; ?>
                                </span>
                            </td>
                            <td style="padding: 12px;"><?php echo formatDate($payout['requested_at']); ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <?php if ($payout['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $payout['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                            ✓ Onayla
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn btn-outline btn-sm">
                                            ✗ Reddet
                                        </button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

