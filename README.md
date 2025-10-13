# 🎮 Rumb - Twitch Code Reward System

Twitch yayıncıları için otomatik kod ödül sistemi. İzleyiciler yayında gösterilen kodları girerek para kazanırlar. Sistem tamamen otomatik çalışır ve Supabase Realtime ile gerçek zamanlı kod gösterimi yapar.

## 📋 Özellikler

### İzleyici İçin

- ✅ Twitch OAuth ile güvenli giriş
- ✅ Yayında gözüken kodları girme
- ✅ Anında ödül kazanma
- ✅ Bakiye takibi
- ✅ Minimum eşiğe ulaşınca ödeme talebi
- ✅ Mobil uyumlu arayüz

### Yayıncı İçin

- ✅ Bakiye yükleme sistemi
- ✅ OBS overlay linki (kişiye özel token)
- ✅ Kod ayarları (countdown, duration, interval)
- ✅ Ödül miktarı belirleme (sabit/rastgele)
- ✅ 20+ overlay teması
- ✅ Ses sistemi (10 kod + 10 countdown sesi)
- ✅ Bütçe hesaplama aracı
- ✅ İstatistikler

### Teknik Özellikler

- ⚡ Supabase Realtime (anında kod gösterimi)
- 🎨 3D card flip animasyonu
- 🔊 Web Audio API (prosedürel sesler)
- 📱 Responsive tasarım
- 🔒 Güvenli (Twitch OAuth 2.0)
- 🚀 Shared hosting uyumlu

## 🛠️ Teknoloji Stack

**Backend:**

- PHP 7.4+
- Supabase (PostgreSQL + Realtime)
- cURL (API requests)

**Frontend:**

- HTML5, CSS3, Vanilla JavaScript
- Supabase JS Client Library
- Web Audio API

**Entegrasyonlar:**

- Twitch OAuth 2.0
- Twitch API
- Supabase REST API
- Supabase Realtime (WebSocket)

## 📦 Kurulum

### 1. Supabase Kurulumu

```sql
-- database/schema.sql dosyasını Supabase SQL Editor'de çalıştırın
-- Bu dosya tüm tabloları, indeksleri ve varsayılan ayarları oluşturur
```

**Supabase Dashboard'da:**

1. Yeni proje oluşturun
2. SQL Editor → schema.sql dosyasını çalıştırın
3. Database → Replication → "codes" tablosunu enable edin (Realtime için)
4. Settings → API → URL, Anon Key, Service Key'i kopyalayın

### 2. Twitch Developer Setup

1. https://dev.twitch.tv/console adresine gidin
2. "Register Your Application"
3. **Name:** Rumb Code Reward
4. **OAuth Redirect URL:** https://yourdomain.com/callback.php
5. **Category:** Website Integration
6. Client ID ve Secret'i kopyalayın

### 3. Hosting Upload

```bash
# Tüm dosyaları hosting'e yükleyin
# .env dosyasını oluşturun (aşağıdaki adımı takip edin)
```

### 4. .env Konfigürasyonu

`.env.example` dosyasını `.env` olarak kopyalayın ve bilgilerinizi girin:

```bash
# Supabase
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your_anon_key
SUPABASE_SERVICE_KEY=your_service_key

# Twitch
TWITCH_CLIENT_ID=your_client_id
TWITCH_CLIENT_SECRET=your_client_secret
TWITCH_REDIRECT_URI=https://yourdomain.com/callback.php

# Admin (şifre hash oluşturmak için: php -r "echo password_hash('şifreniz', PASSWORD_BCRYPT);")
ADMIN_USERNAME=admin
ADMIN_PASSWORD_HASH=$2y$10$...your_hash

# App
APP_URL=https://yourdomain.com
DEBUG_MODE=false
```

### 5. Cron Job Kurulumu

**cPanel'de:**

```
Komut: * * * * * /usr/bin/php /path/to/cron.php?key=YOUR_SECRET_KEY
Interval: Her 1 dakika
```

**veya cron-job.org kullanın:**

```
URL: https://yourdomain.com/cron.php?key=YOUR_SECRET_KEY
Interval: Her 1 dakika
```

### 6. Dosya İzinleri

```bash
# Cache dizini otomatik oluşur, gerekirse:
chmod 755 cache/

# .env güvenliği
chmod 600 .env
```

## 🚀 Kullanım

### İzleyici Olarak

1. https://rumb.net adresine gidin
2. "Twitch ile Giriş Yap" butonuna tıklayın
3. Sistemdeki bir yayıncıyı izleyin
4. Yayında ekranda gözüken 6 haneli kodu girin
5. Anında para kazanın!
6. Bakiye 5 TL'ye ulaştığında ödeme talep edin

### Yayıncı Olarak

1. Twitch ile giriş yapın
2. **Yayıncı** sekmesine geçin
3. Bakiye yükleyin (dekont ile talep)
4. Ayarlarınızı yapın:
   - Ödül miktarı (sabit veya rastgele)
   - Kod ayarları (countdown, duration, interval)
   - Tema seçimi
   - Ses ayarları
