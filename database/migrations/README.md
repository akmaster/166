# Database Migrations

Bu klasör veritabanı güncellemeleri için migration scriptlerini içerir.

## Nasıl Uygulanır?

1. Supabase Dashboard → SQL Editor'e git
2. İlgili migration dosyasını aç
3. SQL kodunu kopyala
4. SQL Editor'e yapıştır
5. "RUN" butonuna tıkla

## Migrations Listesi

### 1. `add_is_bonus_code.sql` (2025-01-11)

**Amaç:** Admin panelinden gönderilen bonus kodları için flag ekleme

**Ne yapar:**

- `codes` tablosuna `is_bonus_code` boolean kolonu ekler
- Default değer: `FALSE`
- Admin panelinden gönderilen kodlar `TRUE` olarak işaretlenir
- Bonus kodlar kullanıldığında yayıncı bakiyesi düşmez

**Çalıştırma:**

```sql
-- Dosya içeriğini kopyalayın ve SQL Editor'de çalıştırın
```

## Notlar

- Her migration bir kere çalıştırılmalıdır
- Migration'lar sıralı olarak uygulanmalıdır
- `IF NOT EXISTS` kullanıldığı için tekrar çalıştırmak güvenlidir
- Hata durumunda lütfen migration'ı geri alın (rollback)
