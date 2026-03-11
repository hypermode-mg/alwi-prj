*** DISCLAIMER ***
Этот проект для демонстрации принципов и инструментов DevOps
основан на проекте AlertsOnWings от kerrytru
ссылка на проект: https://sourceforge.net/projects/screen-ping/
с доработками камрада monitoringusman, за что ему огромный
респект и уважуха

Для запуска проекта вам понадобится сервер с docker и docker-compose
1. Клонируйте на него репозиторий и создайте в корне 3 файла:
db_root_password.txt - в него напишите пароль для пользователя root в СУБД
db_password.txt - в него запишите пароль для пользователя mysql-user
.env - в нём должна быть одна строчка DB_PASSWORD=пароль_пользователя

2. Выполните docker compose up -d для сборки и запуска проекта.