Sen kıdemli bir full-stack mühendissin. Web tabanlı “Apartman/Site Yönetimi – Aidat & Gider Takip” sistemi geliştireceksin. Hızlı geliştirme için Seçenek B seçildi: Laravel (PHP) + Blade veya Laravel + Inertia/Vue (tercihen Blade + Admin panel). Veritabanı PostgreSQL (MySQL da kabul). Proje çok kiracılı olabilir ama MVP’de tek site yeter; yine de tasarım çoklu siteyi destekleyecek şekilde olsun.

AMAÇ
- Apartman/site yönetimi için aidat (tahakkuk), tahsilat, gider, ödeme, kasa/banka, hesaplar ve raporlar modülleri.
- Kullanıcı rolleri: Admin (tam yetkili), Daire Sahibi, Kiracı, Tedarikçi.
- Malik/kiracı/tedarikçi sadece kendi borç-alacak ve ilgili kayıtları görür; tüm işlemleri admin yönetir.

TEKNOLOJİ / KURALLAR
- Laravel 11 (veya güncel LTS), PHP 8.2+.
- Auth: Laravel Breeze/Jetstream (email + şifre), role-permission için spatie/laravel-permission.
- DB: migrations + seeders.
- UI: Yönetim paneli için basit, temiz responsive admin arayüzü (Bootstrap/Tailwind). Menü: Genel Bakış, Aidatlar, Giderler, Hesaplar, Kasa/Banka, Yönetim, Raporlar.
- Kod standardı: Service/Repository katmanı, FormRequest validation, Policy/Permission kontrolü, API değil web MVC (istersen internal API kullanılabilir).
- Cron/Scheduler: Laravel Scheduler ile aylık aidat oluşturma + tekrarlanan gider oluşturma.
- Para birimi: TRY, tüm parasal alanlar decimal(14,2).
- Soft deletes kritik tablolarda kullanılabilir.
- Multi-tenant hazırla: Tüm ana tablolar site_id içerir (MVP’de tek site kaydıyla çalışır). Yetkilendirme site bazlı scope ile.

MODÜLLER (MVP KAPSAMI)
1) GENEL BAKIŞ (Dashboard)
- Tahsil Edilecekler donut: vadesi gelmemiş / bugün ödenecek / geciken toplamları.
- Ödenecekler donut: vadesi gelmemiş / bugün ödenecek / geciken toplamları.
- Kasa & Banka toplam bakiyeleri.
- Son hareketler listesi (tahsilat/ödeme).
- “Yazdırılacak makbuz sayısı” gibi sayaçlar (basit placeholder).

2) AİDATLAR (Tahakkuk)
- “Borç Ekle” (tek daire/kişi): dönem, vade tarihi, açıklama, tutar, hesap (gelir hesabı).
- “Toplu Borç Ekle”: seçili daireler veya tüm daireler için aynı dönem/tutar/vade ile toplu tahakkuk oluştur.
- Liste/filtre: dönem, daire, durum (vadesi gelmemiş / geciken / kapandı), arama.
- Tahakkuk detay: ödeme geçmişi, kalan borç.
- Tahsilat al (admin): ödeme tarihi, ödeme yöntemi (nakit/banka), kasa/banka hesabı, açıklama. Parsiyel ödeme destekle.
- Makbuz oluştur: numara, PDF çıktı (basit HTML->PDF dompdf).
- Otomatik Aidat: Aidat Şablonu tanımla:
  - ad, tutar, vade_gunu (1-28), hangi daireler (tümü/seçili), hesap_id, aktif mi.
  - Scheduler her ayın 1’inde çalışsın: o ay için tahakkukları üret, aynı dönem tekrar üretmesin (idempotent).
  - Üretilen tahakkuk kaydı: type=aidat, dönem=YYYY-MM, vade= o ayın vade_gunu.

3) GİDERLER
- Gider Ekle (admin): tedarikçi, tarih, vade, tutar, açıklama, gider_hesap_id.
- Tekrarlanan Gider Şablonu:
  - ad, tutar, periyot (aylık/3aylık/yıllık), vade_gunu, tedarikçi, gider_hesap_id, aktif.
  - Scheduler her gün çalışabilir: zamanı gelen şablonlar için gider kaydı üret; tekrar üretmesin (idempotent).
- Ödeme Yap (admin): ödeme tarihi, yöntem (nakit/banka), kasa/banka hesabı, açıklama. Parsiyel ödeme opsiyonel ama ideal.
- Gider listesi/filtre: tarih aralığı, tedarikçi, durum (ödenmedi/kısmi/ödendi).

4) HESAPLAR (Hesap Planı)
- Hesaplar: kod, ad, tür (gelir/gider/varlık/borç) minimum.
- Tahakkuk ve gider bir hesaba bağlanır.
- Hesap ekstresi raporu için hareketler hesap bazında toplanır.

5) KASA / BANKA
- Kasa/Banka Hesapları: ad, tür (kasa/banka), başlangıç bakiye.
- Tüm tahsilat ve ödemeler bir kasa/banka hesabına işlenir.
- Ekstre: seçili kasa/banka hesabı + tarih aralığı hareket listesi + başlangıç/bitis bakiye.

6) YÖNETİM (Çekirdek)
- Daireler CRUD: blok, kat, no, m2, arsa_payi(optional), aktif.
- Daire ilişkileri:
  - Daire Sahibi (User) ilişkisi (çoklu sahip opsiyonel ama MVP: 1 malik).
  - Kiracı (User) ilişkisi (opsiyonel, zaman aralığı tutulabilir).
