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
        DB_NAME = "alertsonwings"
        TZ = "Asia/Yekaterinburg"
    }

    options {
        skipDefaultCheckout true
    }

    stages {
        stage('Pre-Cleanup & Checkout') {
            steps {
                script {
                    sh '''
                        echo "Cleaning workspace..."
                        
                        if [ -d "web" ]; then
                            echo "Removing existing 'web' directory..."
                            rm -rf web
                        fi
                        mkdir -p web

                        docker stop alwi-php || true
                        docker rm alwi-php || true

                        git config --global user.email "ci@jenkins.local"
                        git config --global user.name "Jenkins CI"
                        
                        echo "Fetching from remote..."
                        git fetch --all
                        
                        echo "Checking out main branch..."
                        git checkout -f origin/main
                        
                        echo "Workspace is clean and ready."
                    '''
                }
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
                            export DB_NAME_VAL="${DB_NAME}"
                            export DB_HOST_VAL="${DB_HOST}"
                            
                            # Создаем .env файл
                            > $ENV_FILE
                            echo "TZ=${TZ}" >> $ENV_FILE

                            # Создаем конфиг БД (PHP array)
                            mkdir -p web/conf
                            cat > $DB_CONFIG_FILE <<EOF
<?php return array (
  'enabled' => 1,
  'srvname' => 'SuperMonitoring',
  'db' => '${DB_NAME_VAL}',
  'user' => '${DB_USER}',
  'pass' => '${DB_PASS}',
  'address' => '${DB_HOST_VAL}',
  'srvdbtype' => '0',
);
?>
EOF

                            # Проверяем наличие файлов
                            if [ ! -f "$TARGET_FILE1" ]; then echo "ERROR: $TARGET_FILE1 not found"; ls -la web/install/ 2>/dev/null; exit 1; fi
                            if [ ! -f "$TARGET_FILE2" ]; then echo "ERROR: $TARGET_FILE2 not found"; ls -la web/install/ 2>/dev/null; exit 1; fi
                            if [ ! -f "$TARGET_FILE3" ]; then echo "ERROR: $TARGET_FILE3 not found"; ls -la web/modules/pingit/ 2>/dev/null; exit 1; fi
                            if [ ! -f "$TARGET_FILE4" ]; then echo "ERROR: $TARGET_FILE4 not found"; ls -la web/modules/pingit/ 2>/dev/null; exit 1; fi

                            chmod u+w "$TARGET_FILE1" "$TARGET_FILE2" "$TARGET_FILE3" "$TARGET_FILE4" 2>/dev/null || true

                            # --- ИСПРАВЛЕННЫЕ И НАДЕЖНЫЕ ЗАМЕНЫ ЧЕРЕЗ SED ---
                            # Используем разрыв кавычек для безопасной подстановки переменных

                            # Корректируем место поиска libphp в контейнере с apache2
                            sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"

                            # Корректируем значения переменных для упрощения настройки на втором шаге
                            sed -i 's|value="hpinger"|value="alertsonwings"|g' "$TARGET_FILE2"
                            sed -i 's|value="localhost"|value="alwi-db"|g' "$TARGET_FILE2"
                            sed -i 's|value="pass"|value="Enter user password"|g' "$TARGET_FILE2"

                            # Отладочный вывод переменных (чтобы убедиться, что они не пустые)
                            echo "DEBUG: DB_HOST_VAL='${DB_HOST_VAL}', DB_NAME_VAL='${DB_NAME_VAL}'"

                            # Замена host (универсальный шаблон: игнорирует лишние пробелы, работает с одинарными/двойными кавычками)
                            sed -i 's/my *$host *= *["'\'']localhost["'\'']/my $host = "'${DB_HOST_VAL}'"/g' "$TARGET_FILE3"
                            sed -i 's/my *$host *= *["'\'']localhost["'\'']/my $host = "'${DB_HOST_VAL}'"/g' "$TARGET_FILE4"
                            
                            # Замена db (универсальный шаблон)
                            sed -i 's/my *$db *= *["'\'']hpinger["'\'']/my $db = "'${DB_NAME_VAL}'"/g' "$TARGET_FILE3"
                            sed -i 's/my *$db *= *["'\'']hpinger["'\'']/my $db = "'${DB_NAME_VAL}'"/g' "$TARGET_FILE4"
                            
                            # Замена пароля на переменную окружения для Perl
                            # Вставляем текст $ENV{DB_PASS}, который Perl потом прочитает из окружения
                            sed -i 's/my $pass = "pass"/my $pass = $ENV{DB_PASS}/g' "$TARGET_FILE3"
                            sed -i 's/my $pass = "pass"/my $pass = $ENV{DB_PASS}/g' "$TARGET_FILE4"

                            # Проверка результата замен
                            echo "--- Result in $TARGET_FILE3 ---"
                            grep -n -E 'host|db|pass' "$TARGET_FILE3" || echo "No matches found in $TARGET_FILE3"
                            echo "--- Result in $TARGET_FILE4 ---"
                            grep -n -E 'host|db|pass' "$TARGET_FILE4" || echo "No matches found in $TARGET_FILE4"

                            # Копируем run-modules.sh
                            cp run-modules.sh web/modules/pingit/
                            if [ $? -ne 0 ]; then echo "ERROR: Failed to copy run-modules.sh"; exit 1; fi
                        '''
                    }
                }
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    if (!fileExists('Dockerfile')) {
                        error 'Dockerfile not found.'
                    }
                    echo 'Dockerfile found. Building image...'
                    // Если нужен явный билд, раскомментируйте строку ниже:
                    // sh "docker build -t ${DOCKER_IMAGE} ."
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
                    
                    # Передаем переменные окружения в compose
                    export DB_PASS="${DB_PASS}"
                    export DB_USER="${DB_USER}"
                    
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
                                    echo "Failed to reach web page."
                                    exit 1
                                fi
                                sleep 10
                            fi
                        done
                        docker ps --filter "name=alwi-php"
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
                echo 'Cleaning up application container...'
                sh '''
                    docker stop alwi-php || true
                    docker rm alwi-php || true
                '''
            }
        }
    }
}
