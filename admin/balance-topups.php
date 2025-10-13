<?php
/**
 * Admin Balance Topups Page
 */

require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = new Database(true);

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];
    
    $topup = $db->selectOne('balance_topups', '*', ['id' => $requestId]);
    
    if ($topup['success'] && $action === 'approve') {
        // Add to streamer balance
        $streamer = $db->getUserById($topup['data']['streamer_id']);
        if ($streamer['success']) {
            $newBalance = floatval($streamer['data']['streamer_balance']) + floatval($topup['data']['amount']);
            $db->update('users', ['streamer_balance' => $newBalance], ['id' => $topup['data']['streamer_id']]);
        }
        
        $db->update('balance_topups', [
            'status' => 'approved',
            'processed_at' => date('c')
        ], ['id' => $requestId]);
    } elseif ($action === 'reject') {
        $db->update('balance_topups', [
            'status' => 'rejected',
            'processed_at' => date('c')
        ], ['id' => $requestId]);
    }
    
    redirect('/admin/balance-topups.php');
}

$topupsResult = $db->select('balance_topups', '*', [], 'requested_at.desc', 100);
$topups = $topupsResult['success'] ? $topupsResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakiye Yüklemeleri - Admin</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.min.css'); ?>">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container" style="padding: 2rem 0;">
        <h1>📥 Bakiye Yükleme Talepleri</h1>
        
        <div class="card" style="margin-top: 2rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--primary);">
                        <th style="padding: 12px; text-align: left;">Yayıncı</th>
                        <th style="padding: 12px; text-align: right;">Miktar</th>
                        <th style="padding: 12px; text-align: left;">Dekont</th>
                        <th style="padding: 12px; text-align: center;">Durum</th>
                        <th style="padding: 12px; text-align: left;">Tarih</th>
                        <th style="padding: 12px; text-align: center;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topups as $topup): ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <td style="padding: 12px;"><?php echo substr($topup['streamer_id'], 0, 8); ?>...</td>
                            <td style="padding: 12px; text-align: right; font-weight: 700;">
                                <?php echo formatCurrency($topup['amount']); ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if ($topup['payment_proof']): ?>
                                    <a href="<?php echo $topup['payment_proof']; ?>" target="_blank" class="btn btn-secondary btn-sm">
                                        📄 Görüntüle
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <?php
                                $statusColors = [
                                    'pending' => 'var(--warning)',
                                    'approved' => 'var(--success)',
                                    'rejected' => 'var(--danger)'
                                ];
                                $statusLabels = [
                                    'pending' => 'Bekliyor',
                                    'approved' => 'Onaylandı',
                                    'rejected' => 'Reddedildi'
                                ];
                                ?>
                                <span style="color: <?php echo $statusColors[$topup['status']]; ?>; font-weight: 700;">
                                    <?php echo $statusLabels[$topup['status']]; ?>
                                </span>
                            </td>
                            <td style="padding: 12px;"><?php echo formatDate($topup['requested_at']); ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <?php if ($topup['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $topup['id']; ?>">
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

