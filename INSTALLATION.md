# 🚀 Installation Guide - Rumb

Twitch Code Reward System kurulum rehberi.

## 📋 Gereksinimler

### Hosting

- ✅ PHP 7.4 veya üzeri
- ✅ cURL extension enabled
- ✅ Session support
- ✅ HTTPS (SSL sertifikası)
- ✅ Cron job erişimi

### Dış Servisler

- ✅ Supabase hesabı (ücretsiz tier yeterli)
- ✅ Twitch Developer hesabı

## 🔧 Adım Adım Kurulum

### 1. Supabase Kurulumu

#### a) Yeni Proje Oluştur

1. https://supabase.com adresine git
2. "New Project" oluştur
3. Proje adı: `rumb-code-reward`
4. Database password belirle (güvenli tut!)
5. Region seç (en yakın)

#### b) Database Schema Yükle

1. Supabase Dashboard → SQL Editor
2. `database/schema.sql` dosyasını aç
3. Tüm SQL kodunu kopyala
4. SQL Editor'e yapıştır
5. "RUN" butonuna tıkla
6. Başarılı mesajını bekle

#### c) Realtime'ı Aktifleştir

1. Database → Replication
2. "codes" tablosunu bul
3. Toggle switch'i açık yap (enable)
4. Kaydet

#### d) API Credentials'ı Al