5. OBS overlay linkini kopyalayın
6. OBS → Browser Source → Linki yapıştırın (1920x1080)
7. Yayına başlayın!

## 🎨 Temalar

**Oyun Temaları (10):**
Valorant, League of Legends, CS:GO, Dota 2, PUBG, Fortnite, Apex Legends, Minecraft, GTA V, FIFA

**Renk Temaları (10):**
Neon, Sunset, Ocean, Purple, Cherry, Minimal, Dark, Sakura, Cyber, Arctic

## 🔊 Ses Sistemi

**Kod Gösterim Sesleri (10):**
threeTone, successBell, gameCoin, digitalBlip, powerUp, notification, cheerful, simple, epic, gentle

**Countdown Sesleri (10):**
none, tickTock, digitalBeep, drum, heartbeat, countdown, arcade, tension, robot, lastThree

Tüm sesler Web Audio API ile prosedürel olarak üretilir (ses dosyası gerektirmez).

## 📁 Dosya Yapısı

```
project/
├── config/               # Konfigürasyon dosyaları
│   ├── config.php       # Ana config
│   ├── database.php     # Supabase wrapper
│   └── helpers.php      # Yardımcı fonksiyonlar
├── api/                 # REST API endpoints
├── components/          # Modüler component'ler
├── overlay/            # OBS overlay sistemi
├── assets/             # CSS/JS dosyaları
├── cache/              # File-based cache
├── database/           # SQL schema
├── index.php           # Ana sayfa
├── streamers.php       # Canlı yayıncılar
├── callback.php        # Twitch OAuth callback
├── cron.php            # Otomatik kod üretici
└── .env                # Konfigürasyon (GİZLİ!)
```

## 🔧 Önemli İş Mantığı

### Kod Yaşam Döngüsü

1. **Cron Job** (her dakika): Yayıncılar için yeni kod üretir
2. **Supabase Realtime**: Kod database'e INSERT edilince overlay'e anında iletir
3. **Overlay**: Countdown gösterir → 3D flip → Kod gösterir → Süresi dolunca gizler
4. **İzleyici**: Kodu girer → Validation → Ödül kazanır → Yayıncı bakiyesinden düşer

### Varsayılan Değerler

- Countdown: 5 saniye
- Kod süresi: 30 saniye
- Kod aralığı: 600 saniye (10 dakika)
- Ödül: 0.10 TL
- Minimum ödeme: 5.00 TL

## 🔒 Güvenlik

- ✅ Twitch OAuth 2.0 ile güvenli giriş
- ✅ Environment variables (.env)
- ✅ Session security (HttpOnly, Secure cookies)
- ✅ Input sanitization
- ✅ Cron job authentication
- ✅ Rate limiting (kod başına 1 giriş)

## 📊 Performans

- **Cache sistemi:** 2 saniye TTL (aktif kodlar)
- **Database indeksler:** Tüm kritik sorgular optimize
- **Minified CSS/JS:** Production için
- **Lazy loading:** Görseller için

## 🐛 Hata Ayıklama

`.env` dosyasında:

```bash
DEBUG_MODE=true
```

Bu şunları aktif eder:

- PHP error reporting
- Detaylı API hata mesajları
- Overlay debug panel
- Cron log dosyası (cron.log)

## 📝 API Endpoints

**Kullanıcı API'leri:**

- `POST /api/submit-code.php` - Kod gönder
- `GET /api/get-active-code.php` - Aktif kod al
- `POST /api/request-payout.php` - Ödeme talep et
- `POST /api/update-reward-amount.php` - Ödül miktarı güncelle
- `POST /api/calculate-budget.php` - Bütçe hesapla

**Tam liste için `/api/` dizinine bakın.**

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Push edin (`git push origin feature/AmazingFeature`)
5. Pull Request açın

## 📄 Lisans

Bu proje özel lisans altındadır. Ticari kullanım için izin gereklidir.

## 📞 Destek

- **Dokümentasyon:** Bu README
- **Supabase Docs:** https://supabase.com/docs
- **Twitch API:** https://dev.twitch.tv/docs/api

## 🎯 Versiyon

**v3.8 - Production Ready**

- ✅ Supabase Realtime entegrasyonu
- ✅ 20 tema desteği
- ✅ 20 ses efekti
- ✅ Bütçe hesaplama
- ✅ Rastgele ödül sistemi
- ✅ Mobil uyumlu
- ✅ Production hazır

## 🙏 Teşekkürler

- [Supabase](https://supabase.com) - Backend & Realtime
- [Twitch](https://twitch.tv) - OAuth & API
- [Web Audio API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API) - Ses sistemi

---

**Yapımcı:** Rumb Team  
**Tarih:** Ocak 2025  
**Durum:** Production Ready 🚀
