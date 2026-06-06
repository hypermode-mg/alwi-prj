#!/bin/bash
set -e

# Даем права на чтение/запись владельцу и чтение остальным.
# Запрещаем запись для группы и других (go-w), чтобы Apache (www-data) не мог менять файлы.
chmod -R u+rwX,go+rX,go-w /var/www/html 2>/dev/null || true

# Для модулей pingit оставляем ту же логику: чтение есть, записи нет.
# Это защищает от случайного изменения скриптов через веб, но позволяет Cron (root) их выполнять.
chmod -R go-w /var/www/html/modules/pingit 2>/dev/null || true

# Экспорт переменных окружения в файл, чтобы cron мог их видеть
declare -p | grep -E 'PATH|DB_PASS|DB_USER' > /container.env
chmod 644 /container.env

# Настройка cron
# Cron запускается от root, поэтому он сможет выполнить скрипты даже при строгих правах.
# Скрипты берут пароль из $DB_PASS (переменной окружения), а не из файлов.
rm -f /etc/cron.d/pingit
cat > /etc/cron.d/pingit <<EOF
SHELL=/bin/bash
BASH_ENV=/container.env
*/5 * * * * root ( cd /var/www/html/modules/pingit/ && ./run-modules.sh ) >> /var/log/pingit.log 2>&1
EOF
chmod 644 /etc/cron.d/pingit

# Запуск cron
cron

# Запуск Apache в foreground (обязательно для работы контейнера)
exec apache2-foreground
