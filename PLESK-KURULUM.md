# Plesk Ortamında Kurulum Rehberi

## Ön Gereksinimler
- Plesk Obsidian veya üzeri
- PHP 8.2+ (BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML eklentileri)
- MySQL 8.0+ veya MariaDB 10.6+
- Composer (Plesk'te varsayılan olarak gelir)
- Node.js 18+ (opsiyonel - asset derlemesi için)

---

## Adım 1: Domain/Subdomain Ayarı

1. Plesk Panel > **Websites & Domains**
2. Domain veya subdomain seçin (örn: `siteyonetimi.siteniz.com`)
3. **Hosting Settings** > **Document Root** alanını değiştirin:
   ```
   /httpdocs/public
   ```
   > ⚠️ Bu ZORUNLU! Laravel'in giriş noktası `public/index.php`'dir.

4. **SSL/TLS** sertifikası etkinleştirin (Let's Encrypt ücretsiz)

---

## Adım 2: PHP Ayarları

1. Plesk > domain > **PHP Settings**
2. PHP versiyonu: **8.2** veya **8.3** seçin
3. PHP handler: **FPM application** (performans için önerilir)
4. Gerekli eklentiler (etkin olduğundan emin olun):
   - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`
   - `gd`, `json`, `mbstring`, `openssl`
   - `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
5. `memory_limit`: en az `256M`
6. `max_execution_time`: en az `60`
7. `upload_max_filesize`: `10M` (ihtiyaca göre)

---

## Adım 3: Veritabanı Oluşturma

1. Plesk > **Databases** > **Add Database**
2. Ayarlar:
   - Database name: `siteyonetimi`
   - Database server: localhost (MySQL)
   - Database user: yeni kullanıcı oluşturun
   - Password: güçlü şifre belirleyin
3. Bu bilgileri not alın (.env için gerekli)

---

## Adım 4: Dosyaları Yükleme

### Yöntem A: File Manager ile
1. Projeyi ZIP'leyin (tüm dosyalar)
2. Plesk > **File Manager** > `httpdocs` dizinine yükleyin
3. ZIP'i açın

### Yöntem B: SSH ile (Önerilen)
```bash
# Plesk SSH erişimi açık olmalı
cd /var/www/vhosts/siteniz.com/httpdocs

# Git ile (repo varsa)
git clone https://github.com/kullanici/siteyonetimi.git .

# Veya SCP/SFTP ile dosyaları yükleyin
```

### Yöntem C: FTP ile
- FileZilla veya benzeri FTP istemcisi kullanın
- Plesk'ten FTP bilgilerinizi alın
- Tüm dosyaları `httpdocs/` altına yükleyin

---

## Adım 5: SSH ile Kurulum

Plesk > domain > **SSH Access** (aktif olmalı)

```bash
# Proje dizinine gir
cd /var/www/vhosts/siteniz.com/httpdocs

# .env dosyasını oluştur
cp .env.plesk .env
nano .env   # DB bilgilerini düzenle
```

### .env'de Değiştirilecekler:
```env
APP_URL=https://sitenizinalanadi.com
DB_DATABASE=plesk_panel_db_adi
DB_USERNAME=plesk_panel_db_kullanici
DB_PASSWORD=plesk_panel_db_sifre
SEED_DEMO_DATA=false
```

### Kurulum komutlarını çalıştır:
```bash
# Composer bağımlılıkları
composer install --no-dev --optimize-autoloader

# Uygulama anahtarı
php artisan key:generate

# Dizin izinleri
mkdir -p storage/logs
mkdir -p storage/framework/{cache,sessions,testing,views}
mkdir -p storage/fonts
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Veritabanı kurulumu
php artisan migrate --force
php artisan db:seed --force

# Demo veri istenirse (opsiyonel)
# SEED_DEMO_DATA=true yapip tekrar calistirin
# php artisan db:seed --class=DemoDataSeeder --force

# Storage symlink
php artisan storage:link

# Cache optimizasyonu (production için)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Adım 6: Frontend Asset'leri

### Seçenek A: Sunucuda derleme (Node.js varsa)
```bash
npm ci
npm run build
```

### Seçenek B: Lokalde derleyip yükleme (Önerilen)
Kendi bilgisayarınızda:
```bash
npm install
npm run build
```
Sonra `public/build/` klasörünü FTP/SCP ile sunucuya yükleyin.

---

## Adım 7: Apache/Nginx Ayarları

### Plesk + Apache (Varsayılan)
Plesk > domain > **Apache & nginx Settings** > **Additional directives for Apache**:

```apache
<Directory /var/www/vhosts/siteniz.com/httpdocs/public>
    AllowOverride All
    Require all granted
</Directory>
```

Laravel'in `.htaccess` dosyası zaten `public/` içinde olmalı. Yoksa oluşturun:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Plesk + Nginx (Proxy mode)
Plesk > domain > **Apache & nginx Settings** > **Additional nginx directives**:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/www/vhosts/system/siteniz.com/php-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}

location ~ /\.(?!well-known).* {
    deny all;
}
```

---

## Adım 8: Cron Job (Scheduler)

Plesk > **Scheduled Tasks (Cron Jobs)** > **Add Task**:

```
Komut:   /opt/plesk/php/8.2/bin/php /var/www/vhosts/siteniz.com/httpdocs/artisan schedule:run >> /dev/null 2>&1
Çalışma: Her dakika (*/1 * * * *)
```

> Not: PHP yolunu Plesk'teki PHP versiyonuna göre ayarlayın:
> - PHP 8.2: `/opt/plesk/php/8.2/bin/php`
> - PHP 8.3: `/opt/plesk/php/8.3/bin/php`
> - Emin değilseniz: `which php` komutuyla kontrol edin

Bu sayede her ay 1'inde otomatik aidat tahakkuku, her gün tekrarlayan giderler üretilir.

---

## Adım 9: Son Kontroller

1. **Tarayıcıdan erişim testi:**
   `https://sitenizinalanadi.com` açın → Login sayfası gelmeli

2. **Giriş bilgileri:**
   - E-posta: `admin@siteyonetimi.test`
   - Şifre: `password`
   - ⚠️ **İlk girişte şifreyi mutlaka değiştirin!**

3. **Dashboard kontrol:**
   - Donut grafikler görünüyor mu?
   - Kasa/Banka bakiyeleri doğru mu?
   - Menüler çalışıyor mu?

4. **Hata kontrolü:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## Sorun Giderme

### "500 Internal Server Error"
```bash
chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan cache:clear
```

### "Class not found" hatası
```bash
composer dump-autoload
php artisan clear-compiled
```

### Beyaz sayfa / CSS/JS yüklenmiyor
- `public/build/` klasörünün varlığını kontrol edin
- Asset'leri lokalde derleyip yükleyin: `npm run build`
- `APP_URL` doğru mu kontrol edin

### "SQLSTATE Connection refused"
- .env'deki DB bilgilerini kontrol edin
- `DB_HOST=localhost` deneyin (`127.0.0.1` yerine)

### Cron çalışmıyor
- PHP yolunun doğru olduğunu kontrol edin
- Cron loglarını inceleyin: `grep CRON /var/log/syslog`
- Manuel test: `php artisan schedule:run`

### Türkçe karakter sorunu (PDF)
- `storage/fonts/` dizininin yazılabilir olduğundan emin olun
- DomPDF ilk çalışmada font cache oluşturur

---

## Güncelleme Prosedürü

Yeni versiyon yükledikten sonra:
```bash
cd /var/www/vhosts/siteniz.com/httpdocs
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Hızlı Deploy Script

Tüm adımları otomatik çalıştırmak için:
```bash
chmod +x deploy-plesk.sh
./deploy-plesk.sh
```
