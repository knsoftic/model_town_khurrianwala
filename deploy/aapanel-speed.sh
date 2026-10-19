#!/bin/bash
# Speed settings for the site on aaPanel. Safe to run on every deploy: each step
# checks first and only changes what is missing, and keeps a backup of any file it edits.
#
#   1. OPcache for PHP 8.4 (keeps compiled PHP in memory: roughly halves page build time).
#   2. Long browser caching for build assets, images, SVGs, WebP copies and fonts.
#
# Usage: bash deploy/aapanel-speed.sh [domain] [php version]   (defaults: mtkfsd.com 84)

set -u
DOMAIN="${1:-mtkfsd.com}"
PHPV="${2:-84}"
PHP="/www/server/php/$PHPV/bin/php"
INI="/www/server/php/$PHPV/etc/php.ini"
CONF="/www/server/panel/vhost/nginx/$DOMAIN.conf"
NGINX="/www/server/nginx/sbin/nginx"
[ -x "$NGINX" ] || NGINX="nginx"
STAMP="$(date +%Y%m%d%H%M%S)"

echo "== OPcache (PHP $PHPV)"
if [ ! -f "$INI" ]; then
    echo "   $INI not found, skipped"
elif "$PHP" -c "$INI" -m 2>/dev/null | grep -qi "zend opcache"; then
    if [ "$("$PHP" -c "$INI" -r 'echo (int) ini_get("opcache.enable");' 2>/dev/null)" = "1" ]; then
        echo "   already on"
    else
        cp "$INI" "$INI.bak-$STAMP"
        sed -i -E 's/^\s*;?\s*opcache\.enable\s*=.*/opcache.enable=1/' "$INI"
        grep -q '^opcache.enable=1' "$INI" || echo 'opcache.enable=1' >> "$INI"
        /etc/init.d/php-fpm-"$PHPV" reload >/dev/null && echo "   switched on"
    fi
else
    SO="$(find "/www/server/php/$PHPV/lib/php/extensions" -name opcache.so 2>/dev/null | head -1)"
    if [ -n "$SO" ]; then
        cp "$INI" "$INI.bak-$STAMP"
        cat >> "$INI" <<EOF

[Zend Opcache]
zend_extension=$SO
opcache.enable=1
opcache.memory_consumption=192
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=2
EOF
        if "$PHP" -c "$INI" -m 2>/dev/null | grep -qi "zend opcache"; then
            /etc/init.d/php-fpm-"$PHPV" reload >/dev/null && echo "   installed and switched on"
        else
            cp "$INI.bak-$STAMP" "$INI"
            echo "   could not load opcache.so, php.ini restored. Install it from aaPanel: App Store > PHP-8.4 > Setting > Install extensions > opcache"
        fi
    else
        echo "   opcache.so not found. Install it from aaPanel: App Store > PHP-8.4 > Setting > Install extensions > opcache"
    fi
fi

echo "== Browser caching ($DOMAIN)"
if [ ! -f "$CONF" ]; then
    echo "   $CONF not found, skipped"
else
    cp "$CONF" "$CONF.bak-$STAMP"
    # aaPanel's default rules: images 30 days (no WebP/SVG/fonts), JS/CSS only 12 hours.
    # Build files have a content hash in their name, so a year is safe.
    sed -i -E 's/\(gif\|jpg\|jpeg\|png\|bmp\|swf\)\$/(gif|jpg|jpeg|png|bmp|swf|webp|avif|svg|ico|woff2?)$/' "$CONF"
    sed -i -E '/\(js\|css\)\?\$/,/\}/ s/expires\s+12h;/expires      365d;/' "$CONF"

    if cmp -s "$CONF" "$CONF.bak-$STAMP"; then
        rm -f "$CONF.bak-$STAMP"
        echo "   already set"
    elif "$NGINX" -t >/dev/null 2>&1; then
        "$NGINX" -s reload && echo "   updated"
    else
        cp "$CONF.bak-$STAMP" "$CONF"
        echo "   nginx rejected the change, original config restored"
    fi
fi
