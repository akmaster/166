# TWITCH CODE REWARD SYSTEM - TAM PROJE PROMPT

## 📋 PROJE ÖZETİ

Twitch yayıncıları için **otomatik kod ödül sistemi** geliştir. Yayın sırasında ekranda kodlar gösterilir, izleyiciler bu kodları girerek para kazanır. Sistem tamamen otomatik çalışır ve gerçek zamanlı (Supabase Realtime) kod gösterimi yapar.

---

## 🚨 KRİTİK UYARILAR VE YAYIN HATALAR

Bu bölümü dikkatlice oku! Yaygın hataları ve çözümlerini içerir.

### 1. ⚠️ TIMEZONE HATASI (En Kritik!)

```php
// ❌ YANLIŞ
$now = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
$formatted = $now->format('Y-m-d\TH:i:s.u\Z');
// 15:22:38Z yazıyor ama bu Istanbul saati, UTC değil!
// Supabase bunu 3 saat ileri olarak algılıyor!

// ✅ DOĞRU
$now = new DateTime('now', new DateTimeZone('UTC'));
$formatted = $now->format('Y-m-d\TH:i:s.u\Z');
// 12:22:38Z yazıyor ve bu gerçek UTC!
```

### 2. ⚠️ SUPABASE REALTIME KURULUMU

```
Adımlar:
1. Supabase Dashboard → Database → Replication
2. Publications bölümünde "supabase_realtime" bul
3. "0 tables" yazıyorsa TIKLA
4. "codes" tablosunu işaretle
5. MUTLAKA KAYDET!

Test: Overlay console'da "Realtime connected" görmeli
```

### 3. ⚠️ OVERLAY BAŞLANGIÇ DURUMU

```css
/* Overlay başlangıçta GİZLİ olmalı! */
.card-container {
  opacity: 0;
  visibility: hidden;
}

/* Kod gelince GÖRÜNÜR yap */
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

```javascript
// Sayfa yenilendiğinde aktif kodu bul ve devam et
async function checkForCode() {
  const code = await getActiveCode();
  if (code.has_code) {
    const elapsed = code.time_since_created; // API'den al!
    const total = code.countdown_duration + code.duration;

    if (elapsed < total) {
      // Hala aktif - kaldığı yerden devam et
      if (elapsed < code.countdown_duration) {
        // Countdown aşamasında
        startCountdown(code.countdown_duration - elapsed, code.code);
      } else {
        // Kod gösterim aşamasında
        showCode(code.code, total - elapsed);
      }
    }
  }
}
```

### 5. ⚠️ AKTİF KOD KONTROLÜ

```php
// ❌ YANLIŞ - Sadece expires_at'a bakma
function getActiveCode($streamerId) {
    return selectOne('codes', '*', [
        'streamer_id' => $streamerId,
        'expires_at' => ['gt', date('c')]
    ]);
}

// ✅ DOĞRU - Countdown + duration toplamına bak
function getActiveCode($streamerId) {
    $code = selectOne('codes', '*', [
        'streamer_id' => $streamerId,
        'is_active' => true
    ]);

    $elapsed = time() - strtotime($code['created_at']);
    $total = $code['countdown_duration'] + $code['duration'];

    if ($elapsed < $total) {
        return $code; // Hala aktif
    }
    return null;
}
```

### 6. ⚠️ DATABASE QUERY METODLARı

```php
// Supabase query string syntax desteği ekle!
class Database {
    // String conditions destekle
    public function select($table, $columns, $conditions) {
        if (is_string($conditions)) {
            // Direct query string: "id=eq.123&is_active=eq.true"
            $endpoint = "$table?select=$columns&$conditions";
        } else {
            // Array to query string conversion
            $queryString = $this->buildQueryString($conditions);
            $endpoint = "$table?select=$columns&$queryString";
        }
        return $this->request($endpoint, 'GET');
    }
}
```

### 7. ⚠️ PEŞ PEŞE KOD GÜVENLİĞİ

```php
// Aktif kod varken yeni kod gönderme!
$activeCode = $db->getActiveCode($streamerId);
if ($activeCode['success']) {
    return error('Zaten aktif bir kod var. Lütfen kod bitene kadar bekleyin.');
}
```

### 8. ⚠️ CACHE TEMİZLEME

```bash
# Timezone fix'inden sonra cache dosyalarını sil!
rm -f cache/active_code_*

# Veya PHP'de:
clearFileCache('active_code_' . $userId);
```

### 9. ⚠️ EKSIK KOLONLAR

```sql
-- Migration gerekebilir:
ALTER TABLE users ADD COLUMN IF NOT EXISTS twitch_display_name VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS overlay_theme VARCHAR(50) DEFAULT 'neon';
ALTER TABLE codes ADD COLUMN IF NOT EXISTS is_bonus_code BOOLEAN DEFAULT FALSE;

-- Index'leri ekle
CREATE INDEX IF NOT EXISTS idx_users_twitch_display_name ON users(twitch_display_name);
```

### 10. ⚠️ ADMIN BONUS KOD SİSTEMİ

```php
// Admin panelinden gönderilen kodlar is_bonus_code = TRUE
$codeData = [
    'streamer_id' => $streamerId,
    'code' => $code,
    'is_bonus_code' => true, // Bakiye düşmeyecek!
    'created_at' => $now->format('Y-m-d\TH:i:s.u\Z')
];

// Kod kullanımında:
if (!$code['is_bonus_code']) {
    // Sadece normal kodlarda bakiye kontrolü yap
    if ($streamerBalance < $rewardAmount) {
        return error('Bakiye yetersiz');
    }
    updateBalance($streamerId, -$rewardAmount);
}
```

### 11. ⚠️ CRON TİMİNG GECİKMESİ

```php
// ❌ SORUN: Cron 59. saniyede çalışırsa 60. saniyelik kod 1 dakika gecikir
$now = new DateTime('now', new DateTimeZone('UTC'));
$nowFormatted = $now->format('Y-m-d\TH:i:s.u\Z');
$usersResult = $db->query("users?select=*&next_code_time=lte.$nowFormatted");

// ✅ ÇÖZÜM: 45 saniye tolerans ekle
$now = new DateTime('now', new DateTimeZone('UTC'));
$nowPlusTolerance = (clone $now)->modify('+45 seconds');
$nowFormatted = $nowPlusTolerance->format('Y-m-d\TH:i:s.u\Z');
$usersResult = $db->query("users?select=*&next_code_time=lte.$nowFormatted");

// Artık cron 59. saniyede çalışsa bile 60. saniyelik kod üretilir!
```

### 12. ⚠️ CRON CLEANUP TOLERANS HATASI (KRİTİK!)

```php
// ❌ SORUN: Cleanup toleranslı zamanı kullanıyor, yeni kod hemen expire oluyor!
$nowPlusTolerance = (clone $now)->modify('+45 seconds'); // 14:38:45
$nowFormatted = $nowPlusTolerance->format('Y-m-d\TH:i:s.u\Z');

// Kod oluştur: created_at = 14:38:00, expires_at = 14:38:35
// Cleanup: expires_at < 14:38:45 ? → EVET! → is_active = false ❌

