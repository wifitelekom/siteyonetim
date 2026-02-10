#!/bin/bash
# ==============================================
# Plesk Ortamı için Laravel Deploy Script
# ==============================================
# Bu scripti Plesk SSH üzerinden çalıştırın:
#   chmod +x deploy-plesk.sh && ./deploy-plesk.sh
# ==============================================

set -e

echo "================================================"
echo " Site Yönetimi - Plesk Deploy Script"
echo "================================================"

# 1. Composer bağımlılıklarını yükle
echo ""
echo "[1/8] Composer bağımlılıkları yükleniyor..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    php /usr/lib/plesk-9.0/composer.phar install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || \
    php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || \
    echo "HATA: Composer bulunamadı! Plesk'te Composer kurulu olduğundan emin olun."
fi

# 2. .env dosyasını oluştur (yoksa)
echo ""
echo "[2/8] .env dosyası kontrol ediliyor..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "  .env dosyası oluşturuldu. Lütfen düzenleyin!"
    echo "  >>> ÖNEMLI: DB bilgilerini .env'de güncelleyin <<<"
else
    echo "  .env zaten mevcut, atlanıyor."
fi

# 3. Uygulama anahtarı oluştur
echo ""
echo "[3/8] Uygulama anahtarı oluşturuluyor..."
php artisan key:generate --force

# 4. Dizin izinleri
echo ""
echo "[4/8] Dizin izinleri ayarlanıyor..."
mkdir -p storage/logs
mkdir -p storage/framework/{cache,sessions,testing,views}
mkdir -p storage/fonts
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache
# Plesk'te web sunucu kullanıcısına izin ver
chown -R $(whoami):psacln storage bootstrap/cache 2>/dev/null || true

# 5. Veritabanı migration
echo ""
echo "[5/8] Veritabanı tabloları oluşturuluyor..."
php artisan migrate --force

# 6. Seed (ilk kurulumda)
echo ""
echo "[6/8] Demo veriler yükleniyor..."
php artisan db:seed --force

# 7. npm build (assets)
echo ""
echo "[7/8] Frontend asset'leri derleniyor..."
if command -v npm &> /dev/null; then
    npm ci --production=false
    npm run build
    echo "  Asset'ler derlendi."
else
    echo "  UYARI: npm bulunamadı. Asset'leri lokalde derleyip yükleyin."
    echo "  Lokal makinede: npm install && npm run build"
    echo "  Sonra public/build/ klasörünü sunucuya yükleyin."
fi

# 8. Cache ve optimizasyon
echo ""
echo "[8/8] Cache ve optimizasyon..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

echo ""
echo "================================================"
echo " Deploy tamamlandı!"
echo "================================================"
echo ""
echo " YAPILACAKLAR:"
echo " 1. .env dosyasındaki DB bilgilerini kontrol edin"
echo " 2. Plesk'te Document Root: /httpdocs/public"
echo " 3. PHP 8.2+ seçili olduğundan emin olun"
echo " 4. Cron job ekleyin (aşağıya bakın)"
echo ""
echo " Giriş bilgileri:"
echo "   E-posta: admin@siteyonetimi.test"
echo "   Şifre:   password"
echo ""
echo " İlk girişte şifreyi değiştirmeyi unutmayın!"
echo "================================================"
