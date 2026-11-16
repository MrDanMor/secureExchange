#!/usr/bin/env bash
set -Eeuo pipefail
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y apache2 php libapache2-mod-php php-cli php-xml php-zip php-mbstring php-curl unzip zip openssh-server ufw fail2ban curl
systemctl enable --now ssh apache2
php -v
apache2 -v
