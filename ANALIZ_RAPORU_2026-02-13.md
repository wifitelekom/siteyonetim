# Kod Analiz Raporu (13 Subat 2026)

## Kapsam ve yontem
- Incelenen alanlar: `app/`, `routes/`, `database/`, `resources/ts/`, `tests/`
- Haric tutulanlar: `public/build/*` (derlenmis cikti), `starter-kit-*` (yardimci sablon icerikleri)
- Yontem: statik kod incelemesi + temel komut dogrulamalari

## Calistirilan dogrulamalar
- `npm run typecheck` -> **basarili**
- `npm run build` -> **basarili**
- `npm run lint` -> **basarisiz** (starter-kit altindaki eksik eklenti nedeniyle)
- `npx eslint resources/ts -c .eslintrc.cjs --ext .ts,.vue` -> **basarisiz** (`221 error`, `110 warning`)
- `php artisan test` -> **calistirilamadi** (`php` komutu bu ortamda yok)

## Bulgular (oncelik sirasiyla)

### 1) Yetki acigi: Dashboard tum rollere acik ve toplu finansal veriyi donduruyor (Yuksek)
- Kanit:
  - `routes/web.php:29` (`/dashboard` endpointi auth+site.scope altinda, ek permission yok)
  - `app/Http/Controllers/Api/DashboardController.php:15` (authorize/policy kontrolu yok)
  - `app/Services/DashboardService.php:21`, `app/Services/DashboardService.php:30`, `app/Services/DashboardService.php:39` (site geneli alacak/gider/nakit ozetleri)
  - `resources/ts/utils/access.ts:9` (`'/'` tum kullanicilara acik)
- Etki: Owner/tenant/vendor rolleri site geneli finansal ozetleri gorebilir.
- Oneri: `dashboard.view` benzeri ayri permission + controller authorize + role bazli veri daraltma.

### 2) Eszamanli islem riski: tahsilat/odeme yaris kosulunda fazla tahsilat/fazla odeme (Yuksek)
- Kanit:
  - `app/Http/Controllers/Api/ChargeController.php:214` ve `app/Http/Controllers/Api/ExpenseController.php:194` (kalan tutar hesaplamasi transaction disi)
  - `app/Services/ReceiptService.php:23`, `app/Services/ReceiptService.php:44`, `app/Services/ReceiptService.php:46`
  - `app/Services/PaymentService.php:22`, `app/Services/PaymentService.php:40`, `app/Services/PaymentService.php:42`
- Etki: Ayni kayda eszamanli isteklerde kalan tutar iki kez tuketilebilir.
- Oneri: Ilgili `charge/expense` satirlarini transaction icinde `lockForUpdate()` ile kilitleyip kalan tutari iceride tekrar dogrulamak.

### 3) Test paketi guncel mimariye uyumsuz (Yuksek)
- Kanit:
  - API route adlari `api.*`: `routes/web.php:31`, `routes/web.php:39`, `routes/web.php:46` vb.
  - Testler eski web route adlarini kullaniyor: `tests/Feature/ChargeTest.php:72`, `tests/Feature/ExpenseTest.php:54`, `tests/Feature/AuthorizationTest.php:101` vb.
- Etki: Testler gercek uretim akisina dogrulamiyor; yanlis guven hissi yaratiyor.
- Oneri: Feature testlerini JSON API sozlesmesine (`/api/v1/...`, `api.*` route isimleri, JSON assertion) gore bastan hizalamak.

### 4) Performans: bakiye hesaplamasinda N+1 sorgu paterni (Orta)
- Kanit:
  - `app/Models/CashAccount.php:42`, `app/Models/CashAccount.php:43` (accessor icinde iki ayri SUM)
  - Bu accessor yogun kullaniliyor: `app/Services/DashboardService.php:44`, `app/Http/Controllers/Api/CashAccountController.php:160`, `app/Http/Controllers/Api/ChargeController.php:170`, `app/Http/Controllers/Api/ExpenseController.php:151`
- Etki: Hesap sayisi arttikca sorgu sayisi dogrusal artiyor.
- Oneri: Toplu agregasyon (`group by cash_account_id`) veya materialized balance yaklasimi.

