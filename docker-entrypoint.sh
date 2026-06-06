#!/bin/bash
set -e

# -----------------------------------------------------------------------------
# 1. Настройка прав доступа
# -----------------------------------------------------------------------------
# Даем владельцу (jenkins/root) полные права, остальным — только чтение.
# Запись для "других" запрещена (go-w), чтобы случайно не испортить файлы извне.
# Это безопасно, так как Cron работает от root и игнорирует эти ограничения.
chmod -R u+rwX,go+rX,go-w /var/www/html

# Убеждаемся, что логи и временные директории доступны
mkdir -p /var/log/apache2 /var/run/apache2
chmod 755 /var/log/apache2 /var/run/apache2


# -----------------------------------------------------------------------------
# 2. Защита скриптов от прямого доступа через веб (CRITICAL)
# -----------------------------------------------------------------------------
# Включаем конфиг, созданный в Dockerfile, который запрещает Apache отдавать
# файлы из /var/www/html/modules/pingit по HTTP (возвращает 403 Forbidden).
# Даже если в скриптах нет паролей, это скрывает логику работы системы.
echo "Enabling Apache config to restrict access to /modules/pingit..."
a2enconf restrict-pingit || {
    echo "WARNING: Could not enable restrict-pingit.conf (might be already enabled)."
}

# Проверка наличия симлинка (для отладки, можно убрать в продакшене)
if [ ! -L /etc/apache2/conf-enabled/restrict-pingit.conf ]; then
    echo "ERROR: restrict-pingit.conf is NOT enabled! Web server might expose scripts."
    # Не выходим с ошибкой жестко, чтобы не ломать старт, но логируем проблему
fi


# -----------------------------------------------------------------------------
# 3. Настройка Cron для запуска модулей
# -----------------------------------------------------------------------------
# Очищаем старый крон для этого сервиса, чтобы не дублировать задачи при рестарте
(crontab -l 2>/dev/null | grep -v "run-modules.sh") > /tmp/cron.new || true

# Добавляем задачу: запускать каждые 5 минут
# Важно: делаем cd в папку со скриптами, чтобы относительные пути внутри работали корректно
echo "*/5 * * * * root ( cd /var/www/html/modules/pingit && ./run-modules.sh ) >> /var/log/pingit.log 2>&1" >> /tmp/cron.new

# Обновляем крон
crontab /tmp/cron.new
rm /tmp/cron.new

echo "Cron job installed successfully."


# -----------------------------------------------------------------------------
# 4. Финальные проверки и запуск
# -----------------------------------------------------------------------------
echo "Checking required files..."
if [ ! -f /var/www/html/modules/pingit/run-modules.sh ]; then
    echo "ERROR: run-modules.sh not found in expected location!"
    exit 1
fi
if [ ! -f /var/www/html/modules/pingit/pingit.pl ]; then
    echo "ERROR: pingit.pl not found in expected location!"
    exit 1
fi

# Даем права на выполнение скриптам (на всякий случай, если Jenkins скопировал без +x)
chmod +x /var/www/html/modules/pingit/*.sh
chmod +x /var/www/html/modules/pingit/*.pl

echo "Starting Apache..."
# exec передает управление процессу apache2, чтобы контейнер жил пока жив веб-сервер
exec apache2-foreground
