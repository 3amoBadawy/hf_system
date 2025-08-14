#!/usr/bin/env bash
set -euo pipefail

APP_PATH="${1:-/var/www/hf_system}"
ZIP_PATH="${2:-}"

if [[ -z "${ZIP_PATH}" ]]; then
  echo "Usage: $0 <APP_PATH> <ZIP_PATH>"
  exit 1
fi

echo "==> Unzipping v0.4.1 (Top Nav) into ${APP_PATH}"
unzip -o "${ZIP_PATH}" -d "${APP_PATH}"

LAYOUT="${APP_PATH}/resources/views/layouts/app.blade.php"
NAVLINE="@includeIf('layouts.nav')"

if [[ -f "$LAYOUT" ]] && ! grep -q "layouts.nav" "$LAYOUT"; then
  cp "$LAYOUT" "${LAYOUT}.bak.$(date +%s)"
  # حاول الإدراج بعد <body> أولاً، لو مش موجود بعد <div id="app">
  if grep -n "<body" "$LAYOUT" >/dev/null; then
    # أدخل سطرين بعد أول <body>
    awk -v ins="<?php echo $NAVLINE; ?>" '
      BEGIN{done=0}
      {
        print
        if (!done && $0 ~ /<body[^>]*>/) {
          print "    " "<?php echo \"@includeIf('\" \"layouts.nav\" \"')\"; ?>"
          done=1
        }
      }' "$LAYOUT" > "${LAYOUT}.tmp" && mv "${LAYOUT}.tmp" "$LAYOUT"
  elif grep -n "id=[\"\\']app[\"\\']" "$LAYOUT" >/dev/null; then
    awk '
      BEGIN{done=0}
      {
        print
        if (!done && $0 ~ /id=[\"\047]app[\"\047]/) {
          print "    @includeIf('layouts.nav')"
          done=1
        }
      }' "$LAYOUT" > "${LAYOUT}.tmp" && mv "${LAYOUT}.tmp" "$LAYOUT"
  else
    # لو معرفناش مكان مناسب، هنضيفه في أول @yield('content') كحل أخير
    sed -i "0,/yield('content')/s//includeIf('layouts.nav')\n@yield('content')/" "$LAYOUT" || true
  fi
fi

cd "${APP_PATH}"
php artisan config:clear || true
php artisan view:clear || true
php artisan route:clear || true
php artisan cache:clear || true

systemctl reload php8.3-fpm 2>/dev/null || true

echo "==> v0.4.1 Top navigation injected ✅"
