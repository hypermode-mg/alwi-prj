#!/bin/bash

# Определяем все переменные
ENV_FILE=".env"
TARGET_FILE1="web/install/step1.php"
TARGET_FILE2="web/install/step2.php"
TARGET_FILE3="web/modules/pingit/pingit.pl"
TARGET_FILE4="web/modules/pingit/fetch.pl"

echo "DB_USER=mysql-user" > $ENV_FILE
echo "DB_NAME=alertsonwings" >> $ENV_FILE

while true; do
    # Запрос пароля для пользователя root
    while true; do
        read -sp "Введите пароль пользователя root для СУБД: " ROOT_PASSWORD
        echo
        # Проверка на пустой пароль
        if [[ -z "$ROOT_PASSWORD" ]]; then
            echo "Ошибка: Пароль пользователя не может быть пустым. Пожалуйста, введите пароль."
        else
            break # Выходим из внутреннего цикла, если пароль не пустой
        fi
    done

    # Запрос подтверждения пароля для пользователя root
    read -sp "Подтвердите пароль: " ROOT_PASSWORD2
    echo
    # Сравнение паролей пользователя root
    if [[ "$ROOT_PASSWORD" == "$ROOT_PASSWORD2" ]]; then
	echo "DB_ROOT_PASS=$ROOT_PASSWORD" >> $ENV_FILE
    	echo "Пароль пользователя root успешно сохранен в $ENV_FILE"
    break # Выходим из внешнего цикла после корректного ввода
    else
        echo "Пароли  не совпадают. Пожалуйста, попробуйте снова."
    fi
done

while true; do
    # Запрос пароля для пользователя mysql_user
    while true; do
        read -sp "Введите пароль пользователя mysql_user для БД: " USER_PASSWORD
        echo
        # Проверка на пустой пароль
        if [[ -z "$USER_PASSWORD" ]]; then
            echo "Ошибка: Пароль пользователя не может быть пустым. Пожалуйста, введите пароль."
        else
            break # Выходим из внутреннего цикла, если пароль не пустой
        fi
    done

    # Запрос подтверждения пароля для пользователя mysql-user
    read -sp "Подтвердите пароль: " USER_PASSWORD2
    echo
    # Сравнение паролей пользователя mysql-user
    if [[ "$USER_PASSWORD" == "$USER_PASSWORD2" ]]; then
	echo "DB_PASS=$USER_PASSWORD" >> $ENV_FILE
    	echo "Пароль пользователя mysql_user успешно сохранен в $ENV_FILE"
    break # Выходим из внешнего цикла после корректного ввода
    else
        echo "Пароли  не совпадают. Пожалуйста, попробуйте снова."
    fi
done

# Корректируем место поиска libphp в контейнере с apache2 для исправления ошибки
sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"

# Корректируем значения переменных для упрощения настройки на втором шаге
sed -i 's|value="hpinger"|value="alertsonwings"|g' "$TARGET_FILE2"
sed -i 's|value="localhost"|value="alwi-db"|g' "$TARGET_FILE2"
sed -i 's|value="pass"|value="Enter user password"|g' "$TARGET_FILE2"

# Корректируем файл с настройками БД для использования в perl-модулях
sed -i "s/my \$host = \"localhost\"/my \$host = \"alwi-db\"/g" "$TARGET_FILE3"
sed -i "s/my \$host = \"localhost\"/my \$host = \"alwi-db\"/g" "$TARGET_FILE4"
sed -i "s/my \$db = \"hpinger\"/my \$db = \"alertsonwings\"/g" "$TARGET_FILE3"
sed -i "s/my \$db = \"hpinger\"/my \$db = \"alertsonwings\"/g" "$TARGET_FILE4"
sed -i "s/my \$pass = \"pass\"/my \$pass = \$ENV\{DB_PASS\}/g" "$TARGET_FILE3"
sed -i "s/my \$pass = \"pass\"/my \$pass = \$ENV\{DB_PASS\}/g" "$TARGET_FILE4"

echo "Внесены все необходимые изменения в файлы $TARGET_FILE1, $TARGET_FILE2, $TARGET_FILE3 и $TARGET_FILE4"

unset ROOT_PASSWORD ROOT_PASSWORD2 USER_PASSWORD USER_PASSWORD2

# Копируем скрипт запуска модулей проверки состояния инфраструктуры в каталог с модулями
cp run-modules.sh web/modules/pingit/

docker compose up -d
