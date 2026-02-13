# Laravel 12 Upgrade Checklist (PHP 8.3)

## Guncel Durum (2026-02-11)

- [x] Laravel 12 + PHP 8.3 gecisi tamamlandi
- [x] Blade -> Vue SPA gecisi aktif (`routes/web.php` API + SPA fallback)
- [x] Super admin site yonetimi (listele/ekle/duzenle/sil) SPA uzerinde aktif
- [x] Site silmede bagli kayit temizligi duzeltildi (`SitePurger`)
- [x] Raporlar modulu SPA/API’ye tasindi (JSON + PDF endpointleri)
- [x] Sol menu, ust menu ve arama yetkiye gore filtreleniyor
- [x] Legacy route/controller temizligi (web_legacy + eski report/super-site blade controllerlari kaldirildi)
- [ ] Legacy blade view temizligi (kalan eski blade sayfalari gerekirse tamamen silinecek)

## 1) Environment

- PHP target: `8.3.x`
- Node target (current stack): `>=18`
- Database backup al

## 2) Composer Update

Bu repo icinde su komutu calistir:

```bash
composer update --with-all-dependencies
```

Ardindan:

```bash
php artisan optimize:clear
php artisan package:discover
php artisan test
```

## 3) Frontend

Mevcut frontend zinciri korunuyor:

- `laravel-vite-plugin` `^1`
- `vite` `^6`
- `tailwindcss` `^3`

Kontrol:

```bash
npm install
npm run build
```

## 4) Manual Smoke Test

- Login / logout
- Dashboard
- Charges / Receipts / Expenses CRUD
- Super admin `Siteler` ekrani
- Site silme (bagli kayit temizligi)
- PDF olusturma
- Raporlar ekrani (`/reports`) + PDF indirme
- Rol bazli menu gorunurlugu (owner/tenant/admin/super-admin)

## 5) Optional Next Step

Laravel 12 default frontend stack'e gecilecekse ayri bir adimda yap:

- `laravel-vite-plugin` `^2`
- `vite` `^7`
- `tailwindcss` `^4`

Bu adim CSS tarafinda ekstra migration gerektirir.
