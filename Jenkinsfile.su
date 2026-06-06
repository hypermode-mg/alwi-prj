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
    }

    stages {
        stage('Pre-Cleanup') {
            steps {
                script {
                    sh '''
                        # Останавливаем и удаляем ТОЛЬКО контейнер приложения, если он есть
                        docker stop alwi-php || true
                        docker rm alwi-php || true

                        # Удаляем всю папку web целиком, чтобы гарантированно обойти Permission denied.
                        # Права будут восстановлены корректно через git + docker-entrypoint.sh
                        if [ -d "web" ]; then
                            sudo rm -rf web
                            echo "Directory 'web' removed completely to avoid permission issues."
                        fi
                        
                        # Создаем пустую директорию для последующих операций
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
                            > $ENV_FILE
                            echo "TZ=${TZ}" >> $ENV_FILE
                            echo "DB_ROOT_PASS=${ROOT_PASSWORD}" >> $ENV_FILE
                            echo "DB_USER=${DB_USER}" >> $ENV_FILE
                            echo "DB_PASS=${DB_PASS}" >> $ENV_FILE
                            echo "DB_NAME=${DB_NAME}" >> $ENV_FILE

                            # Сразу пишем готовый конфиг БД
                            mkdir -p web/conf
                            cat > $DB_CONFIG_FILE <<EOF
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

                            # Корректируем место поиска libphp
                            sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"

                            # Корректируем значения переменных для второго шага установки
                            sed -i "s|value=\"hpinger\"|value=\"${DB_NAME}\"|g" "$TARGET_FILE2"
                            sed -i "s|value=\"localhost\"|value=\"${DB_HOST}\"|g" "$TARGET_FILE2"
                            sed -i "s|value=\"pass\"|value=\"${DB_PASS}\"|g" "$TARGET_FILE2"

                            # Корректируем Perl‑файлы
                            for perl_file in "$TARGET_FILE3" "$TARGET_FILE4"; do
                                sed -i "s/my \\$host = \"localhost\"/my \\$host = \"${DB_HOST}\"/g" "$perl_file"
                                sed -i "s/my \\$db = \"hpinger\"/my \\$db = \"${DB_NAME}\"/g" "$perl_file"
                                sed -i "s/my \\$pass = \"pass\"/my \\$pass = \"${DB_PASS}\"/g" "$perl_file"
                            done

                            # Копируем скрипт запуска модулей
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

                    # Запускаем ТОЛЬКО сервис alwi-php
                    docker compose -f "${DOCKER_COMPOSE_FILE}" up -d --force-recreate alwi-php
                '''
            }
        }

        stage('Verify Deployment') {
            steps {
                script {
                    echo 'Waiting for web app to start...'
                    sh '''
                        attempt=0
                        max_attempts=6
                        while [ $attempt -lt $max_attempts ]; do
                            if curl -f http://localhost; then
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
                    docker logs alwi-php | grep -i "error\\|fail\\|exception\\|mysql\\|php\\|perl" || true
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
    }
}
