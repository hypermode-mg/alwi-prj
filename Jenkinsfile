pipeline {
    agent { label 'docker_agent' }

    environment {
        ENV_FILE = ".env"
        TARGET_FILE1 = "web/install/step1.php"
        TARGET_FILE2 = "web/install/step2.php"
        TARGET_FILE3 = "web/modules/pingit/pingit.pl"
        TARGET_FILE4 = "web/modules/pingit/fetch.pl"
        DB_CONFIG_FILE = "web/conf/db1776658531.371.php"
        DOCKER_COMPOSE_FILE = "docker-compose.yml"
        DOCKER_IMAGE = "alwi-php:${BUILD_NUMBER}"
        DB_HOST = "alwi-db"
        DB_PORT = "3306"
        DB_NAME = "alertsonwings"
        TZ = "Asia/Yekaterinburg"
        REPO_URL = "https://github.com/hypermode-mg/alwi-prj"
        // Важно: маппим DB_PASS (из credentials) в DB_PASSWORD для docker-compose.yml
        DB_PASSWORD = "${DB_PASS}" 
    }

    stages {
        stage('Pre-Cleanup') {
            steps {
                script {
                    sh '''
                        # Останавливаем и удаляем ТОЛЬКО контейнер приложения
                        docker stop alwi-php || true
                        docker rm alwi-php || true

                        # Удаляем папку web (Jenkins имеет права на workspace)
                        if [ -d "web" ]; then
                            rm -rf web
                            echo "Directory 'web' removed."
                        fi
                        
                        mkdir -p web
                        echo "Empty 'web' directory prepared."
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
                    )
                ]) {
                    script {
                        sh '''
                            # Создаём .env файл
                            > "$ENV_FILE"
                            printf "TZ=%s\n" "${TZ}" >> "$ENV_FILE"
                            printf "DB_ROOT_PASS=%s\n" "${ROOT_PASSWORD}" >> "$ENV_FILE"
                            printf "DB_USER=%s\n" "${DB_USER}" >> "$ENV_FILE"
                            printf "DB_PASSWORD=%s\n" "${DB_PASSWORD}" >> "$ENV_FILE"
                            printf "DB_NAME=%s\n" "${DB_NAME}" >> "$ENV_FILE"

                            # Готовим директорию и конфиг БД с подстановкой переменных
                            mkdir -p web/conf
                            cat > "$DB_CONFIG_FILE" <<EOF
<?php return array (
  'enabled' => 1,
  'srvname' => 'SuperMonitoring',
  'db' => '${DB_NAME}',
  'user' => '${DB_USER}',
  'pass' => '${DB_PASSWORD}',
  'address' => '${DB_HOST}',
  'srvdbtype' => '0',
);
?>
EOF

                            # Корректируем путь к libphp (только если файл существует)
                            if [ -f "$TARGET_FILE1" ]; then
                                sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"
                            fi

                            # Обновляем step2.php
                            if [ -f "$TARGET_FILE2" ]; then
                                sed -i "s|value=\"hpinger\"|value=\"${DB_NAME}\"|g" "$TARGET_FILE2"
                                sed -i "s|value=\"localhost\"|value=\"${DB_HOST}\"|g" "$TARGET_FILE2"
                                # Меняем только в явных атрибутах value, чтобы не затронуть другой код
                                sed -i "s|value=\"pass\"|value=\"${DB_PASSWORD}\"|g" "$TARGET_FILE2"
                            fi

                            # Обновляем Perl-файлы (только явные строки подключения)
                            for perl_file in "$TARGET_FILE3" "$TARGET_FILE4"; do
                                if [ -f "$perl_file" ]; then
                                    sed -i "s/my \\$host = \"localhost\"/my \\$host = \"${DB_HOST}\"/g" "$perl_file"
                                    sed -i "s/my \\$db = \"hpinger\"/my \\$db = \"${DB_NAME}\"/g" "$perl_file"
                                    sed -i "s/my \\$pass = \"pass\"/my \\$pass = \"${DB_PASSWORD}\"/g" "$perl_file"
                                fi
                            done

                            # Копируем run-modules.sh в нужное место (на случай, если git его не кладёт туда)
                            if [ -f "run-modules.sh" ]; then
                                mkdir -p web/modules/pingit
                                cp run-modules.sh web/modules/pingit/
                            else
                                echo "WARN: run-modules.sh not found, skipping copy."
                            fi

                            echo "Configuration files updated with DB credentials"
                        '''
                    }
                }
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    if (!fileExists('Dockerfile')) {
                        error 'Dockerfile not found in workspace. Cannot build image.'
                    }
                    echo 'Dockerfile found; image will be built by docker compose.'
                }
            }
        }

        stage('Deploy via Docker Compose') {
            steps {
                sh '''
                    if [ ! -f "${DOCKER_COMPOSE_FILE}" ]; then
                        echo "ERROR: ${DOCKER_COMPOSE_FILE} not found."
                        exit 1
                    fi

                    # Запускаем ТОЛЬКО сервис alwi-php, не трогая БД.
                    # alwi-php будет ждать, пока alwi-db не станет healthy (это ожидаемо).
                    docker compose -f "${DOCKER_COMPOSE_FILE}" up -d --force-recreate alwi-php

                    # Проверяем статус сервисов
                    docker ps --filter "name=alwi-php" --filter "status=running"
                    docker ps --filter "name=alwi-db" --filter "status=running" || echo "WARNING: alwi-db not found or not running"
                '''
            }
        }

        stage('Verify Deployment') {
            steps {
                script {
                    echo 'Waiting for web app to start...'
                    sh '''
                        attempt=0
                        max_attempts=15
                        while [ $attempt -lt $max_attempts ]; do
                            if curl -f -m 5 http://localhost; then
                                echo "Web page is accessible."
                                break
                            else
                                attempt=$((attempt + 1))
                                if [ $attempt -eq $max_attempts ]; then
                                    echo "Failed to reach web page after ${max_attempts} attempts."
                                    exit 1
                                fi
                                sleep 10
                            fi
                        done
                    '''

                    docker ps --filter "name=alwi-php"
                    docker logs alwi-php 2>&1 | grep -i "error\\|fail\\|exception\\|mysql\\|php\\|perl" || true
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
                echo 'Cleaning up application container only (DB is kept running)...'
                sh '''
                    docker stop alwi-php || true
                    docker rm alwi-php || true
                '''
            }
        }
        always {
            // При необходимости добавь отправку уведомлений
        }
    }
}