// ✅ ÇÖZÜM: Cleanup için GERÇEK zamanı kullan
$nowPlusTolerance = (clone $now)->modify('+45 seconds'); // User selection için
$nowFormatted = $nowPlusTolerance->format('Y-m-d\TH:i:s.u\Z');
$usersResult = $db->query("users?...&next_code_time=lte.$nowFormatted");

// Cleanup için GERÇEK zaman
$nowRealFormatted = $now->format('Y-m-d\TH:i:s.u\Z'); // 14:38:00
$expiredResult = $db->query("codes?is_active=eq.true&expires_at=lt.$nowRealFormatted");
// expires_at < 14:38:00 ? → HAYIR! → Kod SAFE! ✅
```

### 13. ⚠️ getActiveCode() TIMEZONE HATASI

```php
// ❌ SORUN: strtotime() lokal timezone kullanıyor
$createdAt = strtotime($code['created_at']); // Lokal parse!
$now = time();
$elapsed = $now - $createdAt; // 3 saat fark olur!

// ✅ ÇÖZÜM: DateTime ile UTC kullan
$createdAt = new DateTime($code['created_at'], new DateTimeZone('UTC'));
$now = new DateTime('now', new DateTimeZone('UTC'));
$elapsed = $now->getTimestamp() - $createdAt->getTimestamp();
```

### 14. ⚠️ DATABASE UPDATE() PARAMETRE SIRASI

```php
// ❌ YANLIŞ - Parametreler ters sırada!
$db->update('users', ['id' => $userId], $data);
//                    ^^^^^^^^^^^^^^    ^^^^^
//                    Bu CONDITIONS     Bu DATA olarak gidiyor!

// ✅ DOĞRU - Doğru sıralama: update($table, $data, $conditions)
$db->update('users', $data, ['id' => $userId]);
//                    ^^^^^  ^^^^^^^^^^^^^^
//                    DATA   CONDITIONS

// Database class imzası:
public function update($table, $data, $conditions = []) {
    // 1. parametre: table name
    // 2. parametre: güncellenecek data (SET kısmı)
    // 3. parametre: WHERE koşulları
}
```

**Belirti:** API success:true döndürüyor ama veritabanında değişiklik yok!  
**Çözüm:** Tüm `$db->update()` çağrılarını kontrol et, parametre sırası doğru mu?

---

## 📁 PROJE DOSYA YAPISI

```
twitch-code-reward/
│
├── 📄 index.php                    # Ana dashboard (landing + izleyici/yayıncı tabs)
├── 📄 streamers.php                # Canlı yayıncılar listesi
├── 📄 callback.php                 # Twitch OAuth callback
├── 📄 cron.php                     # Otomatik kod üretimi (cron job)
├── 📄 .env                         # Konfigürasyon (Supabase, Twitch, Admin)
├── 📄 README.md                    # Genel dokümantasyon
├── 📄 INSTALLATION.md              # Kurulum rehberi
├── 📄 QUICK_START.md               # Hızlı başlangıç
├── 📄 prompt.md                    # Bu dosya (tam proje prompt)
│
├── 📂 config/                      # Konfigürasyon dosyaları
│   ├── config.php                  # Ana config (sabitler, session)
│   ├── database.php                # Supabase Database class (REST API wrapper)
│   └── helpers.php                 # Yardımcı fonksiyonlar
│
├── 📂 database/                    # Veritabanı şemaları ve migrationlar
│   ├── schema.sql                  # Tam veritabanı şeması (tüm tablolar)
│   └── migrations/                 # Veritabanı migrationları
│       ├── README.md               # Migration kullanım rehberi
│       ├── add_is_bonus_code.sql   # Bonus kod sistemi
│       ├── add_twitch_display_name.sql  # Display name + overlay theme
│       └── add_sound_settings.sql  # Ses ayarları kolonları
│
├── 📂 api/                         # API endpoints (JSON responses)
│   ├── auth.php                    # Twitch OAuth başlat
│   ├── logout.php                  # Çıkış yap
│   ├── get-active-code.php         # Aktif kod getir (overlay için)
│   ├── submit-code.php             # Kod gönder (izleyici)
│   ├── get-activity.php            # Son aktiviteler
│   ├── get-live-streamers.php      # Canlı yayıncılar
│   ├── get-public-stats.php        # Genel istatistikler
│   ├── update-reward-amount.php    # Ödül miktarı güncelle
│   ├── update-code-settings.php    # Kod ayarları güncelle
│   ├── update-random-reward.php    # Rastgele ödül ayarları
│   ├── update-sound-settings.php   # Ses ayarları güncelle
│   ├── update-theme.php            # Overlay teması değiştir
│   ├── request-payout.php          # Ödeme talebi oluştur
│   ├── request-topup.php           # Bakiye yükleme talebi
│   ├── calculate-budget.php        # Bütçe hesaplama
│   ├── apply-budget-settings.php   # Bütçe ayarlarını uygula
│   └── admin/                      # Admin API'leri
│       ├── generate-code.php       # Manuel kod gönder
│       └── get-code-details.php    # Kod detayları
│
├── 📂 admin/                       # Admin paneli
│   ├── login.php                   # Admin girişi
│   ├── logout.php                  # Admin çıkış
│   ├── index.php                   # Admin dashboard
│   ├── users.php                   # Kullanıcı yönetimi
│   ├── codes.php                   # Kod yönetimi
│   ├── payouts.php                 # Ödeme talepleri
│   ├── balance-topups.php          # Bakiye yükleme talepleri
│   ├── settings.php                # Sistem ayarları
│   ├── assets/                     # Admin CSS/JS
│   │   └── admin.css               # Admin panel stilleri
│   └── includes/                   # Admin includes
│       ├── header.php              # Admin header
│       └── footer.php              # Admin footer
│
├── 📂 components/                  # Yeniden kullanılabilir componentler
│   ├── RewardSettings/             # Ödül miktarı ayarlama
│   │   ├── RewardSettings.php      # Component HTML
│   │   ├── RewardSettings.js       # Component JS
│   │   ├── RewardSettings.css      # Component CSS
│   │   └── *.min.*                 # Minified versiyonlar
│   │
│   ├── RandomReward/               # Rastgele ödül sistemi
│   │   ├── RandomReward.php
│   │   ├── RandomReward.js
│   │   ├── RandomReward.css
│   │   └── *.min.*
│   │
│   ├── CodeSettings/               # Kod zamanlama ayarları
│   │   ├── CodeSettings.php        # Countdown, duration, interval
│   │   ├── CodeSettings.js         # Preset'ler, validasyon, timing info
│   │   ├── CodeSettings.css
│   │   └── *.min.*
│   │
│   ├── SoundSettings/              # Ses kontrol sistemi
│   │   ├── SoundSettings.php       # Master toggle, ses seçimi, başlama zamanı
│   │   ├── SoundSettings.js        # Preview, kaydetme, toggle logic
│   │   └── SoundSettings.css       # Gradient design, toggles
│   │
│   └── BudgetCalculator/           # Bütçe hesaplama aracı
│       ├── BudgetCalculator.php    # Kalkülator UI
│       ├── BudgetCalculator.js     # Hesaplama mantığı
│       ├── BudgetCalculator.css
│       └── *.min.*
│
├── 📂 overlay/                     # OBS overlay dosyaları
│   ├── index.php                   # Overlay ana sayfa (token ile erişim)
│   ├── themes.css                  # 20 overlay teması
│   └── sounds.js                   # 20 ses fonksiyonu (Web Audio API)
│
├── 📂 assets/                      # Genel asset'ler
│   ├── css/                        # CSS dosyaları
│   │   ├── style.css               # Ana stil dosyası
│   │   ├── style.min.css           # Minified
│   │   ├── landing.css             # Landing page stilleri
│   │   └── landing.min.css         # Minified
│   └── js/                         # JavaScript dosyaları
│       ├── main.js                 # Ana JS (tab switching, modals)
│       └── main.min.js             # Minified
│
├── 📂 cache/                       # File-based cache (otomatik oluşur)
│   └── *.cache                     # Cache dosyaları (active_code_*, user_*)
│
└── 📂 memory-bank/                 # Cursor AI hafızası (opsiyonel)
    ├── projectbrief.md             # Proje özeti
    ├── productContext.md           # Ürün bağlamı
    ├── systemPatterns.md           # Sistem desenleri
    ├── techContext.md              # Teknoloji bağlamı
    ├── activeContext.md            # Güncel çalışma
    └── progress.md                 # İlerleme takibi
