#!/bin/bash
set -e

echo "Starting entrypoint script..."

# -----------------------------------------------------------------------------
# ДОБАВЛЕНО: Экспорт переменных окружения в файл
# -----------------------------------------------------------------------------
declare -p | grep -E 'PATH|DB_*' > /container.env
chmod 644 /container.env
echo "Environment variables exported to /container.env"

# -----------------------------------------------------------------------------
# 1. Настройка прав доступа (для Варианта А)
# -----------------------------------------------------------------------------
# Так как в Dockerfile мы сделали usermod -u <JENKINS_UID> www-data,
# владелец файлов на хосте (jenkins) и внутри контейнера (www-data) теперь один и тот же (по UID).
# Унифицируем права, чтобы не было сюрпризов от Git/Jenkins.
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;

# Убеждаемся, что логи и временные директории доступны
mkdir -p /var/log/apache2 /var/run/apache2
chmod 755 /var/log/apache2 /var/run/apache2


# -----------------------------------------------------------------------------
# 2. Защита скриптов от прямого доступа через веб
# -----------------------------------------------------------------------------
echo "Enabling Apache config to restrict access to /modules/pingit..."
a2enconf restrict-pingit || {
    echo "WARNING: Could not enable restrict-pingit.conf"
}


# -----------------------------------------------------------------------------
# 3. Настройка Cron
# -----------------------------------------------------------------------------
# Очищаем старый крон для этого сервиса, чтобы не дублировать задачи
(crontab -l 2>/dev/null | grep -v "run-modules.sh") > /tmp/cron.new || true

# Добавляем задачу: запускать каждые 5 минут
# cd важен, чтобы относительные пути внутри скрипта работали корректно
echo "*/5 * * * * root ( cd /var/www/html/modules/pingit && ./run-modules.sh ) >> /var/log/pingit.log 2>&1" >> /tmp/cron.new

# Обновляем крон
crontab /tmp/cron.new
rm /tmp/cron.new

# ЗАПУСК ДЕМОНА CRON (критически важно: в минималистичных образах он не стартует сам)
if command -v service >/dev/null 2>&1; then
    service cron start
else
    cron
fi
echo "Cron daemon started."


# -----------------------------------------------------------------------------
# 4. Финальные проверки и права на выполнение скриптов
# -----------------------------------------------------------------------------
if [ ! -f /var/www/html/modules/pingit/run-modules.sh ]; then
    echo "ERROR: run-modules.sh not found in expected location!"
    exit 1
fi
if [ ! -f /var/www/html/modules/pingit/pingit.pl ]; then
    echo "ERROR: pingit.pl not found in expected location!"
    exit 1
fi

# Гарантируем бит выполнения (x) для скриптов, так как Git/Jenkins могут его сбросить
chmod +x /var/www/html/modules/pingit/*.sh
chmod +x /var/www/html/modules/pingit/*.pl

echo "Starting Apache..."
exec apache2-foreground
