#!/usr/bin/env bash
set -Eeuo pipefail
echo "Apache config:"; sudo apache2ctl configtest
echo "SSH config:"; sudo sshd -t
echo "Services:"; systemctl is-active apache2 ssh
echo "Ports:"; ss -tulpn
echo "HTTP headers:"; curl -sSI http://127.0.0.1/ || true
echo "Exchange path permissions:"; namei -l /srv/axapta-exchange/incoming
echo "SFTP chroot permissions:"; namei -l /srv/sftp/axapta_sftp/upload
