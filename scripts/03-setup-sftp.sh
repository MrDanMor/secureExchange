#!/usr/bin/env bash
set -Eeuo pipefail
USER_NAME=axapta_sftp
GROUP_NAME=sftpusers
CHROOT="/srv/sftp/${USER_NAME}"
if ! getent group "$GROUP_NAME" >/dev/null; then groupadd "$GROUP_NAME"; fi
if ! id "$USER_NAME" >/dev/null 2>&1; then
  useradd -M -g "$GROUP_NAME" -s /usr/sbin/nologin "$USER_NAME"
fi
install -d -o root -g root -m 0755 "$CHROOT"
install -d -o "$USER_NAME" -g "$GROUP_NAME" -m 0750 "$CHROOT/upload"
echo
echo "SFTP user created: $USER_NAME"
echo "Set a strong lab password now (or install a public key using a tested procedure):"
passwd "$USER_NAME"
echo
echo "Add the Match block from config/ssh/sshd_config.d/60-sftp-lab.conf to /etc/ssh/sshd_config.d/"
echo "Then run: sudo sshd -t && sudo systemctl reload ssh"
