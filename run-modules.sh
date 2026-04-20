#!/bin/bash

# Файл блокировки повторного запуска
LOCK_FILE="/tmp/run_perl_scripts.lock"

# Проверка, запущен ли уже скрипт
if [ -f "$LOCK_FILE" ]; then
    echo "Ошибка: скрипт уже запущен (файл блокировки существует: $LOCK_FILE)"
    exit 1
fi

# Создаём файл блокировки
touch "$LOCK_FILE"

# Функция очистки при завершении (включая аварийное)
cleanup() {
    rm -f "$LOCK_FILE"
    echo "Файл блокировки удалён."
}

# Регистрируем очистку при выходе
trap cleanup EXIT

echo "Запуск Perl-модулей проверки"
cd /var/www/html/modules/pingit

# Запускаем pingit.pl
echo "Запускаем pingit.pl"
perl pingit.pl

# Запускаем fetch.pl
echo "Запускаем fetch.pl..."
perl fetch.pl