# Vue Arayuz Duzeltme ve Refactor Plani (Revize)

Bu plan, mevcut kod tabanindaki dogrulanmis bulgulara gore guncellenmistir. Amac; once kirmizi durumda olan derlemeyi toparlamak, sonra dusuk riskli bug fixleri almak, en son buyuk refactorlara gecmektir.

## Hedefler

1. Typecheck ve build adimlarini tekrar yesile cekmek.
2. Kullaniciya gorunen kritik hatalari hizli kapatmak.
3. Hata yonetimini guvenli ve standart hale getirmek.
4. Performans tarafinda bekleyen kolay kazanimlari almak.
5. Reports sayfasini parcali mimariye guvenli gecirmek.

## Faz 0 - Baseline Stabilizasyonu (Bloker)

Amac: Refactor oncesi teknik zemini temizlemek.

- `resources/ts/pages/expenses/[id].vue` icindeki syntax hatasini duzelt (fazladan `}`).
- `npm run typecheck` calistir ve sifir TypeScript hatasi hedefle.
- `npm run build` calistir ve derlemenin gectigini dogrula.

Kabul Kriteri:
- Typecheck ve build basarili.

---

## Faz 1 - Hizli Kritik Duzeltmeler (Dusuk Risk)

Amac: Son kullaniciya yansiyan acik hatalari kapatmak.

- `management/users/create.vue`:
  - `password_confirmation` alani icin eksik hata mesaji bagla.
  - Eklenecek: `:error-messages="fieldErrors.password_confirmation ?? []"`.
- `reports/index.vue` ve `pages/index.vue`:
  - Zayif/tekrarlayan `v-for` key kullanimlarini benzersiz key ile degistir.
  - Index tabanli keyleri kaldir.
- `fetchMeta()` fonksiyonlarinda `catch` eksigi olan sayfalara kullaniciya gorunen hata yonetimi ekle.

Kabul Kriteri:
- Kullanici olusturma formunda sifre tekrar hatasi gorunur.
- Key warning'leri konsolda kaybolur.
- Meta endpoint hatasinda kullaniciya mesaj gorunur.

---

## Faz 2 - Guvenli Hata Yonetimi Standardi (Orta Risk)

Amac: `error as FetchError` kaynakli kaza riskini kaldirmak.

- Yeni dosya: `resources/ts/utils/errorHandler.ts`
  - `getApiErrorMessage(error, fallback)`
  - `getApiFieldErrors(error)`
  - Tip guvenli guard mantigi (`unknown` hata ile calisacak sekilde).
- `as FetchError` kullanan tum sayfalari asamali gecisle standart yardimciya tasimak.
- Gereksiz `FetchError` importlarini temizlemek.

Uygulama Notu:
- Buyuk tek PR yerine alan bazli parcalara bol:
  1. List sayfalari
  2. Create/Edit formlari
  3. Detail/Reports

Kabul Kriteri:
- `as FetchError` cast kullanimi hedef sayfalarda sifirlanir.
- Hata mesaji/fielderrors davranisi regresyonsuz calisir.

---

## Faz 3 - Paralel Veri Yukleme Optimizasyonu (Dusuk-Orta Risk)

Amac: Bagimsiz API cagrilarinda ilk acilis gecikmesini azaltmak.

- Sira ile beklenen `fetchMeta()` + `fetchList(1)` akisini paralellestir:
  - `charges/index.vue`
  - `expenses/index.vue`
  - `payments/index.vue`
  - `receipts/index.vue`
  - `accounts/index.vue`
  - `cash-accounts/index.vue`
  - `management/users/index.vue`
  - `templates/aidat/[id]/edit.vue`
  - `templates/expense/[id]/edit.vue`
- Mumkunse `Promise.allSettled` tercih et; bir endpoint hata verirse digeri yine yuklensin.

Kabul Kriteri:
- Sayfalar ilk acilista daha hizli render olur.
- Tek endpoint hatasi tum acilisi bloke etmez.

---

## Faz 4 - Frontend Form Validasyonu (Orta Risk)

Amac: Bos/hatali veri gonderimini request oncesi engellemek.

- Yeni dosya: `resources/ts/utils/validators.ts`
  - `requiredRule`, `emailRule`, `minLengthRule`, `maxLengthRule`, `exactLengthRule`, `positiveNumberRule`, `matchRule`
- Oncelikli formlar:
  - `management/users/create.vue`
  - `charges/create.vue`
  - `expenses/create.vue`
- Form seviyesinde:
  - `ref="formRef"`
  - submit oncesi `await formRef.value?.validate()`

Kabul Kriteri:
- Bos/hatali form submitinde network cagrisi atilmaz.
- Turkce dogrulama mesajlari alan bazinda gorunur.

---

## Faz 5 - Reports Sayfasi Yapisal Refactor (Orta-Yuksek Risk)

Amac: Monolitik rapor ekranini bakimi kolay bilesenlere bolmek.

- `resources/ts/components/reports/` altina panelleri ayir:
  - `ReportCashStatement.vue`
  - `ReportAccountStatement.vue`
  - `ReportCollections.vue`
  - `ReportPayments.vue`
  - `ReportDebtStatus.vue`
  - `ReportReceivableStatus.vue`
  - `ReportChargeList.vue`
- `resources/ts/pages/reports/index.vue` dosyasini orchestrator seviyesine indir:
  - Ortak meta yukleme
  - Panel kapsayici
- Global tek `errorMessage` yerine panel bazli hata durumu kullan.

Not:
- Route guard zaten session/access kontrolu yaptigi icin page-level tekrar kontroller gozden gecirilmeli ve gereksiz olanlar kaldirilmali.

Kabul Kriteri:
- 7 rapor paneli ayri componentlerde calisir.
- Her panel kendi loading/error durumunu yonetir.
- PDF olusturma akislari bozulmaz.

---

## Faz 6 - Temizlik ve Kucuk Iyilestirmeler (Dusuk Risk)

Amac: Kullanilmayan kodu temizlemek, bakim yukunu azaltmak.

- `resources/ts/composables/useApi.ts` kullanimini dogrula; import yoksa dosyayi kaldir.
- `destr` bagimliligini kaldirmadan once tum kullanimlari kontrol et (`@core/composable/useCookie.ts` dahil).
- Opsiyonel: sayfa unmount sirasinda istek iptal composable'i (`useAbortOnUnmount`) agir sayfalara ekle.

Kabul Kriteri:
- Dead code kaldirilir, calisan davranis korunur.
- Bagimlilik temizligi regresyonsuz tamamlanir.

---

## Dogrulama Matrisi (Her Faz Sonrasi)

1. `npm run typecheck`
2. `npm run build`
3. Ilgili sayfada manuel smoke test:
   - hata senaryosu
   - bos state
   - basarili state

Faz bazli ek kontroller:
- Faz 1: `password_confirmation` ve key warning kontrolu.
- Faz 3: Network tabinda paralel cagrilarin dogrulanmasi.
- Faz 5: 7 rapor panelinde yukleme, filtreleme, PDF akisi.
- Faz 4: Invalid formda request atilmadiginin dogrulanmasi.

---

## Uygulama Stratejisi

1. Kucuk ve geri alinabilir PR'lar.
2. Her PR'da tek teknik tema (hata yonetimi / validasyon / reports refactor gibi).
3. Buyuk refactor (`reports`) oncesi tum hizli bug fixlerin merge edilmesi.
