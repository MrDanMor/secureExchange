#!/usr/bin/env bash
set -Eeuo pipefail
echo "=== OS ==="; cat /etc/os-release
echo "=== Kernel ==="; uname -a
echo "=== Users (human/system overview) ==="; getent passwd
echo "=== Groups ==="; getent group
echo "=== sudo group ==="; getent group sudo || true
echo "=== Listening sockets ==="; ss -tulpn
echo "=== SSH effective settings ==="
sudo sshd -t && sudo sshd -T | egrep '^(port|permitrootlogin|passwordauthentication|pubkeyauthentication|allowusers|allowgroups|subsystem)'
echo "=== Services ==="
systemctl --no-pager --full status ssh apache2 2>&1 || true
echo "=== Apache modules ==="; apache2ctl -M 2>/dev/null || true
echo "=== Apache vhosts ==="; apache2ctl -S 2>&1 || true
echo "=== PHP ==="; php -v 2>&1 || true
echo "=== Web directories ==="; sudo find /var/www -maxdepth 4 -printf '%M %u:%g %p\n' 2>/dev/null || true
echo "=== Exchange directories ==="; sudo find /srv -maxdepth 4 -printf '%M %u:%g %p\n' 2>/dev/null || true
echo "=== Firewall ==="; sudo ufw status verbose 2>&1 || true
echo "=== Recent auth log ==="; sudo tail -n 40 /var/log/auth.log 2>/dev/null || true
echo "=== Recent Apache errors ==="; sudo tail -n 40 /var/log/apache2/error.log 2>/dev/null || true
