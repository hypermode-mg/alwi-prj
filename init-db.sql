-- Создание базы данных (если не существует)
CREATE DATABASE IF NOT EXISTS alertsonwings CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Создание пользователя с доступом с любого хоста
CREATE USER IF NOT EXISTS 'mysql-user'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';

-- Выдача прав на базу данных
GRANT ALL PRIVILEGES ON alertsonwings.* TO 'mysql-user'@'localhost';

-- Применение изменений
FLUSH PRIVILEGES;
