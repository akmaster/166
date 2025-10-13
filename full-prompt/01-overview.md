# TWITCH CODE REWARD SYSTEM - PROJECT OVERVIEW

## 📋 PROJE ÖZETİ

Twitch yayıncıları için **otomatik kod ödül sistemi**. Yayın sırasında ekranda kodlar gösterilir, izleyiciler bu kodları girerek para kazanır. Sistem tamamen otomatik çalışır ve gerçek zamanlı (Supabase Realtime) kod gösterimi yapar.

## 🎯 TEMEL ÖZELLİKLER

### İzleyici:

- ✅ Twitch OAuth ile giriş
- ✅ 6 haneli kod girişi
- ✅ Anında ödül kazanma
- ✅ Bakiye takibi
- ✅ Ödeme talebi (minimum eşik)

### Yayıncı:

- ✅ Otomatik kod üretimi (cron)
- ✅ OBS overlay (token bazlı)
- ✅ Kod ayarları (countdown, duration, interval)
- ✅ Ödül miktarı (sabit/rastgele)
- ✅ **20 overlay teması**
- ✅ **Ses kontrol sistemi (10+10 ses)**
- ✅ Bütçe hesaplayıcı
- ✅ İstatistikler

### Admin:

- ✅ Kullanıcı yönetimi
- ✅ Kod yönetimi (manuel gönderim)
- ✅ Ödeme talepleri
- ✅ Bakiye yükleme talepleri
- ✅ Sistem ayarları

## 🚨 KRİTİK UYARILAR (14 ADET)

### 1. ⚠️ TIMEZONE HATASI (En Kritik!)

**SORUN:** DateTime ile timezone karışıklığı - Supabase UTC bekliyor!

```php
// ❌ YANLIŞ
$now = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
$formatted = $now->format('Y-m-d\TH:i:s.u\Z');
// 15:22:38Z yazıyor ama bu Istanbul saati, UTC değil!

// ✅ DOĞRU
$now = new DateTime('now', new DateTimeZone('UTC'));
$formatted = $now->format('Y-m-d\TH:i:s.u\Z');
// 12:22:38Z yazıyor ve bu gerçek UTC!
```

**Nerede Kullan:**

- Kod oluştururken (`cron.php`, `generate-code.php`)
- Kod kontrolü (`getActiveCode()`, `submit-code.php`)
- Tüm DateTime işlemlerinde

### 2. ⚠️ SUPABASE REALTIME KURULUMU

**SORUN:** Realtime çalışmıyor, overlay'de kod görünmüyor!

**ÇÖZÜM:**

```
1. Supabase Dashboard → Database → Replication
2. Publications → "supabase_realtime" bul
3. "0 tables" yazıyorsa TIKLA
4. "codes" tablosunu ✅ işaretle
5. KAYDET!
```

**Test:** Overlay console'da "Realtime connected" görmeli

### 3. ⚠️ OVERLAY BAŞLANGIÇ DURUMU

**SORUN:** Overlay sürekli görünür, kod yokken de!

**ÇÖZÜM:**

```css
.card-container {
  opacity: 0;
  visibility: hidden;
}

.card-container.visible {
  opacity: 1;
  visibility: visible;
}
```

```javascript
// Kod geldiğinde:
card.classList.add('visible');

// Kod bitince:
card.classList.remove('visible');
```

### 4. ⚠️ F5 KALDĞI YERDEN DEVAM

**SORUN:** Sayfa yenilenince kod kaybolur!

**ÇÖZÜM:**

```javascript
async function checkForCode() {
  const code = await getActiveCode();
  if (code.has_code) {
    const elapsed = code.time_since_created; // API'den UTC hesaplı!
    const total = code.countdown_duration + code.duration;

    if (elapsed < total) {
      if (elapsed < code.countdown_duration) {
        // Countdown'u kaldığı yerden başlat
        startCountdown(code.countdown_duration - elapsed, code.code);
      } else {
        // Kod gösterimini devam ettir
        showCode(code.code, total - elapsed);
      }
    }
  }
}
```

### 5. ⚠️ AKTİF KOD KONTROLÜ

**SORUN:** `expires_at`'a bakmak yetmiyor!

**ÇÖZÜM:**

```php
function getActiveCode($streamerId) {
    $code = selectOne('codes', '*', ['streamer_id' => $streamerId, 'is_active' => true]);

    if (!$code) return null;

    $createdAt = new DateTime($code['created_at'], new DateTimeZone('UTC'));
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $elapsed = $now->getTimestamp() - $createdAt->getTimestamp();
    $total = $code['countdown_duration'] + $code['duration'];

    if ($elapsed < $total) {
        return $code; // Hala aktif
    }
    return null;
}
```

### 6. ⚠️ DATABASE QUERY METODLARı

**SORUN:** Supabase query string syntax desteklenmeli!

