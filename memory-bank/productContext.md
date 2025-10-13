# Product Context: Rumb

## Neden Var?

Twitch yayıncılarının izleyicilerle etkileşimi artırmak ve izleyicilere maddi ödül vermek için bir platform.

## Çözülen Problem

1. **Yayıncılar için:** Manuel kod dağıtımı, izleyici etkileşimi eksikliği
2. **İzleyiciler için:** Yayın izlerken kazanç fırsatı
3. **Platform için:** Otomatik, ölçeklenebilir ödül sistemi

## Ana Kullanım Senaryoları

### Senaryo 1: İzleyici Olarak Kod Girme

1. Kullanıcı Twitch ile giriş yapar
2. Sistemdeki bir yayıncıyı izler
3. OBS overlay'de kod belirir
4. Countdown sonrası kod aktif olur
5. Kod girişi yapar
6. Anında ödül kazanır
7. Bakiye 5 TL'ye ulaşınca ödeme talep eder

### Senaryo 2: Yayıncı Olarak Sistem Kurulumu

1. Twitch ile giriş yapar
2. Bakiye yükleme talebi oluşturur (dekont ile)
3. Admin onaylar, bakiye yüklenir
4. Ayarları yapar:
   - Ödül miktarı: 0.10 TL
   - Kod süresi: 30s
   - Kod aralığı: 10 dakika
   - Tema: Valorant
   - Ses: threeTone
5. OBS overlay linkini kopyalar
6. OBS'e Browser Source olarak ekler
7. Yayına başlar
8. Sistem otomatik kod üretir (cron job)
9. Kodlar Realtime ile overlay'e gelir
10. İzleyiciler kodları girer, yayıncı bakiyesi azalır

### Senaryo 3: Bütçe Hesaplama

1. Yayıncı bütçe hesaplama aracını açar
2. Bilgileri girer:
   - Toplam bütçe: 100 TL
   - Yayın süresi: 3 saat
   - İzleyici sayısı: 50
   - Katılım oranı: %30
3. Sistem optimal ayarları hesaplar
4. Önerilen ayarları tek tıkla uygular

## Kullanıcı Deneyimi Hedefleri

- **Hız:** Kod girişi ve ödül 2 saniye içinde
- **Sadelik:** 3 tıkla kurulum (yayıncı için)
- **Güvenilirlik:** %99 uptime, Realtime bağlantı
- **Şeffaflık:** Her işlem görünür, bakiye anlık

## Başarı Metrikleri

- Kod giriş sayısı
- Aktif yayıncı sayısı
- Dağıtılan toplam ödül
- Ortalama katılım oranı
- Realtime bağlantı başarı oranı