```

### 📦 Toplam Dosya Sayısı:

- **PHP Files:** ~45
- **JavaScript Files:** ~12
- **CSS Files:** ~12
- **SQL Files:** 4
- **Config Files:** 1 (.env)
- **Documentation:** 4

### 🔑 Kritik Dosyalar:

1. **`.env`** → Tüm hassas bilgiler (ASLA commit etme!)
2. **`config/database.php`** → Supabase REST API wrapper
3. **`cron.php`** → Otomatik kod üretimi (her 1 dakikada çalışmalı)
4. **`overlay/index.php`** → OBS tarafından yüklenecek overlay
5. **`database/schema.sql`** → İlk kurulumda çalıştır

---

## 🎯 ANA ÖZELLİKLER

### İzleyici Özellikleri:

- Twitch OAuth ile giriş
- Yayında gözüken 6 haneli kodu girme
- Her kod girişinde ödül kazanma (yayıncıya göre değişken)
- Bakiye takibi
- Minimum eşiğe ulaşınca ödeme talebi
- Mobil uyumlu UI

### Yayıncı Özellikleri:

- Twitch OAuth ile giriş
- Bakiye yükleme sistemi (dekont ile talep)
- OBS overlay linki (her yayıncıya özel token)
- **Kod ayarları kontrolü (countdown, duration, interval)**
- **Ödül miktarı belirleme (sabit veya rastgele)**
- **20+ overlay teması seçimi**
- **Ses sistemi (10 kod sesi + 10 countdown sesi)**
- Bütçe hesaplama aracı
- İstatistik gösterimi

### Admin Paneli:

- Admin login (username/password)
- Kod yönetimi
- Kullanıcı listesi
- Ödeme talepleri onaylama
- Bakiye yükleme talepleri onaylama
- İstatistikler
- Sistem ayarları (minimum ödeme eşiği)

### OBS Overlay:

- **Supabase Realtime entegrasyonu (anında kod gösterimi)**
- 3D card flip animasyonu (countdown → kod)
- 20 farklı tema desteği
- Debug panel (gelişmiş)
- Ses sistemi entegrasyonu
- Responsive tasarım (1920x1080)

---

## 🛠️ TEKNİK STACK

### Backend:

- **PHP 7.4+** (shared hosting uyumlu)
- **Supabase** (PostgreSQL database + Realtime)
- **cURL** (API requests)
- Dosya tabanlı cache sistemi

### Frontend:

- HTML5, CSS3, JavaScript (Vanilla)
- Supabase JS Client Library (CDN)
- Web Audio API (ses sistemi)
- CSS animations & 3D transforms

### Entegrasyonlar:

- **Twitch OAuth 2.0** (kullanıcı girişi)
- **Twitch API** (canlı yayın bilgileri)
- **Supabase REST API** (CRUD)
- **Supabase Realtime** (WebSocket, kod gösterimi)

### Deployment:

- Shared hosting (cPanel + cron job)
- HTTPS zorunlu
- .env konfigürasyonu

---

## 🗄️ VERİTABASI YAPISI (Supabase PostgreSQL)

### ⚠️ KRITIK: Timezone ve Supabase Realtime Uyarıları

**1. TIMEZONE HATASI (En Yaygın Hata!):**

```php
// ❌ YANLIŞ - Istanbul timezone kullanıp UTC olarak işaretle
$now = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
$now->format('Y-m-d\TH:i:s.u\Z'); // Z = UTC demek ama 15:22 Istanbul'du!

// ✅ DOĞRU - Supabase için MUTLAKA UTC kullan
$now = new DateTime('now', new DateTimeZone('UTC'));
$now->format('Y-m-d\TH:i:s.u\Z');
```

**2. SUPABASE REALTIME KURULUMU:**

```
Dashboard → Database → Replication → Publications
→ "supabase_realtime" publication'ını bul
→ "codes" tablosunu ekle ve KAYDET!
```

Eğer bu adım yapılmazsa overlay'de kod gözükmez!

**3. AKTIF KOD KONTROLÜ:**

```php
// Kod aktifliğini kontrol ederken countdown + duration toplamına bak
$elapsed = $now - $createdAt;
$totalDuration = $countdown + $duration;
if ($elapsed < $totalDuration) {
    // Kod hala aktif (countdown veya gösterim aşamasında)
}
```

### 1. `users` Tablosu

```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    twitch_user_id VARCHAR(255) UNIQUE NOT NULL,
    twitch_username VARCHAR(255) NOT NULL,
    twitch_display_name VARCHAR(255), -- ⚠️ ZORUNLU! Username'den farklı olabilir
    twitch_email VARCHAR(255),
    twitch_avatar_url TEXT,
    overlay_token VARCHAR(64) UNIQUE NOT NULL,
    streamer_balance DECIMAL(10, 2) DEFAULT 0.00,
    custom_reward_amount DECIMAL(10, 2) DEFAULT NULL,
    custom_code_duration INT DEFAULT NULL, -- saniye
    custom_code_interval INT DEFAULT NULL, -- saniye
    custom_countdown_duration INT DEFAULT NULL, -- saniye
    use_random_reward BOOLEAN DEFAULT FALSE,
    random_reward_min DECIMAL(10, 2) DEFAULT NULL,
    random_reward_max DECIMAL(10, 2) DEFAULT NULL,
    sound_enabled BOOLEAN DEFAULT TRUE,
    sound_type VARCHAR(50) DEFAULT 'threeTone',
    countdown_sound_type VARCHAR(50) DEFAULT 'none',
    overlay_theme VARCHAR(50) DEFAULT 'neon', -- ⚠️ ZORUNLU! Overlay teması
    next_code_time TIMESTAMPTZ DEFAULT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
