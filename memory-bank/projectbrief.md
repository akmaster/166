# Project Brief: Twitch Code Reward System (Rumb)

## Proje Özeti

Twitch yayıncıları için otomatik kod ödül sistemi. İzleyiciler yayında gösterilen 6 haneli kodları girerek para kazanırlar. Sistem tamamen otomatik çalışır.

## Temel Özellikler

### İzleyici Tarafı

- Twitch OAuth ile giriş
- 6 haneli kodları girme
- Anında ödül kazanma
- Bakiye takibi ve ödeme talebi
- İşlem geçmişi

### Yayıncı Tarafı

- Bakiye yükleme sistemi
- OBS overlay (kişiye özel token)
- Kod ayarları (countdown, duration, interval)
- Ödül miktarı (sabit/rastgele)
- 20 tema seçeneği
- 20 ses efekti (10 kod + 10 countdown)
- Bütçe hesaplama aracı

### Teknik Altyapı

- **Backend:** PHP 7.4+
- **Database:** Supabase (PostgreSQL + Realtime)
- **Frontend:** HTML5, CSS3, Vanilla JS
- **Hosting:** Shared hosting (cPanel uyumlu)
- **Automation:** Cron job (her dakika)

## Önemli Kısıtlamalar

- Shared hosting desteği (cPanel)
- File-based cache (Redis yok)
- Supabase Realtime kullanımı zorunlu
- Ses dosyası yok (Web Audio API ile prosedürel)
- Admin paneli basit (session-based)

## Varsayılan Değerler

- Countdown: 5 saniye
- Kod süresi: 30 saniye
- Kod aralığı: 600 saniye (10 dakika)
- Ödül: 0.10 TL
- Minimum ödeme: 5.00 TL

## Supabase Yapısı

- 6 tablo: users, codes, submissions, payout_requests, balance_topups, settings
- Realtime: codes tablosu için aktif
- REST API kullanımı (PHP cURL)

## Güvenlik

- Twitch OAuth 2.0
- .env dosyası ile credential yönetimi
- Session security (HttpOnly, Secure)
- Input sanitization
- Rate limiting (kod başına 1 giriş)
- Cron job authentication
