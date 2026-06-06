pipeline {
    agent { label 'docker_agent' }


    environment {
        ENV_FILE=".env"
        TARGET_FILE1="web/install/step1.php"
        TARGET_FILE2="web/install/step2.php"
        TARGET_FILE3="web/modules/pingit/pingit.pl"
        TARGET_FILE4="web/modules/pingit/fetch.pl"
        DB_CONFIG_FILE="web/conf/db1776658531.371.php"
        DOCKER_IMAGE = 'alwi-php:${BUILD_NUMBER}'
        DB_HOST = 'alwi-db'
        DB_PORT = '3306'
        DB_NAME = 'alertsonwings'
        TZ = 'Asia/Yekaterinburg'
        REPO_URL = 'https://github.com/hypermode-mg/alwi-prj'
    }

        stage('Pre-Cleanup') {
            steps {
                script {
                    sh '''
                        # Останавливаем и удаляем существующий контейнер приложения, если есть
                        docker stop alwi-php || true
                        docker rm alwi-php || true
                        # Очищаем каталог web с правами root
                        sudo rm -rf web/*
                        echo "Directory 'web' cleaned successfully."
                    '''
                }
            }
        }

        stage('Checkout Repository') {
            steps {
                git(
                    url: "${REPO_URL}",
                    branch: 'main'
                )
            }
        }

        stage('Modify App Configuration') {
            steps {
                withCredentials([
                    usernamePassword(
                credentialsId: 'db-app-credentials',
                usernameVariable: 'DB_USER',
                passwordVariable: 'DB_PASS'
            ),
            usernamePassword(
                credentialsId: 'db-root-credentials',
                usernameVariable: 'ROOT_USER',
                passwordVariable: 'ROOT_PASSWORD'
            )]) {
                script {
                    sh '''
                # Создаём .env файл с учётными данными БД
                echo "DB_USER=${DB_USER}" > $ENV_FILE
                echo "DB_NAME=${DB_NAME}" >> $ENV_FILE
                echo "DB_PASS=${DB_PASS}" >> $ENV_FILE
                echo "DB_ROOT_PASS=${ROOT_PASSWORD}" >> $ENV_FILE

                # Создаём файл конфигурации БД web/conf/db1776658531.371.php
                mkdir -p web/conf  # создаём директорию, если её нет
                cat > $DB_CONFIG_FILE << EOF
<?php return array (
  'enabled' => 1,
  'srvname' => 'SuperMonitoring',
  'db' => '${DB_NAME}',
  'user' => '${DB_USER}',
  'pass' => '${DB_PASS}',
  'address' => '${DB_HOST}',
  'srvdbtype' => '0',
);
?>
EOF

                echo "Создан файл конфигурации базы данных: $DB_CONFIG_FILE"

                # Корректируем место поиска libphp в контейнере с apache2 для исправления ошибки
                sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"

                # Корректируем значения переменных для упрощения настройки на втором шаге
                sed -i 's|value="hpinger"|value="${DB_NAME}"|g' "$TARGET_FILE2"
                sed -i 's|value="localhost"|value="${DB_HOST}"|g' "$TARGET_FILE2"
                sed -i 's|value="pass"|value="${DB_PASS}"|g' "$TARGET_FILE2"

                # Корректируем файл с настройками БД для использования в perl-модулях
                sed -i "s/my \$host = \\"localhost\\"/my \$host = \\"${DB_HOST}\\"/g" "$TARGET_FILE3"
                sed -i "s/my \$host = \\"localhost\\"/my \$host = \\"${DB_HOST}\\"/g" "$TARGET_FILE4"
                sed -i "s/my \$db = \\"hpinger\\"/my \$db = \\"${DB_NAME}\\"/g" "$TARGET_FILE3"
                sed -i "s/my \$db = \\"hpinger\\"/my \$db = \\"${DB_NAME}\\"/g" "$TARGET_FILE4"
                sed -i "s/my \$pass = \\"pass\\"/my \$pass = \\"${DB_PASS}\\"/g" "$TARGET_FILE3"
                sed -i "s/my \$pass = \\"pass\\"/my \$pass = \\"${DB_PASS}\\"/g" "$TARGET_FILE4"

                # Копируем скрипт запуска модулей проверки состояния инфраструктуры в каталог с модулями
                cp run-modules.sh web/modules/pingit/

                echo "Configuration files updated with DB credentials"
            '''
                }
            }
        }
    }

        stage('Build Docker Image') {
            steps {
                script {
                    docker.build("${DOCKER_IMAGE}")
                }
            }
        }

        stage('Deploy App Container') {
            steps {
                withCredentials([usernamePassword(
                    credentialsId: 'db-app-credentials',
            usernameVariable: 'DB_USER',
            passwordVariable: 'DB_PASS'
        )]) {
            sh '''
                # Запускаем контейнер приложения
                docker run -d \\
                  --name alwi-php \\
                  -p 80:80 \\
                  --network alwi-net \\
                  -e TZ=${TZ} \\
                  -e DB_PASS=${DB_PASS} \\
                  ${DOCKER_IMAGE}
            '''
        }
    }
}

        stage('Verify Deployment') {
            steps {
                script {
                    echo 'Waiting for app to start...'
                    sleep time: 30, unit: 'SECONDS'

            sh '''
                # Проверяем статус контейнера
                docker ps --filter "name=alwi-php"

                # Проверяем логи на ошибки подключения к БД
                docker logs alwi-php | grep -i "error\\|fail\\|exception\\|mysql\\|php\\|perl" || true

                # Проверка доступности веб‑страницы
                curl -f http://localhost || exit 1
            '''
                }
            }
        }
    }

    post {
        success {
            echo 'Deployment successful!'
        }
        failure {
            echo 'Deployment failed!'
            script {
                echo 'Cleaning up...'
                sh '''
            docker stop alwi-php || true
            docker rm alwi-php || true
                '''
            }
        }
    }
}