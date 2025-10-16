<?php
/**
 * Budget Calculator Component
 * 
 * Helps streamers calculate optimal settings based on budget
 */
?>

<link rel="stylesheet" href="<?php echo baseUrl('components/BudgetCalculator/BudgetCalculator.min.css'); ?>">

<div class="budget-calculator-component card">
    <h3>📊 Bütçe Hesaplama Aracı</h3>
    
    <div class="calculator-inputs">
        <div class="input-group">
            <label for="total_budget">Toplam Bütçe (₺)</label>
            <input type="number" id="total_budget" min="1" max="100000" step="0.01" placeholder="Örn: 100">
        </div>
        
        <div class="input-group">
            <label for="stream_hours">Yayın Süresi (saat)</label>
            <input type="number" id="stream_hours" min="0.5" max="24" step="0.5" placeholder="Örn: 3">
        </div>
        
        <div class="input-group">
            <label for="estimated_viewers">Tahmini İzleyici Sayısı</label>
            <input type="number" id="estimated_viewers" min="1" max="100000" placeholder="Örn: 50">
        </div>
        
        <div class="input-group">
            <label for="participation_rate">Katılım Oranı (%)</label>
            <input type="number" id="participation_rate" min="1" max="100" value="30" placeholder="30">
        </div>
    </div>
    
    <button id="calculate-budget" class="btn btn-secondary btn-block">
        🧮 Hesapla
    </button>
    
    <div id="calculation-results" style="display: none;">
        <div class="results-header">
            <h4>📈 Önerilen Ayarlar</h4>
        </div>
        
        <div class="results-grid">
            <div class="result-item">
                <span class="result-label">Ödül Miktarı:</span>
                <span class="result-value" id="result-reward">-</span>
            </div>
            
            <div class="result-item">
                <span class="result-label">Kod Aralığı:</span>
                <span class="result-value" id="result-interval">-</span>
            </div>
            
            <div class="result-item">
                <span class="result-label">Toplam Kod Sayısı:</span>
                <span class="result-value" id="result-codes">-</span>
            </div>
            
            <div class="result-item">
                <span class="result-label">Beklenen Katılımcı:</span>
                <span class="result-value" id="result-participants">-</span>
            </div>
            
            <div class="result-item">
                <span class="result-label">Toplam Maliyet:</span>
                <span class="result-value" id="result-cost">-</span>
            </div>
            
            <div class="result-item">
                <span class="result-label">Verimlilik:</span>
                <span class="result-value" id="result-efficiency">-</span>
            </div>
        </div>
        
        <button id="apply-budget-settings" class="btn btn-primary btn-block">
            ✅ Bu Ayarları Uygula
        </button>
    </div>
</div>

<script src="<?php echo baseUrl('components/BudgetCalculator/BudgetCalculator.min.js'); ?>"></script>