```

**Önemli Kolonlar:**

- `overlay_token`: OBS overlay için özel token
- `streamer_balance`: Yayıncının dağıtacağı para
- `custom_*`: Yayıncıya özel ayarlar (NULL = sistem varsayılanı kullan)
- `next_code_time`: Bir sonraki kod zamanı (countdown için)

### 2. `codes` Tablosu

```sql
CREATE TABLE codes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    streamer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    code VARCHAR(6) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_bonus_code BOOLEAN DEFAULT FALSE, -- ⚠️ ZORUNLU! Admin bonus kodları için
    expires_at TIMESTAMPTZ NOT NULL,
    duration INT DEFAULT 30, -- saniye
    countdown_duration INT DEFAULT 5, -- saniye
    created_at TIMESTAMPTZ DEFAULT NOW()
);
```

**⚠️ is_bonus_code Açıklaması:**

- `TRUE`: Admin panelinden gönderilen bonus kod (yayıncı bakiyesinden düşmez)
- `FALSE`: Normal kod (cron veya yayıncının kendi kodu, bakiye düşer)

```php
// Kod kullanımında kontrol:
if (!$code['is_bonus_code']) {
    // Normal kod - bakiye kontrolü yap
    if ($streamerBalance < $rewardAmount) {
        return error('Yayıncı bakiyesi yetersiz');
    }
    // Bakiyeden düş
    updateBalance($streamerId, -$rewardAmount);
}
// Bonus kodda bakiye kontrolü YOK!
```

**İndeksler:**

```sql
CREATE INDEX idx_codes_streamer_id ON codes(streamer_id);
CREATE INDEX idx_codes_active ON codes(is_active) WHERE is_active = TRUE;
CREATE INDEX idx_codes_expires_at ON codes(expires_at);
CREATE INDEX idx_codes_code ON codes(code);
```

### 3. `submissions` Tablosu

```sql
CREATE TABLE submissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    code_id UUID REFERENCES codes(id) ON DELETE CASCADE,
    streamer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    reward_amount DECIMAL(10, 2) NOT NULL,
    submitted_at TIMESTAMPTZ DEFAULT NOW()
);
```

**İndeksler:**

```sql
CREATE INDEX idx_submissions_user_id ON submissions(user_id);
CREATE INDEX idx_submissions_code_id ON submissions(code_id);
CREATE INDEX idx_submissions_streamer_id ON submissions(streamer_id);
CREATE INDEX idx_submissions_submitted_at ON submissions(submitted_at DESC);
```

### 4. `payout_requests` Tablosu

```sql
CREATE TABLE payout_requests (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending', -- pending, completed, rejected
    requested_at TIMESTAMPTZ DEFAULT NOW(),
    processed_at TIMESTAMPTZ DEFAULT NULL
);
```

**İndeksler:**

```sql
CREATE INDEX idx_payout_requests_user_id ON payout_requests(user_id);
CREATE INDEX idx_payout_requests_status ON payout_requests(status);
```

### 5. `balance_topups` Tablosu

```sql
CREATE TABLE balance_topups (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    streamer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_proof TEXT,
    note TEXT,
    status VARCHAR(20) DEFAULT 'pending', -- pending, approved, rejected
    requested_at TIMESTAMPTZ DEFAULT NOW(),
    processed_at TIMESTAMPTZ DEFAULT NULL
);
```

### 6. `settings` Tablosu

```sql
CREATE TABLE settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    key VARCHAR(255) UNIQUE NOT NULL,
    value JSONB NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Varsayılan ayarlar
