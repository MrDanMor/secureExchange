#!/usr/bin/env bash
set -Eeuo pipefail
APP=/var/www/secure-file-exchange
EXCHANGE=/srv/axapta-exchange
install -d -o root -g root -m 0755 "$APP"
install -d -o root -g root -m 0755 "$APP/public"
install -d -o root -g root -m 0750 "$EXCHANGE"
install -d -o www-data -g www-data -m 0750 "$EXCHANGE/incoming"
install -d -o www-data -g www-data -m 0750 "$EXCHANGE/processed"
install -d -o www-data -g www-data -m 0750 "$EXCHANGE/quarantine"
cp -a "$(dirname "$0")/../app/." "$APP/public/"
chown -R root:root "$APP"
find "$APP" -type d -exec chmod 0755 {} \;
find "$APP" -type f -exec chmod 0644 {} \;
# The web application needs write access only to the exchange incoming folder.
usermod -aG www-data www-data 2>/dev/null || true
chown www-data:www-data "$EXCHANGE/incoming"
chmod 0750 "$EXCHANGE/incoming"
echo "Deployed to $APP; exchange storage is $EXCHANGE"
