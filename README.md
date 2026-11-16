# Secure File Exchange

Тренировочный проект для практики администрирования Ubuntu. Имитация портала поставщика и файлового обмена. Загрузка XLSX через PHP, отдельный пользователь получает доступ только к каталогу обмена.

## Архитектура

- Apache document root: `/var/www/secure-file-exchange/public`
- Файлы обмена: `/srv/axapta-exchange/incoming` (вне web-root)
- SFTP-пользователь: `axapta_sftp` (без shell)
- Chroot: `/srv/sftp/axapta_sftp`; writable-папка внутри chroot: `/upload`
- PHP-приложение: загрузка XLSX с проверкой расширения, MIME, ZIP/OOXML-маркеров, лимита размера и случайным именем
- Файлы не выдаются напрямую веб-сервером

## Требования

- Ubuntu Server 24.04 VM
- Пользователь с sudo
- Для HTTPS в локальной лаборатории можно начать с HTTP на localhost
- Let's Encrypt требует реального домена и доступности challenge-порта

## Проверки

```bash
# Состояние служб и порты
systemctl status apache2 ssh
ss -tulpn

# SSH конфигурация
sudo sshd -t
sudo sshd -T | egrep 'permitrootlogin|passwordauthentication|pubkeyauthentication|port'

# Apache
sudo apache2ctl configtest
curl -i http://127.0.0.1/

# Web-root
find /var/www/secure-file-exchange -maxdepth 3 -type d -ls

# Журналы
sudo tail -n 100 /var/log/auth.log
sudo tail -n 100 /var/log/apache2/access.log
sudo tail -n 100 /var/log/apache2/error.log
```

## Загрузчик

- Откройте портал, загрузите настоящий небольшой `.xlsx`.
- Попробуйте `.php`, `.txt`, переименованный файл и слишком большой файл — они должны отклоняться.
- Убедитесь, что принятый файл появился в `/srv/axapta-exchange/incoming`.
- Попробуйте запросить `/uploads/` или `/exchange/` по HTTP: этих URL в приложении нет; каталог хранения находится вне web-root.
- Не загружайте реальные конфиденциальные документы в лабораторию.

Проверка MIME/ZIP/OOXML — это многоуровневый фильтр, но не полноценная проверка безопасности Office-документов. Для production добавьте антивирусную проверку (например, ClamAV), лимиты распаковки, контроль XML/ZIP-bomb, журналирование, CSRF, rate limiting и бизнес-валидацию XLSX.

## SFTP

Chroot-структура:
```text
/srv/sftp/axapta_sftp        root:root 755
└── upload                   axapta_sftp:sftpusers 750
```

SFTP-пользователь может писать только в `/upload`. В учебной схеме файлы попадают туда, но приложение сохраняет загрузки портала в `/srv/axapta-exchange/incoming`. Для имитации обмена можно вручную перенести файлы из SFTP upload в incoming после проверки.

Применение:
```bash
sudo install -m 0644 config/sshd_config.d/90-lab-hardening.conf /etc/ssh/sshd_config.d/
sudo sshd -t
sudo sshd -T | egrep 'permitrootlogin|passwordauthentication|pubkeyauthentication'
# Только после проверки и при наличии консоли:
sudo systemctl reload ssh
```

## Логи и вывод

Шаблон отчёта: `docs/audit-report-template.md`.
Команды первичного аудита: `scripts/00-audit.sh`.
