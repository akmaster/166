# FILE STRUCTURE - Complete Project Layout

## 📁 FULL DIRECTORY TREE

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
├── 📄 prompt.md                    # Tam proje prompt
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
└── 📂 full-prompt/                 # Ultra detaylı prompt dosyaları
    ├── README.md                   # Prompt kullanım rehberi
    ├── 01-overview.md              # Proje özeti
    ├── 02-file-structure.md        # Bu dosya
    ├── 03-database.md              # Database detayları
    ├── 04-components.md            # Component detayları
    ├── 05-api-endpoints.md         # API detayları
    ├── 06-sound-system.md          # Ses sistemi
    ├── 07-overlay-themes.md        # Overlay temaları
    ├── 08-installation.md          # Kurulum
    ├── 09-helpers.md               # Helper fonksiyonlar
    └── MASTER.md                   # HEPSİNİ İÇERİR!
```

## 📦 DOSYA İSTATİSTİKLERİ

| Kategori             | Adet | Açıklama                            |
| -------------------- | ---- | ----------------------------------- |
| **PHP Files**        | ~45  | Backend logic, API endpoints, pages |
| **JavaScript Files** | ~12  | Components, overlay, main.js        |
| **CSS Files**        | ~12  | Styles, themes, components          |
| **SQL Files**        | 4    | Schema + 3 migrations               |
| **Config Files**     | 1    | .env                                |
| **Documentation**    | 14   | README, INSTALLATION, prompts, etc  |
| **TOPLAM**           | ~88  | files                               |

## 🔑 KRİTİK DOSYALAR (Öncelik Sırasıyla)

### 1. `.env` (EN KRITIK!)

**Neden:** Tüm hassas bilgiler burada  
**İçerik:** Supabase URL/Keys, Twitch credentials, Admin password  
**⚠️ ASLA GIT'E EKLEME!**

### 2. `config/database.php`

**Neden:** Supabase ile tüm iletişim buradan  
**İçerik:** Database class (select, insert, update, delete, REST API wrapper)

### 3. `database/schema.sql`

**Neden:** İlk kurulumda çalıştırılmalı  
**İçerik:** 6 tablo (users, codes, submissions, payout_requests, balance_topups, settings)

### 4. `cron.php`

**Neden:** Otomatik kod üretimi  
**Nasıl:** Cron job - her 1 dakikada çalışmalı  
**URL:** `https://yourdomain.com/cron.php?secret=YOUR_CRON_SECRET`

### 5. `overlay/index.php`

**Neden:** OBS tarafından yüklenir  
**Nasıl:** Token bazlı erişim  
**URL:** `https://yourdomain.com/overlay/?token=UNIQUE_TOKEN`

## 🎨 COMPONENT YAPISI

Her component aynı pattern'i izler:

```
ComponentName/
├── ComponentName.php       # HTML structure (PHP ile data binding)
├── ComponentName.js        # Logic (API calls, events, validation)
├── ComponentName.css       # Styles (gradient design, animations)
└── ComponentName.min.*     # Minified versions
```

**Ortak Özellikler:**

- ✅ Cache busting (`?v=<?php echo ASSET_VERSION; ?>`)
- ✅ Session-aware (kullanıcı datası)
- ✅ Form validation (client + server)
- ✅ Status messages (success/error/warning)
- ✅ Responsive design

## 📂 KLASÖR SORUMLULUK

| Klasör         | Sorumluluğu                   | Kimin Kullandığı   |
| -------------- | ----------------------------- | ------------------ |
| `/`            | Landing, Dashboard, Streamers | Herkes             |
| `/api/`        | JSON endpoints                | Frontend (AJAX)    |
| `/admin/`      | Admin panel                   | Sadece admin       |
| `/overlay/`    | OBS overlay                   | OBS Browser Source |
| `/components/` | Reusable UI                   | Dashboard          |
| `/config/`     | Configuration, helpers        | Tüm backend        |
| `/database/`   | Schema, migrations            | İlk kurulum        |
| `/assets/`     | Global CSS/JS                 | Tüm sayfalar       |
| `/cache/`      | File cache                    | Sistem (otomatik)  |

## 🔒 GÜVENLİK YAPISI

```
Public Access (Herkes):
├── index.php (landing)
├── streamers.php
├── callback.php (OAuth)
└── /api/* (JSON endpoints - auth gerektirir)

Authenticated Access (Giriş yapmış):
├── index.php (dashboard)
└── /api/update-* (kullanıcı kendi datası)

Admin Only:
├── /admin/* (tüm sayfalar)
└── /api/admin/* (admin endpoints)

Token Based:
└── /overlay/?token=xxx (overlay)

Cron Job:
└── cron.php?secret=xxx (otomatik kod)
```

## 📋 DEPLOYMENT YAPISI

### Development:

```
/htdocs/
├── .env (DEBUG_MODE=true)
├── cache/ (777 permissions)
└── [tüm dosyalar]
```

### Production:

```
/public_html/
├── .env (DEBUG_MODE=false)
├── cache/ (755 permissions)
├── .htaccess (optional - redirects)
└── [tüm dosyalar]
```

### Cron Setup:

```bash
# cron-job.org veya cPanel cron
*/1 * * * * curl "https://yourdomain.com/cron.php?secret=YOUR_SECRET"
```

---

**Next:** `03-database.md` → Database schema ve migrationlar