1. Settings → API
2. Şunları kopyala:
   - **URL:** `https://xxx.supabase.co`
   - **anon public:** `eyJhbGc...`
   - **service_role:** `eyJhbGc...` (Show secret'a tıkla)

### 2. Twitch Developer Setup

#### a) Uygulama Oluştur

1. https://dev.twitch.tv/console adresine git
2. "Register Your Application"
3. Form doldur:
   - **Name:** Rumb Code Reward
   - **OAuth Redirect URLs:** `https://YOURDOMAIN.com/callback.php`
   - **Category:** Website Integration
4. "Create" butonuna tıkla

#### b) Credentials'ı Al

1. "Manage" butonuna tıkla
2. Şunları kopyala:
   - **Client ID:** `6to5oqt...`
   - **Client Secret:** "New Secret" → kopyala

### 3. Hosting Upload

#### a) Dosyaları Yükle

1. cPanel File Manager aç
2. `public_html` dizinine git
3. Tüm proje dosyalarını upload et
4. Sıkıştırılmış ise extract et

#### b) .env Dosyası Oluştur

1. `.env.example` dosyasını kopyala
2. Adını `.env` olarak değiştir
3. Editör ile aç
4. Bilgileri doldur:

```bash
# Supabase (Adım 1d'den)
SUPABASE_URL=https://qffaddlbrmqogchzplah.supabase.co
SUPABASE_ANON_KEY=eyJhbGc...
SUPABASE_SERVICE_KEY=eyJhbGc...

# Twitch (Adım 2b'den)
TWITCH_CLIENT_ID=6to5oqt...
TWITCH_CLIENT_SECRET=mywlkx...
TWITCH_REDIRECT_URI=https://YOURDOMAIN.com/callback.php

# Admin
ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=$2y$10$...

# App
APP_URL=https://YOURDOMAIN.com
SESSION_LIFETIME=3600
DEBUG_MODE=false

# Cron
CRON_SECRET_KEY=RANDOM_STRONG_KEY_HERE

# Cache
CACHE_ENABLED=true
CACHE_TTL=2
TIMEZONE=Europe/Istanbul
```

#### c) Admin Şifre Hash'i Oluştur

SSH veya Terminal'de:

```bash
php -r "echo password_hash('YourSecurePassword123', PASSWORD_BCRYPT);"
```

Çıktıyı kopyala ve `.env` dosyasında `ADMIN_PASSWORD_HASH` değerine yapıştır.

#### d) File Permissions Ayarla

cPanel File Manager'da:

```
cache/        → 755
.env          → 600
cron.php      → 644
```

### 4. Cron Job Kurulumu

#### a) cPanel Cron Jobs

1. cPanel → Cron Jobs
2. "Add New Cron Job"
3. Frequency: `* * * * *` (her dakika)
4. Command:

```bash
/usr/bin/php /home/username/public_html/cron.php?key=YOUR_SECRET_KEY
```

**Not:** `YOUR_SECRET_KEY` yerine `.env` dosyasındaki `CRON_SECRET_KEY` değerini yaz.

#### b) Cron-Job.org (Alternatif)

Eğer cPanel erişiminiz yoksa:

1. https://cron-job.org adresine git
2. Yeni cron job oluştur
3. URL: `https://YOURDOMAIN.com/cron.php?key=YOUR_SECRET_KEY`
4. Interval: Every 1 minute
5. Aktifleştir

### 5. Test Etme

#### a) OAuth Test

1. `https://YOURDOMAIN.com` adresine git
2. "Twitch ile Giriş Yap" butonuna tıkla
3. Twitch'e yönlendirilmeli
4. Authorize et
5. Dashboard'a dönmeli

#### b) Admin Panel Test

1. `https://YOURDOMAIN.com/admin/login.php` git
2. Username/Password gir
3. Dashboard görmelisin

#### c) Overlay Test

1. Dashboard → Yayıncı sekmesi
2. Overlay linkini kopyala
3. Yeni tarayıcı sekmesinde aç
4. Debug panel görmelisin (DEBUG_MODE=true ise)

#### d) Cron Test

1. Terminal/SSH:

```bash
php cron.php?key=YOUR_SECRET_KEY
```

2. Response kontrolü:

```json
{ "success": true, "codes_generated": 0, "duration_ms": 123, "timestamp": "..." }
```

### 6. Production Hazırlık

#### a) .env Güncelle

```bash
DEBUG_MODE=false
```

#### b) Admin Şifresini Değiştir

Güçlü bir şifre hash'i oluştur ve `.env` dosyasını güncelle.

#### c) HTTPS Kontrolü

SSL sertifikası aktif mi kontrol et. Twitch OAuth HTTPS gerektirir.

#### d) Realtime Kontrolü

Supabase Dashboard → Database → Replication → "codes" tablosu enabled mi?

## ✅ Kurulum Tamamlandı!

### İlk Kullanım

#### Yayıncı Olarak:

1. Giriş yap
2. Yayıncı sekmesine geç
3. Bakiye yükleme talebi oluştur
4. Admin panelden onayla
5. Ayarları yap (ödül, tema, ses)
6. OBS overlay linkini kopyala
7. OBS → Browser Source → Link ekle (1920x1080)
8. Yayına başla!

#### İzleyici Olarak:

1. Giriş yap
2. Yayıncı izle
3. Ekranda kod çıkınca gir
4. Kazandığın parayı gör
5. 5 TL'ye ulaşınca ödeme talep et

## 🐛 Sorun Giderme

### OAuth Hatası

- Twitch redirect URI kontrolü yap
- HTTPS aktif mi kontrol et
- Client ID/Secret doğru mu?

### Realtime Çalışmıyor

- Supabase'de Replication enabled mi?
- Browser console'da WebSocket hatası var mı?
- Fallback polling çalışıyor mu?

### Cron Çalışmıyor

- `cron.php` dosyası 644 permission'a sahip mi?
- Secret key doğru mu?
- PHP path doğru mu? (`which php` ile kontrol et)

### Cache Problemi

- `cache/` dizini var mı?
- 755 permission var mı?
- `.gitkeep` dosyası var mı?

## 📞 Destek

Sorun yaşarsanız:

1. `error.log` dosyasını kontrol edin
2. Browser console'u kontrol edin
3. Supabase logs'u kontrol edin
4. README.md'yi okuyun

---

**Kurulum tamamlandı!** 🎉 Artık sistemi kullanabilirsiniz.
