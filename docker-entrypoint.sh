#!/bin/bash
set -e

# Настройка прав
chown -R www-data:www-data /var/www/html
chown -R root:root /var/www/html/modules/pingit
chmod -R 755 /var/www/html/modules/pingit


# Экспорт переменных окружения
declare -p | grep -E 'PATH|DB_PASS' > /container.env
chmod 644 /container.env

# Настройка cron
echo 'SHELL=/bin/bash' > /etc/cron.d/pingit
echo 'BASH_ENV=/container.env' >> /etc/cron.d/pingit
echo '*/5 * * * * root ( cd /var/www/html/modules/pingit/ && ./run-modules.sh ) >> /var/log/pingit.log 2>&1' >> /etc/cron.d/pingit
chmod 644 /etc/cron.d/pingit

# Запуск cron в background
cron

# Запуск Apache в foreground (основной процесс контейнера)
exec apache2-foreground