#!/usr/bin/env bash
set -Eeuo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
install -m 0644 "$ROOT/config/apache/secure-file-exchange.conf" /etc/apache2/sites-available/secure-file-exchange.conf
a2dissite 000-default.conf || true
a2ensite secure-file-exchange.conf
a2enmod rewrite
apache2ctl configtest
systemctl reload apache2
