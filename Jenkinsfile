pipeline {
    // Агент — хост (Ubuntu), никаких docker-обёрток
    agent { label 'docker_agent' }

    environment {
        ENV_FILE = ".env"
        TARGET_FILE1 = "web/install/step1.php"
        TARGET_FILE2 = "web/install/step2.php"
        TARGET_FILE3 = "web/modules/pingit/pingit.pl"
        TARGET_FILE4 = "web/modules/pingit/fetch.pl"
        DB_CONFIG_FILE = "web/conf/db1780739515.5653.php"
        DOCKER_COMPOSE_FILE = "docker-compose.yml"
        DOCKER_IMAGE = "alwi-php:${BUILD_NUMBER}"
        DB_HOST = "alwi-db"
        DB_PORT = "3306"
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
                        # 1. Очистка web БЕЗ sudo.
                        # Если тут будет Permission denied — значит, файлы принадлежат root.
                        # Это лечится ОДИН РАЗ через SSH на хосте: chown -R jenkins:jenkins <путь_к_workspace>
                        if [ -d "web" ]; then
                            echo "Removing existing 'web' directory..."
                            rm -rf web
                        fi
                        mkdir -p web

                        # 2. Чистим локальный Docker-контейнер (если он есть)
                        docker stop alwi-php || true
                        docker rm alwi-php || true

                        # 3. Git
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
                            # Экспортируем переменные, чтобы безопасно использовать их в sed
                            export DB_NAME_VAL="${DB_NAME}"
                            export DB_HOST_VAL="${DB_HOST}"
                            export DB_PASS_VAL="${DB_PASS}"
                            export DB_USER_VAL="${DB_USER}"
                            export ROOT_PASS_VAL="${ROOT_PASSWORD}"

                            > $ENV_FILE
                            echo "TZ=${TZ}" >> $ENV_FILE
                            echo "DB_ROOT_PASS=${ROOT_PASS_VAL}" >> $ENV_FILE
                            echo "DB_USER=${DB_USER_VAL}" >> $ENV_FILE
                            echo "DB_PASS=${DB_PASS_VAL}" >> $ENV_FILE
                            echo "DB_NAME=${DB_NAME_VAL}" >> $ENV_FILE

                            mkdir -p web/conf
                            cat > $DB_CONFIG_FILE <<EOF
<?php return array (
  'enabled' => 1,
  'srvname' => 'SuperMonitoring',
  'db' => '${DB_NAME_VAL}',
  'user' => '${DB_USER_VAL}',
  'pass' => '${DB_PASS_VAL}',
  'address' => '${DB_HOST_VAL}',
  'srvdbtype' => '0',
);
?>
EOF

                            # Проверки на существование файлов
                            if [ ! -f "$TARGET_FILE1" ]; then echo "ERROR: $TARGET_FILE1 not found"; ls -la web/install/ 2>/dev/null; exit 1; fi
                            if [ ! -f "$TARGET_FILE2" ]; then echo "ERROR: $TARGET_FILE2 not found"; ls -la web/install/ 2>/dev/null; exit 1; fi

                            chmod u+w "$TARGET_FILE1" "$TARGET_FILE2" 2>/dev/null || true

                            sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"

                            # Безопасная подстановка переменных через разрыв кавычек
                            sed -i 's|value="hpinger"|value="'"${DB_NAME_VAL}"'" |g' "$TARGET_FILE2"
                            sed -i 's|value="localhost"|value="'"${DB_HOST_VAL}"'" |g' "$TARGET_FILE2"
                            sed -i 's|value="pass"|value="'"${DB_PASS_VAL}"'" |g' "$TARGET_FILE2"

                            echo "Step2.php updated successfully."
                            grep -n -E 'value="[^"]*"' "$TARGET_FILE2" | head -10

                            for perl_file in "$TARGET_FILE3" "$TARGET_FILE4"; do
                                if [ -f "$perl_file" ]; then
                                    chmod u+w "$perl_file" 2>/dev/null || true
                                    sed -i "s/my \\$host = \"localhost\"/my \\$host = \"${DB_HOST_VAL}\"/g" "$perl_file"
                                    sed -i "s/my \\$db = \"hpinger\"/my \\$db = \"${DB_NAME_VAL}\"/g" "$perl_file"
                                    sed -i "s/my \\$pass = \"pass\"/my \\$pass = \"${DB_PASS_VAL}\"/g" "$perl_file"
                                else
                                    echo "WARNING: Perl file not found: $perl_file (skipping)"
                                fi
                            done

                            cp run-modules.sh web/modules/pingit/
                            if [ $? -ne 0 ]; then echo "ERROR: Failed to copy run-modules.sh"; exit 1; fi
                            
                            echo "Configuration update complete."
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
                    echo 'Dockerfile found.'
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
                        docker logs alwi-php | grep -i "error\\\\|fail\\\\|exception\\\\|mysql\\\\|php\\\\|perl" || true
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