### 5) Aylik tahakkuk uretiminde yaris kosulu + veritabani benzersizlik eksikligi (Orta)
- Kanit:
  - `app/Services/TemplateService.php:51` (`exists`), `app/Services/TemplateService.php:54` (`create`)
  - `app/Services/ChargeService.php:37` (`exists` kontrolu)
  - `database/migrations/2025_01_01_000008_create_charges_table.php:8` (site+daire+donem+hesap icin unique constraint yok)
- Etki: Eszamanli calismalarda duplicate tahakkuk olusabilir.
- Oneri: DB seviyesinde unique index + servis tarafinda `upsert`/hata yakalama.

### 6) Rol-kural uyusmazligi: super-admin UI'da site ayarina gidebiliyor ama API 404 donduruyor (Orta)
- Kanit:
  - UI erisim/menu: `resources/ts/utils/access.ts:45`, `resources/ts/layouts/components/UserProfile.vue:11`
  - API: `app/Http/Controllers/Api/SiteSettingsController.php:16`, `app/Http/Controllers/Api/SiteSettingsController.php:17`
- Etki: super-admin icin kirik kullanici akisi.
- Oneri: Ya super-admin'i UI'da bu sayfadan cikarin, ya da API'de site secimiyle yonetim akisi tanimlayin.

### 7) Lint hatti kirik + frontend kalite borcu yuksek (Orta)
- Kanit:
  - Lint komutu tum repo tariyor: `package.json:10`
  - Starter kit config'i `eslint-plugin-prettier` bekliyor: `starter-kit-laravel-html/.eslintrc.json:8`, `starter-kit-laravel-html/.eslintrc.json:9`
  - App kodunda `221 error` cikti (hedefli eslint calismasinda)
- Etki: CI guvenilirligi dusuk, kod standardi surdurulebilir degil.
- Oneri: Lint kapsamini uretim kaynaklariyla sinirlamak (`resources/ts`, `app`) + tek seferlik `--fix` + kalanlari manuel temizleme.

### 8) Frontend bundle optimizasyonu: ana CSS cok buyuk (Orta)
- Kanit:
  - `resources/ts/main.ts:7` tam template stilini yukluyor.
  - Build ciktisinda `main-*.css ~1.8 MB` (gzip ~314 KB).
- Etki: ilk yukleme maliyeti yuksek.
- Oneri: kullanilmayan style katmanlarini ayirmak, sayfa bazli stil yuklemek, kritik CSS yaklasimi.

### 9) Kucuk teknik borc: loader script'te tekrar eden atama (Dusuk)
- Kanit:
  - `resources/views/application.blade.php:57` ve `resources/views/application.blade.php:59` ayni degiskeni iki kez set ediyor.
- Etki: islevsel kirik degil, gereksiz tekrar.
- Oneri: tek satira dusurulmeli.

### 10) Kucuk UI bug: `super_admin` yazimi role adiyla uyumsuz (Dusuk)
- Kanit:
  - `resources/ts/layouts/components/DefaultLayoutWithHorizontalNav.vue:22`
  - `resources/ts/@layouts/components/VerticalNav.vue:36`
- Etki: super-admin icin baslik/metin davranisi yanlis.
- Oneri: `'super_admin'` -> `'super-admin'`.

## Guclu taraflar
- Site izolasyonu icin global scope yaklasimi (`BelongsToSite`) dogru yonde.
- Login rate-limit tanimli (`throttle:login`).
- Makbuz numarasi uretiminde transaction + lock yaklasimi iyi (`ReceiptService`).
- Frontend type-check ve prod build basariyla geciyor.

## Oncelikli aksiyon plani
1. Dashboard erisim modelini role/permission bazinda sertlestirin.
2. Tahsilat/odeme akislarina transaction ici satir kilidi ve kalan tutar yeniden dogrulamasi ekleyin.
3. `charges` icin benzersizlik kuralini DB seviyesine alin.
4. Testleri yeni API sozlesmesine gore guncelleyin; CI'da kosar hale getirin.
5. Lint kapsamini netlestirip frontend lint borcunu kademeli temizleyin.
6. `CashAccount` balance hesaplarini toplu agregasyona cevirin.
