# 🚀 Hızlı Başlangıç - Test için Kullanıcı Oluşturma

## Problem: Dropdown'da Yayıncı Görünmüyor

Eğer admin panelinde "Manuel Kod Gönder" modal'ında yayıncı listesi boş ise, sistemde henüz kullanıcı kayıtlı değil demektir.

## Çözüm 1: Twitch OAuth ile Giriş (Önerilen)

1. Ana sayfaya git: `https://rumb.net`
2. **"Twitch ile Giriş Yap"** butonuna tıkla
3. Twitch'e yönlendirileceksin
4. İzin ver
5. Dashboard'a döneceksin
6. ✅ Artık kullanıcı sisteme kaydedildi!

## Çözüm 2: Manuel Test Kullanıcısı Oluşturma (Geliştirme)

Eğer Twitch OAuth henüz çalışmıyorsa, Supabase'de manuel test kullanıcısı oluşturabilirsiniz:

### Supabase Dashboard → SQL Editor:

```sql
-- Test yayıncısı oluştur
INSERT INTO users (
    twitch_user_id,
    twitch_username,
    twitch_display_name,
    twitch_email,
    twitch_avatar,
    streamer_balance,
    overlay_token,
    created_at
) VALUES (
    '123456789',
    'test_streamer',
    'Test Yayıncı',
    'test@example.com',
    'https://static-cdn.jtvnw.net/jtv_user_pictures/default-profile_image-300x300.png',
    100.00,
    encode(gen_random_bytes(32), 'hex'),
    NOW()
);

-- Test izleyicisi oluştur
INSERT INTO users (
    twitch_user_id,
    twitch_username,
    twitch_display_name,
    twitch_email,
    twitch_avatar,
    created_at
) VALUES (
    '987654321',
    'test_viewer',
    'Test İzleyici',
    'viewer@example.com',
    'https://static-cdn.jtvnw.net/jtv_user_pictures/default-profile_image-300x300.png',
    NOW()
);
```

### Kontrol:

```sql
-- Kullanıcıları listele
SELECT
    twitch_display_name,
    twitch_username,
    streamer_balance,
    created_at
FROM users
ORDER BY created_at DESC;
```

## Çözüm 3: Mevcut Kullanıcıları Kontrol Et

Belki kullanıcılar var ama farklı bir sorun vardır:

### Supabase Dashboard → SQL Editor:

```sql
-- Tüm kullanıcıları göster
SELECT * FROM users LIMIT 10;

-- Kullanıcı sayısını göster
SELECT COUNT(*) as total_users FROM users;

-- Yayıncı sayısını göster (bakiyesi olan)
SELECT COUNT(*) as streamers FROM users WHERE streamer_balance > 0;
```

## Test Akışı

### 1. Kullanıcı Oluşturulduktan Sonra:

1. **Admin Panel** → **Kodlar** sayfasına git
2. **"⚡ Manuel Kod Gönder"** butonuna tıkla
3. Dropdown'da "Test Yayıncı" görünmeli
4. Seç ve kod gönder

### 2. Overlay Test:

```
Overlay URL: https://rumb.net/overlay/index.php?token=OVERLAY_TOKEN
```

Token'ı almak için:

```sql
SELECT
    twitch_display_name,
    overlay_token
FROM users
WHERE twitch_username = 'test_streamer';
```

### 3. Kod Girişi Test:

1. Ana sayfa → "Test İzleyici" olarak giriş yap
2. Dashboard'da kod giriş alanı
3. Admin'den gönderilen kodu gir
4. ✅ Kazanç görünmeli!

## Sorun Giderme

### "No streamers found" Log'u

Eğer `error.log` dosyasında bu mesajı görüyorsan:

```bash
# PHP error log kontrol et
tail -f /path/to/error.log
```

### Database Bağlantı Kontrolü

```sql
-- Supabase'de test query
SELECT NOW() as current_time;
```

### Debug Mode Aktif Mi?

`.env` dosyasında:

```bash
DEBUG_MODE=true
```

Sonra log'ları kontrol et.

## Hızlı Reset (Temiz Başlangıç)

Eğer her şeyi sıfırlamak istersen:

```sql
-- DİKKAT: Bu tüm verileri siler!
TRUNCATE TABLE submissions CASCADE;
TRUNCATE TABLE codes CASCADE;
TRUNCATE TABLE payout_requests CASCADE;
TRUNCATE TABLE balance_topups CASCADE;
TRUNCATE TABLE users CASCADE;

-- Sonra yukarıdaki INSERT komutlarını tekrar çalıştır
```

## İletişim

Sorun devam ederse:

1. Browser Console'u aç (F12)
2. Network tab'ı kontrol et
3. Hata mesajlarını kopyala
4. Log dosyalarını kontrol et