- Kullanıcılar:
  - Admin kullanıcıları yönetir: rol atar (admin/owner/tenant/vendor).
  - Vendor kullanıcıları tedarikçi profili ile eşleşebilir.
- Tedarikçiler CRUD: unvan, vergi no, telefon, email, adres.

7) RAPORLAR (MVP)
Finansal Raporlar:
- Kasa - Banka Ekstresi
- Hesap Ekstresi
- Tahsilatlar (tarih aralığı)
- Ödemeler (tarih aralığı)
- Borç Durumu (açık tahakkuklar: daire bazında toplam)
- Alacak Durumu (aynı mantık)
- Tahakkuk Listeleri (dönem bazında aidat)

Rapor çıktı formatı: web sayfası + “PDF indir” opsiyonu (dompdf).

İŞ KURALLARI / DURUM HESAPLAMA
- Tahakkuk (aidat/borç) durumları:
  - open: toplam_ödeme < tutar
  - paid: toplam_ödeme >= tutar
  - overdue: vade < bugün ve open
- Gider durumları:
  - unpaid/partial/paid
- Parsiyel ödeme: ödeme dağıtımı bir tahakkuk/gider üzerinde tutulur (payment allocations).

VERİ MODELİ (TABLO ÖNERİSİ — BUNLARI UYGULA)
- sites: id, name, ...
- users: (laravel default) + site_id
- roles/permissions: spatie
- apartments (daireler): id, site_id, block, floor, number, m2, is_active, ...
- apartment_user (pivot): apartment_id, user_id, relation_type (owner/tenant), start_date, end_date (tenant için)
- vendors (tedarikçiler): id, site_id, name, tax_no, phone, email, address, user_id(optional)
- accounts (hesaplar): id, site_id, code, name, type (income/expense/asset/liability)
- cash_accounts (kasa_banka): id, site_id, name, type (cash/bank), opening_balance
- charges (tahakkuklar): id, site_id, apartment_id, account_id, charge_type (aidat/other), period (YYYY-MM), due_date, amount, description, created_by
- receipts (tahsilatlar): id, site_id, apartment_id(optional), cash_account_id, paid_at, method (cash/bank/online), total_amount, description, created_by
- receipt_items (tahsilat dağılımı): id, receipt_id, charge_id, amount
- expenses (giderler): id, site_id, vendor_id, account_id, expense_date, due_date, amount, description, created_by
- payments (ödemeler): id, site_id, vendor_id(optional), cash_account_id, paid_at, method, total_amount, description, created_by
- payment_items: id, payment_id, expense_id, amount
- templates_aidat: id, site_id, name, amount, due_day, account_id, scope (all/selected), active
- templates_aidat_apartments: template_id, apartment_id (scope=selected için)
- templates_expense: id, site_id, name, amount, due_day, period (monthly/quarterly/yearly), vendor_id, account_id, active, last_generated_at(optional)
- documents/receipts_pdf: makbuz numarası + pdf path (istersen receipts tablosuna receipt_no ekle)

YETKİLENDİRME
- Admin: tüm modüller CRUD
- Owner/Tenant:
  - Sadece kendi apartment_id’lerine bağlı charges/receipts görebilir.
  - Gider/ödemeleri görmez (opsiyon: genel giderleri sadece toplam gösterebilirsin).
- Vendor:
  - Kendi vendor_id’sine bağlı expenses/payments görebilir.
- Policies + spatie permissions ile uygula.

ROUTING / SAYFALAR
- /login
- /dashboard
- /charges (list/create bulk/create single/show/collect payment)
- /receipts (list/show/print)
- /expenses (list/create/create recurring/show/pay)
- /payments (list/show)
- /accounts (CRUD)
- /cash-accounts (CRUD + statement)
- /management/apartments (CRUD + residents)
- /management/users (CRUD + roles)
- /management/vendors (CRUD)
- /reports/* (sayfa + pdf)
- /settings/templates (aidat template + gider template)

SCHEDULER KOMUTLARI
- php artisan make:command GenerateMonthlyCharges
  - her ayın 1’i 03:00’te çalışsın
  - her aktif aidat template için: o ayın period’u için charges yoksa üret.
- php artisan make:command GenerateRecurringExpenses
  - her gün 04:00’te çalışsın
  - periyot kontrolüyle zamanı gelen template’lerden expense üret, dupe engelle.

SEED / DEMO DATA
- Varsayılan site oluştur
- Admin kullanıcı oluştur
- Bazı hesaplar seed: Aidat Geliri, Su Gideri, Elektrik Gideri, Bakım-Onarım, Personel vb.
- Örnek 10 daire, 2 vendor

TEST
- En azından feature test: admin borç ekle, toplu borç ekle, tahsilat al, gider ekle, ödeme yap, scheduler idempotency.
- Validation testleri.

ÇIKTI
- Çalışan Laravel projesi: migrations, seeders, controllers, services, policies, blade views, scheduler, pdf çıktıları.
- Kurulum dokümantasyonu: .env örneği, migrate/seed, scheduler kurulumu (cron entry).
- Menü ve sayfalar Türkçe metinler.

Şimdi kodu üret: proje iskeleti, paket kurulumları, tüm migration’lar, modeller, ilişkiler, controller+service, blade view’lar, rapor sayfaları, PDF çıktılar, scheduler komutları ve seed/testleri. Önce mimariyi kısa bir dosya ağacıyla göster, ardından adım adım kodu ver.

database olarak mysql kullanılacak.
çoklu site desteği olacak.
aidatlar ve giderler için otomatik tekrarlanabilir seçeneği olacak.