INSERT INTO settings (key, value) VALUES
('payout_threshold', '5.00'),
('reward_per_code', '0.10'),
('code_duration', '30'),
('code_interval', '600');
```

---

## 📁 DOSYA YAPISI

```
project/
├── config/
│   ├── config.php              # .env yükleme, sabitler, session
│   ├── database.php            # Supabase REST API wrapper class
│   └── helpers.php             # 30+ yardımcı fonksiyon
│
├── api/                        # REST API endpoints
│   ├── auth.php               # Twitch OAuth redirect
│   ├── submit-code.php        # Kod girişi
│   ├── get-active-code.php    # Aktif kod getir (cache'li)
│   ├── get-activity.php       # Kullanıcı aktiviteleri
│   ├── request-payout.php     # Ödeme talebi
│   ├── request-topup.php      # Bakiye yükleme talebi
│   ├── update-reward-amount.php
│   ├── update-code-settings.php  # Countdown, duration, interval
│   ├── update-random-reward.php
│   ├── update-sound-settings.php
│   ├── calculate-budget.php
│   ├── get-live-streamers.php    # Twitch API entegrasyonu
│   ├── get-public-stats.php
│   └── logout.php
│
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── index.php              # Dashboard
│   ├── codes.php              # Kod yönetimi
│   ├── users.php              # Kullanıcı listesi
│   ├── payouts.php            # Ödeme talepleri
│   ├── balance-topups.php     # Bakiye yükleme talepleri
│   ├── settings.php           # Sistem ayarları
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   └── api/
│       ├── generate-code.php
│       ├── process-topup.php
│       └── stats.php
│
├── components/                 # Modüler component'ler
│   ├── CodeSettings/
│   │   ├── CodeSettings.php
│   │   ├── CodeSettings.css
│   │   ├── CodeSettings.min.css
│   │   ├── CodeSettings.js
│   │   └── CodeSettings.min.js
│   ├── RewardSettings/
│   ├── RandomReward/
│   └── BudgetCalculator/
│
├── overlay/
│   └── index.php              # OBS overlay (Supabase Realtime)
│
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── style.min.css
│   │   ├── admin.css
│   │   ├── admin.min.css
│   │   ├── landing.css
│   │   └── landing.min.css
│   └── js/
│       ├── main.js
│       ├── main.min.js
│       ├── admin.js
│       └── admin.min.js
│
├── cache/                      # File-based cache (otomatik oluşur)
├── index.php                   # Ana sayfa (landing + dashboard)
├── streamers.php               # Canlı yayıncılar sayfası
├── callback.php                # Twitch OAuth callback
├── cron.php                    # Otomatik kod üretici (cron job)
├── .env                        # Konfigürasyon (GİZLİ!)
├── .env.example                # Örnek .env dosyası
├── .gitignore
└── README.md
```

---

## 🔑 ÖNEMLİ İŞ MANTIĞI KURALLARI

### Kod Yaşam Döngüsü:

1. **Kod Üretimi (cron.php - her dakika çalışır)**

   - Her yayıncı için son koddan bu yana `custom_code_interval` (varsayılan: 600s) geçti mi kontrol
   - Geçtiyse yeni kod üret
   - `expires_at = now + custom_code_duration` (varsayılan: 30s)
   - Supabase'e INSERT (Realtime trigger için)
   - Cache temizle
   - `next_code_time` güncelle

2. **Kod Gösterimi (overlay/index.php)**

   - Supabase Realtime ile dinle (`streamer_id` filtreli)
   - Yeni kod geldiğinde:
     - `countdown_duration` > 0 ise countdown göster (ön yüz)
     - Countdown bitince 3D flip animasyonu
     - Kodu göster (arka yüz)
     - `duration` saniye sonra gizle
   - Ses sistemi entegre

3. **Kod Girişi (api/submit-code.php)**

   - Kod format kontrolü (6 haneli rakam)
   - Kod var mı?
   - Süresi dolmamış mı?
   - Countdown fazında mı? (ilk 5 saniye kod girilemiyor)
   - Kullanıcı bu kodu daha önce kullanmış mı?
   - Yayıncının bakiyesi yeterli mi?
   - **Ödül hesaplama:**
     - `use_random_reward = TRUE` ise: `RAND(min, max)`
     - Değilse: `custom_reward_amount` veya varsayılan 0.10
   - Yayıncı bakiyesinden düş
   - `submissions` tablosuna kaydet
   - Başarı döndür

4. **Bakiye Hesaplama**

   ```php
   Kullanıcı Bakiyesi =
       SUM(submissions.reward_amount WHERE user_id = X)
       - SUM(payout_requests.amount WHERE user_id = X AND status = 'completed')
   ```

5. **Ödeme Talebi**

   - Bakiye >= `payout_threshold` (varsayılan: 5 TL) mı kontrol
   - `payout_requests` tablosuna ekle (status: pending)
   - Admin onayı bekle
   - Admin onaylarsa: status = completed, bakiyeden düş

6. **Bakiye Yükleme**
   - Yayıncı: miktar + dekont URL ile talep
   - `balance_topups` tablosuna ekle (status: pending)
   - Admin onaylarsa: `streamer_balance`'a ekle, status = approved

---

## 🎨 ÖZELLİK DETAYLARI

### 1. Kod Ayarları (CodeSettings Component)

**Kullanıcı Kontrolleri (Yayıncı):**

- **Countdown Duration**: 0-300 saniye (kod gösterilmeden önce hazırlık)
- **Code Duration**: 1-9,999,999 saniye (kod ekranda kalma süresi)
- **Code Interval**: 1-9,999,999 saniye (kodlar arası bekleme)

**Kurallar:**

```javascript
Duration >= Countdown + 10; // Güvenlik
Interval >= Duration + 30; // Bekleme süresi
```

**Presetler:**

- Hızlı: 3s countdown, 15s duration, 60s interval
- Normal: 5s countdown, 30s duration, 300s interval (5 dk)
- Rahat: 10s countdown, 60s duration, 600s interval (10 dk)

**Instant Apply:**

- Ayar değişince mevcut kod expire edilir
- Cache temizlenir
- Yeni ayarlarla kod üretilir (~1 dakika içinde)

### 2. Rastgele Ödül Sistemi

- Kapalı: Sabit ödül (`custom_reward_amount` veya 0.10 TL)
- Açık: Her kod girişinde rastgele miktar
  - Min: 0.05 - 10.00 TL arası
  - Max: 0.05 - 10.00 TL arası
  - Örnek: 0.10-0.20 TL arası (her kullanıcı farklı kazanır)

### 3. Bütçe Hesaplama Aracı

**Input:**

- Toplam bütçe (TL)
- Yayın süresi (saat)
- Tahmini izleyici sayısı

**Output:**

- Önerilen kod aralığı (dakika)
- Önerilen ödül miktarı
- Beklenen toplam kod sayısı
- Beklenen toplam maliyet
- Katılım oranı (%30 varsayılan)

**Tek tıkla uygula:** Hesaplanan ayarları otomatik ata

### 4. Tema Sistemi (20 Tema)

**Oyun Temaları (10):**

- Valorant, League of Legends, CS:GO, Dota 2, PUBG
- Fortnite, Apex Legends, Minecraft, GTA V, FIFA

**Renk Temaları (10):**

- Neon, Sunset, Ocean, Purple, Cherry
- Minimal, Dark, Sakura, Cyber, Arctic

**Her tema:**

- CSS değişkenleri (`--theme-primary`, `--theme-secondary`)
- Tema ikonu
- Canlı önizleme
- Overlay URL'de parametre: `?theme=valorant`

### 5. Ses Sistemi (Web Audio API)

**Kod Gösterim Sesleri (10):**

- threeTone, successBell, gameCoin, digitalBlip, powerUp
- notification, cheerful, simple, epic, gentle

**Countdown Sesleri (10):**

- none (sessiz), tickTock, digitalBeep, drum, heartbeat
- countdown, arcade, tension, robot, lastThree

**Ayarlar:**

- Ses Açık/Kapalı (toggle)
- Kod sesi seçimi (dropdown)
- Countdown sesi seçimi (dropdown)
- Test butonu (önizleme)

**Teknik:**

```javascript
// Örnek ses fonksiyonu
function playThreeTone(context) {
  const frequencies = [600, 800, 1000];
  frequencies.forEach((freq, i) => {
    const osc = context.createOscillator();
    const gain = context.createGain();
    osc.connect(gain).connect(context.destination);
    osc.frequency.value = freq;
    osc.type = 'sine';
    const start = context.currentTime + i * 0.15;
    gain.gain.setValueAtTime(0.3, start);
    gain.gain.exponentialRampToValueAtTime(0.01, start + 0.15);
    osc.start(start);
    osc.stop(start + 0.15);
  });
}
```

### 6. Supabase Realtime Entegrasyonu

**Overlay'de (JavaScript):**

```javascript
// Client oluştur
const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// Realtime dinle
const channel = supabase
  .channel('codes-changes')
  .on(
    'postgres_changes',
    {
      event: '*',
      schema: 'public',
      table: 'codes',
      filter: `streamer_id=eq.${userId}`,
    },
    (payload) => {
      handleRealtimeCodeChange(payload);
    }
  )
  .subscribe();

