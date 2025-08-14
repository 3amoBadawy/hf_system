# ==== 0) إعدادات عامة ====
set -euo pipefail
PROJ="/var/www/hf_system"
BACKDIR="/var/backups/hf_system"
ZIPDIR="$PROJ/updategpt"
RESTDIR="/tmp/hf_restore_$(date +%s)"

mkdir -p "$BACKDIR"

# ==== 1) دور على ملف الـZIP المطلوب (احدث ملف فيه v0.4.1_add_top_nav) ====
echo "==> Listing candidate zips in $ZIPDIR ..."
ls -lh "$ZIPDIR"/*.zip 2>/dev/null || { echo "❌ مفيش زِبس في $ZIPDIR"; exit 1; }

ZIPFILE="$(ls -t "$ZIPDIR"/*v0.4.1_add_top_nav*.zip 2>/dev/null | head -n1 || true)"
if [ -z "${ZIPFILE:-}" ]; then
  echo "❌ مفيش ZIP اسمه فيه v0.4.1_add_top_nav. اختار ZIP يدويًا:"
  read -rp "اكتب المسار الكامل لملف الـZIP: " ZIPFILE
fi
[ -f "$ZIPFILE" ] || { echo "❌ الملف مش موجود: $ZIPFILE"; exit 1; }
echo "==> Using ZIP: $ZIPFILE"

# ==== 2) باك أب للوضع الحالي ====
echo "==> Backup current project to $BACKDIR ..."
tar -czf "$BACKDIR/hf_system_before_restore_$(date +%Y%m%d_%H%M%S).tar.gz" -C /var/www hf_system

# ==== 3) فك الضغط في مجلد مؤقت ====
echo "==> Unzip to $RESTDIR ..."
mkdir -p "$RESTDIR"
unzip -q "$ZIPFILE" -d "$RESTDIR"

# بعض الزبس بتفك في فولدر داخلي؛ نزبط ROOT
CANDIDATE="$(find "$RESTDIR" -maxdepth 2 -type d -name 'vendor' -printf '%h\n' -quit || true)"
[ -n "${CANDIDATE:-}" ] && RESTROOT="$CANDIDATE" || RESTROOT="$RESTDIR"
echo "==> RESTROOT = $RESTROOT"

# ==== 4) حافظ على .env الحالي و public/index.php الحالي (لو لزم) ====
# هننسخ .env الموجود دلوقتي للنسخة الجديدة لو مش موجود فيها
if [ -f "$PROJ/.env" ] && [ ! -f "$RESTROOT/.env" ]; then
  cp -a "$PROJ/.env" "$RESTROOT/.env"
  echo "==> Copied current .env to restored tree"
fi

# ==== 5) سواب آمن للمجلد ====
echo "==> Swapping directories ..."
mv "$PROJ" "${PROJ}_old_$(date +%s)"
mkdir -p /var/www
mv "$RESTROOT" "$PROJ"

# لو اتبقى بواقي فك الضغط امسحها
rm -rf "$RESTDIR" || true

# ==== 6) صلاحيات لارافيل ====
echo "==> Fixing permissions ..."
mkdir -p "$PROJ/storage/framework/"{cache,data,sessions,views} "$PROJ/bootstrap/cache"
chown -R www-data:www-data "$PROJ/storage" "$PROJ/bootstrap/cache"
find "$PROJ/storage" -type d -exec chmod 775 {} \; ; find "$PROJ/storage" -type f -exec chmod 664 {} \;
find "$PROJ/bootstrap/cache" -type d -exec chmod 775 {} \; ; find "$PROJ/bootstrap/cache" -type f -exec chmod 664 {} \;

# ==== 7) Composer (لو مفيش vendor/ أو عايزين نضمن التزامن) ====
cd "$PROJ"
export COMPOSER_ALLOW_SUPERUSER=1
if [ ! -d vendor ]; then
  echo "==> composer install (no vendor found) ..."
  composer install --no-dev --prefer-dist --optimize-autoloader -n || true
else
  echo "==> vendor exists; running composer dump-autoload for safety ..."
  composer dump-autoload -o || true
fi

# ==== 8) علاج خطأ Laravel\Pail\PailServiceProvider (لو ظهر) ====
if grep -q "Laravel\\\\Pail\\\\PailServiceProvider" config/app.php 2>/dev/null; then
  if ! grep -q "laravel/pail" composer.lock 2>/dev/null; then
    echo "==> Removing Pail provider from config/app.php (not installed in prod) ..."
    cp config/app.php "config/app.php.bak.$(date +%s)"
    sed -i "/Laravel\\\\\\\\Pail\\\\\\\\PailServiceProvider/d" config/app.php || true
  fi
fi

# ==== 9) كاش وميجريشن ====
echo "==> Clearing caches and running migrations ..."
sudo -u www-data php artisan config:clear || true
sudo -u www-data php artisan route:clear  || true
sudo -u www-data php artisan view:clear   || true
sudo -u www-data php artisan migrate --force || true
sudo -u www-data php artisan optimize:clear

# ==== 10) Reload PHP-FPM + Nginx ====
systemctl reload php8.3-fpm 2>/dev/null || systemctl reload php8.2-fpm 2>/dev/null || systemctl reload php8.1-fpm 2>/dev/null || true
systemctl reload nginx 2>/dev/null || true

# ==== 11) تأكيد الراوتس الأساسية ====
echo "==> Routes snapshot:"
php artisan route:list | grep -E "login|logout|sales|customers|branches|admin" || true

echo "✅ Done. افتح /login وشيّك إن النافبار (top nav) ظاهر كما في v0.4.1"