**ÇÖZÜM:**

```php
public function select($table, $columns, $conditions) {
    if (is_string($conditions)) {
        // Direct: "id=eq.123&is_active=eq.true"
        $endpoint = "$table?select=$columns&$conditions";
    } else {
        // Array to query string
        $queryString = $this->buildQueryString($conditions);
        $endpoint = "$table?select=$columns&$queryString";
    }
    return $this->request($endpoint, 'GET');
}
```

### 7. ⚠️ PEŞ PEŞE KOD GÜVENLİĞİ

**SORUN:** Aktif kod varken yeni kod gönderilebiliyor!

**ÇÖZÜM:**

```php
$activeCode = $db->getActiveCode($streamerId);
if ($activeCode['success']) {
    return error('Zaten aktif bir kod var!');
}
```

### 8. ⚠️ CACHE TEMİZLEME

**SORUN:** Kod değişiklikleri yansımıyor!

**ÇÖZÜM:**

```php
clearFileCache('active_code_' . $userId);
```

```bash
# Manual cleanup
rm -f cache/active_code_*
```

### 9. ⚠️ EKSIK KOLONLAR

**SORUN:** Migration yapılmamış, kolonlar yok!

**ÇÖZÜM:**

```sql
-- Kontrol et
SELECT column_name FROM information_schema.columns
WHERE table_name = 'users' AND column_name = 'sound_enabled';

-- Yoksa migration'ı çalıştır
ALTER TABLE users ADD COLUMN sound_enabled BOOLEAN DEFAULT TRUE;
```

### 10. ⚠️ ADMIN BONUS KOD SİSTEMİ

**SORUN:** Admin kodları da bakiye düşürüyor!

**ÇÖZÜM:**

```php
// Admin kod gönderirken:
$codeData = [
    'is_bonus_code' => true, // Bakiye düşmeyecek!
    // ...
];

// Kod kullanımında:
if (!$code['is_bonus_code']) {
    // Sadece normal kodlarda bakiye kontrol et
    updateBalance($streamerId, -$rewardAmount);
}
```

### 11. ⚠️ CRON TİMİNG GECİKMESİ

**SORUN:** Cron 59. saniyede çalışırsa kod 1 dakika gecikir!

**ÇÖZÜM:**

```php
// 45 saniye tolerans ekle
$now = new DateTime('now', new DateTimeZone('UTC'));
$nowPlusTolerance = clone $now;
$nowPlusTolerance->modify('+45 seconds');

$users = $db->select('users', '*',
    "next_code_time=lte.{$nowPlusTolerance->format('Y-m-d\TH:i:s.u\Z')}"
);
```

### 12. ⚠️ CRON CLEANUP TOLERANS HATASI

**SORUN:** Yeni oluşturulan kodlar hemen expire oluyor!

**ÇÖZÜM:**

```php
// ❌ YANLIŞ - Toleranslı zamanı kullanma
$db->update('codes', ['is_active' => false],
    "expires_at=lt.{$nowPlusTolerance->format(...)}");

// ✅ DOĞRU - Gerçek zamanı kullan
$db->update('codes', ['is_active' => false],
    "expires_at=lt.{$now->format(...)}");
```

### 13. ⚠️ getActiveCode() TIMEZONE HATASI

**SORUN:** `strtotime()` lokal timezone kullanıyor!

**ÇÖZÜM:**

```php
// ❌ YANLIŞ
$createdAt = strtotime($code['created_at']);
$now = time();
$elapsed = $now - $createdAt; // 3 saat fark!

// ✅ DOĞRU
$createdAt = new DateTime($code['created_at'], new DateTimeZone('UTC'));
$now = new DateTime('now', new DateTimeZone('UTC'));
$elapsed = $now->getTimestamp() - $createdAt->getTimestamp();
```

### 14. ⚠️ DATABASE UPDATE() PARAMETRE SIRASI

**SORUN:** API başarılı diyor ama kaydetmiyor!

**ÇÖZÜM:**

```php
// ❌ YANLIŞ
$db->update('users', ['id' => $userId], $data);

// ✅ DOĞRU - Signature: update($table, $data, $conditions)
$db->update('users', $data, ['id' => $userId]);
```

---

## 🔧 TEKNOLOJİLER

**Backend:**

- PHP 7.4+
- Supabase (PostgreSQL + Realtime)
- Twitch OAuth 2.0
- cURL
- File-based cache

**Frontend:**

- HTML5, CSS3
- Vanilla JavaScript
- Supabase JS Client (CDN)
- Web Audio API

**Database:**

- PostgreSQL (via Supabase)
- 6 tablo: users, codes, submissions, payout_requests, balance_topups, settings

**Deployment:**

- Shared hosting (cPanel)
- HTTPS zorunlu
- Cron job (1 dakika)

---

**Next:** `02-file-structure.md` → Tam dosya yapısı
