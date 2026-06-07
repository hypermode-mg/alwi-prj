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
        DB_HOST = "alwi-db"
        DB_NAME = "alertsonwings"
        TZ = "Asia/Yekaterinburg"
        JENKINS_UID = '999'
        JENKINS_GID = '987'
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
                        
                        docker stop alwi-php || true
                        docker rm alwi-php || true
                        
                        rm -rf web
                        mkdir -p web

                        git config --global user.email "ci@jenkins.local"
                        git config --global user.name "Jenkins CI"
                        
                        git fetch --all
                        git checkout -f origin/main
                        
                        echo "Workspace ready."
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
                        
                        # --- 1. Генерируем .env ---
                        > $ENV_FILE
                        echo "TZ=${TZ}" >> $ENV_FILE
                        echo "DB_USER=${DB_USER}" >> $ENV_FILE
                        echo "DB_NAME=${DB_NAME_VAL}" >> $ENV_FILE
                        echo "DB_ROOT_PASS=${ROOT_PASSWORD}" >> $ENV_FILE
                        echo "DB_PASS=${DB_PASS}" >> $ENV_FILE
                        chmod 600 $ENV_FILE

                        # --- 2. Генерируем PHP конфиг БД ---
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

                        # --- ЖЁСТКАЯ ПРОВЕРКА ПУТЕЙ И СУЩЕСТВОВАНИЯ ---
                        for f in "$TARGET_FILE1" "$TARGET_FILE2" "$TARGET_FILE3" "$TARGET_FILE4"; do
                            if [ ! -f "$f" ]; then
                                echo "CRITICAL: File not found: $f"
                                ls -la $(dirname "$f") || true
                                exit 1
                            fi
                        done

                        chmod u+w "$TARGET_FILE1" "$TARGET_FILE2" "$TARGET_FILE3" "$TARGET_FILE4"

                        # --- ОТЛАДКА: выводим полные пути и первые строки файлов ---
                        echo "=== DEBUG: Files to patch ==="
                        echo "TARGET_FILE3: $TARGET_FILE3"
                        head -n 10 "$TARGET_FILE3" || true
                        echo "TARGET_FILE4: $TARGET_FILE4"
                        head -n 10 "$TARGET_FILE4" || true

                        # --- Замены для PHP/HTML ---
                        sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"
                        sed -i 's|value="hpinger"|value="alertsonwings"|g' "$TARGET_FILE2"
                        sed -i 's|value="localhost"|value="alwi-db"|g' "$TARGET_FILE2"
                        sed -i 's|value="pass"|value="Enter user password"|g' "$TARGET_FILE2"

                        # --- СУПЕР-ПРОЗРАЧНЫЕ ЗАМЕНЫ ЧЕРЕЗ BASH (без сложных экранирований) ---
                        # Создаём временные файлы с нужными строками
                        tmp_host="my \$host = \"${DB_HOST_VAL}\";"
                        tmp_db="my \$db = \"${DB_NAME_VAL}\";"
                        tmp_pass="my \$pass = \$ENV{DB_PASS};"

                        for file in "$TARGET_FILE3" "$TARGET_FILE4"; do
                            # Делаем бэкап
                            cp "$file" "${file}.bak"

                            # Читаем построчно и заменяем только нужные строки
                            awk -v h="$tmp_host" -v d="$tmp_db" -v p="$tmp_pass" '
                            {
                                if ($0 ~ /my[[:space:]]*\\$host[[:space:]]*=[[:space:]]*["][^"]*["]/) {
                                    print h; next;
                                }
                                if ($0 ~ /my[[:space:]]*\\$db[[:space:]]*=[[:space:]]*["][^"]*["]/) {
                                    print d; next;
                                }
                                if ($0 ~ /my[[:space:]]*\\$pass[[:space:]]*=[[:space:]]*["][^"]*["]/) {
                                    print p; next;
                                }
                                print $0
                            }' "$file" > "${file}.patched"

                            # Проверяем, что патч реально что-то изменил
                            if diff -q "$file" "${file}.patched" > /dev/null 2>&1; then
                                echo "WARNING: No changes detected in $file after awk patch!"
                                head -n 20 "$file" || true
                            else
                                mv "${file}.patched" "$file"
                                echo "OK: Patched $file"
                            fi
                        done

                        # --- ОТЛАДКА ПОСЛЕ ---
                        echo "=== DEBUG: After patch ==="
                        grep -n -E 'host|db|pass' "$TARGET_FILE3" || true
                        grep -n -E 'host|db|pass' "$TARGET_FILE4" || true

                        # --- run-modules.sh ---
                        if [ ! -f run-modules.sh ]; then
                            echo "ERROR: run-modules.sh not found!"
                            exit 1
                        fi
                        cp run-modules.sh web/modules/pingit/
                        find web/modules/pingit -type f \\( -name '*.sh' -o -name '*.pl' \\) -exec chmod +x {} \\; || true

                        # --- Права ---
                        export JENKINS_UID_VAL=${JENKINS_UID}
                        export JENKINS_GID_VAL=${JENKINS_GID}
                        chown -R ${JENKINS_UID_VAL}:${JENKINS_GID_VAL} web/
'''
                    }
                }
            }
        }


        stage('Deploy alwi-php only') {
            steps {
                script {
                    sh '''
                        export DB_PASS="${DB_PASS}"
                        export DB_USER="${DB_USER}"
                        export DB_ROOT_PASSWORD="${ROOT_PASSWORD}"
                        export DB_NAME="${DB_NAME}"
                        
                        export JENKINS_UID=${JENKINS_UID}
                        export JENKINS_GID=${JENKINS_GID}
                
                        echo "Deploying alwi-php ONLY (UID=${JENKINS_UID})..."

                        docker compose -f "${DOCKER_COMPOSE_FILE}" up -d --build --force-recreate alwi-php
                    '''
                }
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
                            if curl -f http://localhost; then
                                echo "Web page is accessible."
                                break
                            else
                                attempt=$((attempt + 1))
                                [ $attempt -eq $max_attempts ] && { echo "Timeout waiting for app."; exit 1; }
                                sleep 10
                            fi
                        done
                        docker ps --filter "name=alwi-php" --filter "status=running"
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
                sh '''
                    docker stop alwi-php || true
                    docker rm alwi-php || true
                '''
            }
        }
    }
}
