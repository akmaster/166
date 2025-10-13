<?php
/**
 * Admin - Codes Management
 * 
 * List and monitor all generated codes
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/helpers.php';

// Require admin authentication
requireAdmin();

$db = new Database(true); // Use service key for admin operations

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Filters
$filterStatus = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$filterStreamer = isset($_GET['streamer_id']) ? sanitize($_GET['streamer_id']) : '';
$searchCode = isset($_GET['code']) ? sanitize($_GET['code']) : '';

// Build query conditions
$conditions = [];
if ($filterStatus === 'active') {
    $conditions[] = "is_active=eq.true";
} elseif ($filterStatus === 'expired') {
    $conditions[] = "is_active=eq.false";
}

if ($filterStreamer) {
    $conditions[] = "streamer_id=eq.$filterStreamer";
}

if ($searchCode && strlen($searchCode) >= 3) {
    $conditions[] = "code=like.*$searchCode*";
}

$conditionString = $conditions ? '&' . implode('&', $conditions) : '';

// Get codes with streamer info
$codesResult = $db->query(
    "codes?select=*,users!codes_streamer_id_fkey(twitch_username,twitch_display_name)" . 
    $conditionString . 
    "&order=created_at.desc&limit=$limit&offset=$offset"
);

$codes = $codesResult['success'] ? $codesResult['data'] : [];

// Get total count for pagination
$countResult = $db->query(
    "codes?select=count" . $conditionString
);
$totalCodes = $countResult['success'] && isset($countResult['data'][0]['count']) 
    ? (int)$countResult['data'][0]['count'] 
    : 0;
$totalPages = ceil($totalCodes / $limit);

// Get all streamers for filter dropdown (admin can send codes regardless of balance)
$streamersResult = $db->query('users?select=id,twitch_display_name,twitch_username,streamer_balance&order=twitch_display_name.asc');

// Debug output (temporary)
if (DEBUG_MODE) {
    echo "<!-- DEBUG STREAMERS RESULT: " . json_encode($streamersResult) . " -->\n";
}

$streamers = [];
if ($streamersResult['success'] && isset($streamersResult['data'])) {
    $streamers = $streamersResult['data'];
} else {
    // Try alternative query without order
    $streamersResult2 = $db->query('users?select=id,twitch_display_name,twitch_username,streamer_balance');
    if ($streamersResult2['success'] && isset($streamersResult2['data'])) {
        $streamers = $streamersResult2['data'];
    }
}

// Debug: Check if we have streamers
if (DEBUG_MODE) {
    error_log('Streamers count: ' . count($streamers));
    error_log('Streamers data: ' . json_encode($streamers));
}

// Calculate stats
$statsResult = $db->query("codes?select=count,is_active");
$totalCodesCount = 0;
$activeCodes = 0;
if ($statsResult['success']) {
    $totalCodesCount = count($statsResult['data']);
    foreach ($statsResult['data'] as $stat) {
        if ($stat['is_active']) $activeCodes++;
    }
}

$pageTitle = 'Code Management';
include 'includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1>💎 Code Management</h1>
        <p>Monitor and manage all generated codes</p>
    </div>
    <div>
        <button onclick="openGenerateModal()" class="btn btn-primary">
            ⚡ Manuel Kod Gönder
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalCodesCount); ?></div>
            <div class="stat-label">Total Codes</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($activeCodes); ?></div>
            <div class="stat-label">Active Codes</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalCodesCount - $activeCodes); ?></div>
            <div class="stat-label">Expired Codes</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalPages); ?></div>
            <div class="stat-label">Pages</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <h3>🔍 Filters</h3>
    <form method="GET" action="codes.php" class="filter-form">
        <div class="filter-grid">
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="all" <?php echo $filterStatus === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo $filterStatus === 'expired' ? 'selected' : ''; ?>>Expired</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Streamer</label>
                <select name="streamer_id">
                    <option value="">All Streamers</option>
                    <?php foreach ($streamers as $streamer): ?>
                        <option value="<?php echo htmlspecialchars($streamer['id']); ?>" 
                                <?php echo $filterStreamer === $streamer['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($streamer['twitch_display_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Search Code</label>
                <input type="text" 
                       name="code" 
                       placeholder="Enter code..." 
                       value="<?php echo htmlspecialchars($searchCode); ?>"
                       maxlength="6">
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="codes.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Codes Table -->
<div class="card">
    <div class="card-header">
        <h3>Code List</h3>
        <span class="badge"><?php echo number_format($totalCodes); ?> codes</span>
    </div>
    
    <?php if (empty($codes)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>No Codes Found</h3>
            <p>No codes match your filters. Try adjusting the filters or wait for codes to be generated.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Streamer</th>
                        <th>Created</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th>Submissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($codes as $code): ?>
                        <?php
                        $isActive = $code['is_active'];
                        $isExpired = strtotime($code['expires_at']) < time();
                        $streamerName = $code['users']['twitch_display_name'] ?? 'Unknown';
                        $submissionCount = 0;
                        
                        // Get submission count for this code
                        $subResult = $db->query("submissions?select=count&code_id=eq." . $code['id']);
                        if ($subResult['success'] && isset($subResult['data'][0]['count'])) {
                            $submissionCount = (int)$subResult['data'][0]['count'];
                        }
                        ?>
                        <tr class="<?php echo $isActive && !$isExpired ? 'row-active' : 'row-expired'; ?>">
                            <td>
                                <span class="code-display"><?php echo htmlspecialchars($code['code']); ?></span>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <strong><?php echo htmlspecialchars($streamerName); ?></strong>
                                    <small>@<?php echo htmlspecialchars($code['users']['twitch_username'] ?? 'unknown'); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="time-cell">
                                    <?php echo date('Y-m-d H:i:s', strtotime($code['created_at'])); ?>
                                    <small><?php echo timeAgo($code['created_at']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="time-cell">
                                    <?php echo date('Y-m-d H:i:s', strtotime($code['expires_at'])); ?>
                                    <small><?php echo timeAgo($code['expires_at']); ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($isActive && !$isExpired): ?>
                                    <span class="badge badge-success">✅ Active</span>
                                <?php elseif ($isExpired): ?>
                                    <span class="badge badge-danger">⏰ Expired</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">❌ Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="submission-count">
                                    <?php echo $submissionCount; ?> submission<?php echo $submissionCount !== 1 ? 's' : ''; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-info" 
                                            onclick="viewCodeDetails('<?php echo htmlspecialchars($code['id']); ?>')">
                                        👁️ View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $filterStatus; ?>&streamer_id=<?php echo $filterStreamer; ?>&code=<?php echo $searchCode; ?>" 
                       class="btn btn-secondary">← Previous</a>
                <?php endif; ?>
                
                <span class="page-info">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $filterStatus; ?>&streamer_id=<?php echo $filterStreamer; ?>&code=<?php echo $searchCode; ?>" 
                       class="btn btn-secondary">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Generate Code Modal -->
<div id="generateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeGenerateModal()">&times;</span>
        <h2>⚡ Manuel Kod Gönder</h2>
        
        <form id="generate-code-form">
            <div class="form-group">
                <label>Yayıncı Seçin</label>
                <select name="streamer_id" id="streamer_select" required>
                    <option value="">-- Yayıncı Seçin --</option>
                    <option value="all">🌟 TÜM YAYINCILARA GÖNDER</option>
                    <?php if (empty($streamers)): ?>
                        <option disabled>⚠️ Sistemde kayıtlı yayıncı bulunamadı</option>
                    <?php else: ?>
                        <?php foreach ($streamers as $streamer): ?>
                            <option value="<?php echo htmlspecialchars($streamer['id']); ?>">
                                <?php echo htmlspecialchars($streamer['twitch_display_name'] ?? $streamer['twitch_username'] ?? 'Unknown'); ?>
                                (Bakiye: <?php echo formatCurrency($streamer['streamer_balance'] ?? 0); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small>
                    <?php if (empty($streamers)): ?>
                        <span style="color: var(--danger);">⚠️ Henüz sisteme kayıtlı yayıncı yok. Yayıncıların Twitch ile giriş yapması gerekiyor.</span>
                    <?php else: ?>
                        Tüm yayıncılara aynı anda kod göndermek için "TÜM YAYINCILARA GÖNDER" seçin (<?php echo count($streamers); ?> yayıncı mevcut)
                    <?php endif; ?>
                </small>
            </div>
            
            <div style="padding: 12px; background: rgba(0, 212, 170, 0.1); border-left: 4px solid var(--success); border-radius: 4px; margin-bottom: 20px;">
                <strong style="color: var(--success);">🎁 Bonus Kod Sistemi:</strong>
                <p style="margin: 8px 0 0 0; color: var(--text-secondary); font-size: 0.9rem;">
                    Admin panelinden gönderilen kodlar <strong>BONUS KOD</strong> olarak işaretlenir. 
                    İzleyiciler bu kodları kullandığında <strong>yayıncının bakiyesinden düşüş olmaz</strong>. 
                    Ara sırada promosyon/bonus olarak göndermek için idealdir.
                </p>
            </div>
            
            <div style="padding: 12px; background: rgba(255, 193, 7, 0.1); border-left: 4px solid var(--warning); border-radius: 4px; margin-bottom: 20px;">
                <strong style="color: var(--warning);">⚠️ Aktif Kod Kontrolü:</strong>
                <p style="margin: 8px 0 0 0; color: var(--text-secondary); font-size: 0.9rem;">
                    Yayıncının <strong>aktif bir kodu varsa</strong> (countdown + kod gösterimi devam ediyorsa) 
                    yeni kod gönderilemez. Lütfen mevcut kod bitene kadar bekleyin.
                </p>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="custom_code_check" onchange="toggleCustomCode()">
                    Özel kod gir (varsayılan: rastgele 6 haneli kod)
                </label>
            </div>
            
            <div class="form-group" id="custom_code_group" style="display: none;">
                <label>Özel Kod (6 haneli)</label>
                <input type="text" 
                       name="custom_code" 
                       id="custom_code" 
                       placeholder="123456"
                       maxlength="6"
                       pattern="[0-9]{6}">
                <small>6 haneli sayısal kod giriniz</small>
            </div>
            
            <div class="form-group">
                <label>Countdown Süresi (saniye)</label>
                <input type="number" 
                       name="countdown_duration" 
                       value="5" 
                       min="0" 
                       max="30">
            </div>
            
            <div class="form-group">
                <label>Kod Süresi (saniye)</label>
                <input type="number" 
                       name="code_duration" 
                       value="30" 
                       min="10" 
                       max="300">
            </div>
            
            <div id="generate-result" style="margin-top: 15px;"></div>
            
            <div class="button-group" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" id="generate-btn">
                    🚀 Kod Gönder
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeGenerateModal()">
                    İptal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Code Details Modal -->
<div id="codeModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Code Details</h2>
        <div id="codeDetails">
            <div class="loading">Loading...</div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 2.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.filter-form {
    margin-top: 20px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.button-group {
    display: flex;
    gap: 10px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success {
    background: rgba(0, 212, 170, 0.2);
    color: var(--success);
}

.badge-danger {
    background: rgba(255, 71, 87, 0.2);
    color: var(--danger);
}

.badge-secondary {
    background: rgba(173, 173, 184, 0.2);
    color: var(--text-secondary);
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: rgba(145, 71, 255, 0.1);
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.data-table th {
    font-weight: 600;
    color: var(--primary);
}

.row-active {
    background: rgba(0, 212, 170, 0.05);
}

.row-expired {
    opacity: 0.6;
}

.code-display {
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary);
    letter-spacing: 0.1rem;
}

.user-cell strong {
    display: block;
    color: var(--text-primary);
}

.user-cell small {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

.time-cell small {
    display: block;
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.submission-count {
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-top: 20px;
    padding: 20px;
}

.page-info {
    color: var(--text-secondary);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--text-secondary);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 28px;
    font-weight: bold;
    color: var(--text-secondary);
    cursor: pointer;
}

.close:hover {
    color: var(--text-primary);
}

.loading {
    text-align: center;
    padding: 20px;
    color: var(--text-secondary);
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section h3 {
    margin-bottom: 15px;
    color: var(--primary);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.detail-item {
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
}

.detail-item label {
    display: block;
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.detail-item span {
    color: var(--text-primary);
    font-weight: 600;
}

.detail-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.detail-table th,
.detail-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-table th {
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.9rem;
}

.error {
    color: var(--danger);
    text-align: center;
    padding: 20px;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
// Generate Code Modal
function openGenerateModal() {
    document.getElementById('generateModal').classList.add('active');
}

function closeGenerateModal() {
    document.getElementById('generateModal').classList.remove('active');
    document.getElementById('generate-code-form').reset();
    document.getElementById('custom_code_group').style.display = 'none';
    document.getElementById('generate-result').innerHTML = '';
}

function toggleCustomCode() {
    const checked = document.getElementById('custom_code_check').checked;
    const group = document.getElementById('custom_code_group');
    const input = document.getElementById('custom_code');
    
    if (checked) {
        group.style.display = 'block';
        input.required = true;
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

// Generate Code Form Submit
document.getElementById('generate-code-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultDiv = document.getElementById('generate-result');
    const submitBtn = document.getElementById('generate-btn');
    
    // Validate custom code if provided
    const customCode = formData.get('custom_code');
    if (customCode && !/^\d{6}$/.test(customCode)) {
        resultDiv.innerHTML = '<p class="error">Özel kod 6 haneli sayısal olmalıdır!</p>';
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Gönderiliyor...';
    resultDiv.innerHTML = '<p style="color: var(--text-secondary);">Kod oluşturuluyor...</p>';
    
    try {
        const response = await fetch('/api/admin/generate-code.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        const data = JSON.parse(responseText);
        
        if (data.success) {
            resultDiv.innerHTML = `<p class="success">✅ ${data.message}</p>`;
            
            if (data.data && data.data.codes_generated) {
                resultDiv.innerHTML += `<p style="color: var(--text-secondary); margin-top: 10px;">
                    ${data.data.codes_generated} kod oluşturuldu ve overlay'lere gönderildi.
                </p>`;
            }
            
            // Reload page after 2 seconds
            setTimeout(() => {
                closeGenerateModal();
                location.reload();
            }, 2000);
        } else {
            resultDiv.innerHTML = `<p class="error">❌ Hata: ${data.message}</p>`;
            submitBtn.disabled = false;
            submitBtn.textContent = '🚀 Kod Gönder';
        }
    } catch (error) {
        resultDiv.innerHTML = '<p class="error">❌ Bağlantı hatası!</p>';
        submitBtn.disabled = false;
        submitBtn.textContent = '🚀 Kod Gönder';
    }
});

function viewCodeDetails(codeId) {
    const modal = document.getElementById('codeModal');
    const detailsDiv = document.getElementById('codeDetails');
    
    modal.classList.add('active');
    detailsDiv.innerHTML = '<div class="loading">Loading...</div>';
    
    // Fetch code details
    fetch(`../api/admin/get-code-details.php?code_id=${codeId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const code = data.data.code;
                const submissions = data.data.submissions || [];
                
                detailsDiv.innerHTML = `
                    <div class="detail-section">
                        <h3>Code Information</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Code:</label>
                                <span class="code-display">${code.code}</span>
                            </div>
                            <div class="detail-item">
                                <label>Streamer:</label>
                                <span>${code.streamer_name}</span>
                            </div>
                            <div class="detail-item">
                                <label>Created:</label>
                                <span>${code.created_at}</span>
                            </div>
                            <div class="detail-item">
                                <label>Expires:</label>
                                <span>${code.expires_at}</span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span>${code.is_active ? '✅ Active' : '❌ Inactive'}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Submissions (${submissions.length})</h3>
                        ${submissions.length > 0 ? `
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Reward</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${submissions.map(sub => `
                                        <tr>
                                            <td>${sub.user_display_name}</td>
                                            <td>${sub.formatted_reward}</td>
                                            <td>${sub.time_ago}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : '<p style="color: var(--text-secondary);">No submissions yet</p>'}
                    </div>
                `;
            } else {
                detailsDiv.innerHTML = `<p class="error">Error: ${data.message}</p>`;
            }
        })
        .catch(err => {
            detailsDiv.innerHTML = '<p class="error">Failed to load details</p>';
        });
}

function closeModal() {
    document.getElementById('codeModal').classList.remove('active');
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('codeModal');
    if (event.target === modal) {
        closeModal();
    }
};
</script>

<?php include 'includes/footer.php'; ?>