// Event handling
function handleRealtimeCodeChange(payload) {
  if (payload.eventType === 'INSERT') {
    // Yeni kod geldi
    if (payload.new.is_active) {
      displayCountdown(payload.new.countdown_duration);
    }
  } else if (payload.eventType === 'UPDATE') {
    // Kod güncellendi
  } else if (payload.eventType === 'DELETE') {
    // Kod silindi
  }
}
```

**Fallback (Realtime başarısız olursa):**

- 5 saniyede bir polling (REST API)
- `/api/get-active-code.php` çağır

### 7. 3D Card Flip Animasyonu

**CSS:**

```css
.card-flipper {
  perspective: 1000px;
  transform-style: preserve-3d;
  transition: transform 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.flipped .card-flipper {
  transform: rotateY(180deg);
}

.card-front,
.card-back {
  backface-visibility: hidden;
}

.card-back {
  transform: rotateY(180deg);
}
```

**Akış:**

1. Countdown göster (front yüz)
2. Countdown bitince `.flipped` class ekle
3. 0.8s flip animasyonu
4. Kod göster (back yüz)

### 8. Twitch Entegrasyonları

**OAuth Login:**

```php
// api/auth.php
$authUrl = 'https://id.twitch.tv/oauth2/authorize?' . http_build_query([
    'client_id' => TWITCH_CLIENT_ID,
    'redirect_uri' => TWITCH_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'user:read:email'
]);
header('Location: ' . $authUrl);
```

**Callback:**

```php
// callback.php
$code = $_GET['code'];
// Exchange code for access token
// Get user info from Twitch API
// Save to database
// Set session
```

**Canlı Yayınlar:**

```php
// api/get-live-streamers.php
// 1. Twitch App Access Token al (cache 1 saat)
// 2. Tüm kullanıcıların Twitch ID'lerini al
// 3. Twitch Streams API çağır (100 ID'ye kadar batch)
// 4. Canlı olanları filtrele
// 5. Metadata ekle (thumbnail, viewer count, game)
```

### 9. Cache Sistemi

**File-based Cache:**

```php
// Kaydet
setFileCache('active_code_' . $userId, $data, $ttl = 2);

// Oku
$cached = getFileCache('active_code_' . $userId, $ttl = 2);

// Temizle
clearFileCache('active_code_' . $userId);
```

**Cache Kullanımı:**

- `get-active-code.php`: 2 saniye TTL
- Twitch App Access Token: 3600 saniye (1 saat)
- Settings: Session cache + static cache

### 10. Performans İyileştirmeleri

**Database İndeksler:**

```sql
-- Kritik indeksler
CREATE INDEX idx_codes_streamer_active ON codes(streamer_id, is_active) WHERE is_active = TRUE;
CREATE INDEX idx_submissions_user_code ON submissions(user_id, code_id);
CREATE INDEX idx_users_overlay_token ON users(overlay_token);
CREATE INDEX idx_users_next_code_time ON users(next_code_time);
```

**CSS/JS Minification:**

- Tüm CSS/JS dosyaları `.min.css` ve `.min.js` versiyonları var
- Production'da minified versiyonlar kullan

**Lazy Loading:**

```html
<img src="..." loading="lazy" width="400" height="300" />
```

---

## 🔒 GÜVENLİK ÖNLEMLERİ

### 1. Environment Variables (.env)

```bash
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_ANON_KEY=eyJ...
SUPABASE_SERVICE_KEY=eyJ...
TWITCH_CLIENT_ID=xxx
TWITCH_CLIENT_SECRET=xxx
TWITCH_REDIRECT_URI=https://yourdomain.com/callback.php
ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=$2y$10$... # password_hash('password', PASSWORD_BCRYPT)
APP_URL=https://yourdomain.com
SESSION_LIFETIME=3600
DEBUG_MODE=false
```

### 2. Session Güvenliği

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // HTTPS only
session_start();
```

### 3. Input Sanitization

```php
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
```

### 4. Rate Limiting

- Kod girişi: Aynı kod için 1 kez
- API endpoint'ler: IP bazlı rate limit (isteğe bağlı)

### 5. Cron Job Security

```php
$cronKey = 'STRONG_RANDOM_KEY'; // .env'den al
if ($_GET['key'] !== $cronKey) die('Unauthorized');
```

---

## 📦 KURULUM ADIMLARı

### 1. Supabase Kurulumu

```sql
-- 1. supabase.com'da proje oluştur
-- 2. SQL Editor'de database tablolarını oluştur (yukarıdaki SQL'leri çalıştır)
-- 3. Settings → API'den URL, Anon Key, Service Key al
-- 4. Realtime'ı etkinleştir (Database → Replication → codes tablosu)
```

### 2. Twitch Developer Setup

```
1. https://dev.twitch.tv/console
2. "Register Your Application"
3. Name: Code Reward System
4. OAuth Redirect URL: https://yourdomain.com/callback.php
5. Category: Website Integration
6. Client ID ve Secret'i kopyala
```

### 3. Hosting Upload

```bash
1. Tüm dosyaları upload et
2. .env dosyasını oluştur ve doldur
3. Dosya izinleri:
   - cache/ : 755 (otomatik oluşur)
   - .env : 600 (gizli)
```

### 4. Cron Job Kurulumu

```
# cPanel → Cron Jobs
# Komut (her dakika):
* * * * * /usr/bin/php /home/username/public_html/cron.php?key=YOUR_SECRET_KEY

# Alternatif: cron-job.org gibi ücretsiz servis
# URL: https://yourdomain.com/cron.php?key=YOUR_SECRET_KEY
# Interval: Her 1 dakika
```

### 5. Admin Şifre Hash

```bash
php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"
# Çıktıyı .env'deki ADMIN_PASSWORD_HASH'e yapıştır
```

---

## 🎯 SAYFA DETAYLARI

### index.php (Ana Sayfa)

**Login değilse:**

- Landing page (hero, stats, live streamers preview, how it works tabs, features, testimonials, CTA)
- Twitch ile giriş butonu

**Login ise:**

- Tab sistemi: İzleyici / Yayıncı
- **İzleyici Tab:**
  - Kod giriş formu
  - Ses ayarları (toggle, kod sesi, countdown sesi)
  - Bakiye gösterimi
  - Ödeme talep butonu (eşiğe ulaştıysa)
  - Son işlemler listesi
- **Yayıncı Tab:**
  - Yayıncı bakiyesi gösterimi
  - Bakiye yükleme butonu (modal)
  - İstatistikler (dağıtılan ödül, kazanan izleyici, kullanılan kod, son aktivite)
  - RewardSettings component
  - CodeSettings component
  - SoundSettings component (ses kontrolü)
  - RandomReward component
  - BudgetCalculator component
  - Tema seçici (20 tema + canlı önizleme)
  - OBS overlay linki (kopyalama)

### streamers.php (Canlı Yayıncılar)

- Sistemdeki tüm kullanıcılar
- Twitch API ile canlı durumu kontrol
- Canlı olanlar üstte (viewer count'a göre sırala)
- Stream bilgileri: thumbnail, game, title, viewer count
- Live badge animasyonu
- İzleyici bakiyesi gösterimi
- "İzle" butonu (Twitch'e yönlendir)

### overlay/index.php (OBS Overlay)

- Token ile yayıncı kontrolü
- Supabase Realtime bağlantı
- Kod kartı (3D flip)
- Debug panel (sağ üst, daraltılabilir)
- Tema desteği (URL parametresi)
- Ses sistemi
- Otomatik fallback (polling)

### admin/\* (Admin Paneli)

- **index.php**: Dashboard (toplam istatistikler)
- **codes.php**: Tüm kodlar, manuel kod üret
- **users.php**: Kullanıcı listesi, bakiye düzenle
- **payouts.php**: Ödeme talepleri (onayla/reddet)
- **balance-topups.php**: Bakiye yükleme talepleri (onayla/reddet)
- **settings.php**: Sistem ayarları (payout_threshold)

---

## 🧩 COMPONENT YAPISI

Her component şunları içerir:

```
components/ComponentName/
├── ComponentName.php      # HTML + PHP logic
├── ComponentName.css      # Styles
├── ComponentName.min.css  # Minified styles
├── ComponentName.js       # Functionality
└── ComponentName.min.js   # Minified JS
```

**Include:**

```php
<?php include __DIR__ . '/components/ComponentName/ComponentName.php'; ?>
```

**Component kendini load eder:**

```php
// ComponentName.php içinde
?>
<link rel="stylesheet" href="/components/ComponentName/ComponentName.min.css">
<div id="componentName">...</div>
<script src="/components/ComponentName/ComponentName.min.js"></script>
```

---

## 📊 API ENDPOINT'LER

### Kullanıcı API'leri:

- `POST /api/auth.php` - Twitch OAuth başlat
- `POST /api/submit-code.php` - Kod gönder
- `GET /api/get-active-code.php?user_id=X` - Aktif kod (cache'li)
- `GET /api/get-activity.php` - Kullanıcı aktiviteleri
- `POST /api/request-payout.php` - Ödeme talebi
- `POST /api/request-topup.php` - Bakiye yükleme
- `POST /api/update-reward-amount.php` - Ödül miktarı değiştir
- `POST /api/update-code-settings.php` - Kod ayarları (countdown, duration, interval)
- `POST /api/update-random-reward.php` - Rastgele ödül ayarla
- `POST /api/update-sound-settings.php` - Ses ayarları
- `POST /api/calculate-budget.php` - Bütçe hesapla
- `GET /api/get-live-streamers.php` - Canlı yayıncılar
- `GET /api/get-public-stats.php` - Genel istatistikler

### Admin API'leri:

- `POST /admin/api/generate-code.php` - Manuel kod üret
- `POST /admin/api/process-topup.php` - Bakiye yükleme onayla/reddet
- `GET /admin/api/stats.php` - Admin istatistikleri

---

## 🚨 HATA AYIKLAMA

### Debug Mode (.env)

```bash
DEBUG_MODE=true
```

**Etkileri:**

- PHP error reporting açık
- Error log dosyası: `error.log`
- Detaylı API hata mesajları
- Overlay debug panel açık

### Overlay Debug Panel

- Durum (bekleniyor, countdown, kod gösteriliyor)
- Countdown timer
- Kod süresi timer
- Sonraki kod timer
- Bağlantı durumu (Realtime / Polling)
- Ses durumu
- Son güncelleme zamanı

### Log Kullanımı:

```php
error_log("Custom message: " . json_encode($data));
```

---

## 🎨 UI/UX DETAYLARI

### Responsive Breakpoints:

```css
/* Mobile */
@media (max-width: 768px) {
  ...;
}

/* Tablet */
@media (min-width: 769px) and (max-width: 1024px) {
  ...;
}

/* Desktop */
@media (min-width: 1025px) {
  ...;
}
```

### Renk Paleti (CSS Variables):

```css
:root {
  --primary: #9147ff; /* Twitch mor */
  --secondary: #00b8d4; /* Açık mavi */
  --success: #00d4aa; /* Yeşil */
  --warning: #ffa502; /* Turuncu */
  --danger: #ff4757; /* Kırmızı */
  --dark: #0e0e10; /* Koyu arka plan */
  --card-bg: #18181b; /* Kart arka plan */
  --text-primary: #efeff1; /* Beyaz metin */
  --text-secondary: #adadb8; /* Gri metin */
}
```

### Animasyonlar:

```css
/* Fade in */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Pulse (countdown) */
@keyframes pulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Glow */
@keyframes glow {
  0%,
  100% {
    box-shadow: 0 0 20px rgba(145, 71, 255, 0.5);
  }
  50% {
    box-shadow: 0 0 40px rgba(145, 71, 255, 1);
  }
}
```

---

## 📝 ÖNEMLİ NOTLAR

### Varsayılan Değerler:

- Countdown: 5 saniye
- Code Duration: 30 saniye
- Code Interval: 600 saniye (10 dakika)
- Reward: 0.10 TL
- Payout Threshold: 5.00 TL
- Participation Rate: %30

### Profesyonel Limitler:

**Minimum Limitler:**

- **Countdown:** 0 saniye
- **Code Duration:** 1 saniye
- **Code Interval:** 60 saniye (1 dakika)
  - **Sebep:** Cron job 1 dakikada bir çalışır
  - Kullanıcı daha az ayarlasa bile sistem 60 saniye kullanır

**Maksimum Limitler:**

- **Countdown:** 300 saniye (5 dakika)
- **Code Duration:** 3600 saniye (1 saat)
- **Code Interval:** 86400 saniye (1 gün / 24 saat)
  - **Sebep:** Gerçekçi ve profesyonel kullanım senaryoları
  - Daha uzun süreler anlamsız ve test edilmemiş görünümü verir

### NULL Değer Mantığı:

```php
// users tablosunda:
custom_reward_amount = NULL    → Sistem varsayılanı kullan (0.10 TL)
custom_code_duration = NULL    → Sistem varsayılanı kullan (30s)
custom_countdown_duration = NULL → Sistem varsayılanı kullan (5s)
custom_code_interval = NULL    → Sistem varsayılanı kullan (600s)
```

### Timezone:

```php
date_default_timezone_set('Europe/Istanbul');
// Tüm datetime'lar UTC'de saklan, gösterimde local'e çevir
```

### Kod Format:

- 6 haneli rakam
- Örnekler: 000000, 123456, 999999
- Regex: `/^\d{6}$/`

---

## 🚀 PRODUCTION DEPLOYMENT

### Pre-launch Checklist:

- [ ] .env dosyası dolduruldu ve güvenli
- [ ] DEBUG_MODE=false
- [ ] Admin şifresi güçlü
- [ ] Cron job kuruldu ve çalışıyor
- [ ] HTTPS aktif
- [ ] Supabase Realtime aktif
- [ ] Twitch OAuth callback URL doğru
- [ ] File permissions doğru (cache: 755)
- [ ] CSS/JS minified versiyonlar kullanılıyor
- [ ] Database indeksler oluşturuldu

### Performans:

- Hedef: < 500ms API response
- Cache kullan (2s TTL)
- Database indeksler kritik
- CDN kullan (isteğe bağlı - CloudFlare)

### Backup:

- Günlük database backup (Supabase otomatik)
- .env dosyası yedekle (güvenli lokasyon)

---

## 📖 ÖRNEK KULLANIM SENARYOSU

### Yayıncı Akışı:

1. Twitch ile giriş yap
2. Bakiye yükle (50 TL)
3. Ayarları yap:
   - Ödül: 0.10 TL (sabit)
   - Countdown: 5 saniye
   - Duration: 30 saniye
   - Interval: 300 saniye (5 dakika)
   - Tema: Valorant
   - Ses: Epic (kod), Drum (countdown)
4. OBS overlay linkini kopyala
5. OBS'de Browser Source ekle
6. Yayına başla
7. 5 dakikada bir otomatik kod çıkar

### İzleyici Akışı:

1. Twitch ile giriş yap
2. Yayını izle
3. Kod ekranda göründüğünde:
   - 5 saniye hazırlık (countdown)
   - Kod belirir (30 saniye geçerli)
   - rumb.net'e git, kodu gir
   - 0.10 TL kazan
4. Bakiye 5 TL olunca ödeme talep et
5. Admin onaylar, para hesaba gelir

---

## 🎁 EK ÖZELLİKLER (İsteğe Bağlı)

### 1. Redis Cache (Gelişmiş)

- File cache yerine Redis kullan
- Daha hızlı, daha ölçeklenebilir

### 2. CDN Entegrasyonu

- CloudFlare ile CSS/JS/image'lar
- Global performans artışı

### 3. Email Bildirimleri

- Ödeme talebi onaylandığında
- Bakiye yükleme onaylandığında
- PHPMailer veya SendGrid

### 4. Analytics

- Google Analytics entegrasyonu
- Kullanıcı davranış analizi

### 5. WebSocket (Alternatif)

- Socket.io kullan
- Daha fazla kontrol

---

## ✅ PROJE TAMAMLANDI!

Bu prompt ile başka bir AI, **aynı sistemi tam olarak yeniden oluşturabilir**. Tüm teknik detaylar, iş mantığı kuralları, veritabanı yapısı, API'ler ve özellikler eksiksiz şekilde belirtilmiştir.

**Önemli:** Tüm hassas bilgiler (API key'ler, şifreler) `.env` dosyasında saklanmalı ve asla git'e commit edilmemelidir.

---

## 📋 ÖNCELİKLİ KONTROL LİSTESİ (İmplementasyon Sırası)

### Adım 1: Database Kurulumu

```sql
1. ✅ Supabase projesi oluştur
2. ✅ Tüm tabloları oluştur (MUTLAKA twitch_display_name, overlay_theme, is_bonus_code ekle!)
3. ✅ Index'leri oluştur
4. ✅ Realtime'ı etkinleştir (codes tablosu)
```

### Adım 2: Timezone ve Config

```php
1. ✅ MUTLAKA UTC kullan! (new DateTime('now', new DateTimeZone('UTC')))
2. ✅ .env dosyasını doğru yapılandır
3. ✅ TIMEZONE constant'ını tanımla
```

### Adım 3: Database Class

```php
1. ✅ query() metodu ekle (string conditions için)
2. ✅ getActiveCode() metodunda elapsed time kontrolü
3. ✅ String ve array conditions desteği
```

### Adım 4: Overlay

```php
1. ✅ Başlangıçta gizli (opacity: 0, visibility: hidden)
2. ✅ F5 devam etme özelliği (checkForCode ile)
3. ✅ Realtime bağlantı fallback (polling)
4. ✅ Kod bitince gizlenme
```

### Adım 5: Admin Panel

```php
1. ✅ Aktif kod kontrolü (peş peşe kod önleme)
2. ✅ Bonus kod sistemi (is_bonus_code = true)
3. ✅ UTC kullanımı
```

### Adım 6: Test

```bash
1. ✅ Kod gönder → overlay'de gözüküyor mu?
2. ✅ F5 at → kaldığı yerden devam ediyor mu?
3. ✅ Kod bittikten sonra → overlay gizleniyor mu?
4. ✅ Peş peşe kod → hata mesajı veriyor mu?
5. ✅ Cache temizle → yeni değerler alınıyor mu?
```

---

## 🎓 YAYIN SORUNLAR VE ÇÖZÜMLER ÖZETİ

### Sorun 1: Overlay'de Kod Görünmüyor

**Sebep:** Supabase Realtime açık değil
**Çözüm:** Dashboard → Replication → codes tablosunu ekle

### Sorun 2: Zaman Farkı (Negatif Elapsed)

**Sebep:** Istanbul timezone kullanıp UTC olarak işaretleme
**Çözüm:** `new DateTime('now', new DateTimeZone('UTC'))`

### Sorun 3: F5'te Kod Kayboluyor

**Sebep:** Sayfa yenilendiğinde aktif kod kontrolü yok
**Çözüm:** `checkForCode()` ile elapsed time hesapla

### Sorun 4: Overlay Sürekli Görünür

**Sebep:** Başlangıçta visible
**Çözüm:** `opacity: 0, visibility: hidden` + kod gelince `.visible` ekle

### Sorun 5: Peş Peşe Kod Çakışması

**Sebep:** Aktif kod kontrolü yok
**Çözüm:** `getActiveCode()` kontrolü ekle

### Sorun 6: "Column does not exist" Hatası

**Sebep:** Migration yapılmamış
**Çözüm:** `ALTER TABLE` ile eksik kolonları ekle

### Sorun 7: Admin Kodu Bakiye Düşüyor

**Sebep:** `is_bonus_code` kontrolü yok
**Çözüm:** `if (!$code['is_bonus_code'])` ekle

### Sorun 8: Kod Süre İçinde Kabul Edilmiyor

**Sebep:** `expires_at` kontrolü yanlış, timezone ve duration eksik
**Çözüm:** UTC kullan + `timeSinceCreated >= (countdown + duration)` kontrolü

### Sorun 9: Kodlar 1-2 Dakika Gecikmeli Üretiliyor

**Sebep:** Cron timing problemi (59. saniyede çalışırsa 60. saniyedeki kod kaçar)
**Çözüm:** 45 saniye tolerans ekle: `modify('+45 seconds')`

---

## 📞 DESTEK VE KAYNAKLAR

- Supabase Docs: https://supabase.com/docs
- Twitch API Docs: https://dev.twitch.tv/docs/api
- Web Audio API: https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API
- PHP cURL: https://www.php.net/manual/en/book.curl.php
- DateTime Timezone: https://www.php.net/manual/en/class.datetimezone.php

**Son Güncelleme:** Ekim 2025
**Versiyon:** 6.2 (Smart Countdown Sound)
**Changelog:**

- ✅ Timezone hatası düzeltildi (UTC zorunlu - tüm DateTime işlemleri)
- ✅ F5 kaldığı yerden devam eklendi (resume functionality)
- ✅ Overlay başlangıç gizleme eklendi
- ✅ Aktif kod kontrolü eklendi (duplicate prevention)
- ✅ Bonus kod sistemi eklendi (admin codes, no balance deduction)
- ✅ Database query string desteği eklendi
- ✅ Supabase Realtime kurulum dokümantasyonu eklendi
- ✅ Profesyonel limitler eklendi (Duration: 1 saat, Interval: 1 gün)
- ✅ Kod giriş süre kontrolü düzeltildi (UTC + countdown + duration)
- ✅ Cron timing toleransı eklendi (45s) - 1 dakika gecikme sorunu çözüldü
- ✅ Kullanıcı bilgilendirme sistemi eklendi - Gerçek zamanlı boş bekleme süresi hesaplama
- ✅ **KRİTİK BUG FIX:** Cron cleanup tolerans hatası düzeltildi - Yeni kodlar artık expire olmuyor
- ✅ **KRİTİK BUG FIX:** getActiveCode() timezone hatası düzeltildi - F5'te kod kaybolma sorunu çözüldü
- ✅ **YENİ ÖZELLİK:** Ses kontrol sistemi eklendi - 10 kod sesi + 10 geri sayım sesi
- ✅ Kullanıcı bazında ses açma/kapama ve ses seçimi
- ✅ Geri sayım sesi her saniyede çalacak şekilde güncellendi
- ✅ **YENİ ÖZELLİK:** Granüler ses kontrolü - Her ses türü için ayrı toggle (kod sesi/geri sayım sesi bağımsız)
- ✅ **YENİ ÖZELLİK:** Akıllı geri sayım sesi - "Son kaç saniyede ses çalsın" ayarı eklendi (0-300s)
