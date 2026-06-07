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

                        # --- ПРОВЕРКА СУЩЕСТВОВАНИЯ ФАЙЛОВ ---
                        for f in "$TARGET_FILE1" "$TARGET_FILE2" "$TARGET_FILE3" "$TARGET_FILE4"; do
                            if [ ! -f "$f" ]; then
                                echo "CRITICAL: File not found: $f"
                                ls -la $(dirname "$f") || true
                                exit 1
                            fi
                        done

                        chmod u+w "$TARGET_FILE1" "$TARGET_FILE2" "$TARGET_FILE3" "$TARGET_FILE4"

                        # --- ОТЛАДКА: покажи, что реально лежит в файлах ---
                        echo "=== DEBUG: Before patch (fetch.pl) ==="
                        grep -n -E 'host|db|pass' "$TARGET_FILE3" || true
                        echo "=== DEBUG: Before patch (pingit.pl) ==="
                        grep -n -E 'host|db|pass' "$TARGET_FILE4" || true

                        # --- Замены для PHP/HTML ---
                        sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"
                        sed -i 's|value="hpinger"|value="alertsonwings"|g' "$TARGET_FILE2"
                        sed -i 's|value="localhost"|value="alwi-db"|g' "$TARGET_FILE2"
                        sed -i 's|value="pass"|value="Enter user password"|g' "$TARGET_FILE2"

                        # --- ПРЯМЫЕ ЗАМЕНЫ ПО ТОЧНОМУ ТЕКСТУ (самый надёжный способ) ---
                        # Формируем строки с нужным форматированием
                        NEW_HOST="my \$host = \"${DB_HOST_VAL}\"; #"
                        NEW_DB="my \$db = \"${DB_NAME_VAL}\"; #"
                        NEW_PASS="my \$pass = \$ENV{DB_PASS}; #"

                        # Делаем бэкапы
                        cp "$TARGET_FILE3" "${TARGET_FILE3}.bak"
                        cp "$TARGET_FILE4" "${TARGET_FILE4}.bak"

                        # Заменяем точные строки через sed -F (fixed strings), без regex!
                        # -F говорит sed: "воспринимай шаблон как обычный текст, не как regex"
                        sed -i -F 'my $host = "localhost"; #' "$NEW_HOST" "$TARGET_FILE3" "$TARGET_FILE4" 2>/dev/null || true
                        sed -i -F 'my $db = "hpinger"; #' "$NEW_DB" "$TARGET_FILE3" "$TARGET_FILE4" 2>/dev/null || true
                        sed -i -F 'my $pass = "pass"; #' "$NEW_PASS" "$TARGET_FILE3" "$TARGET_FILE4" 2>/dev/null || true

                        # ВАЖНО: если sed -F не поддерживается в вашей версии sed (старый GNU),
                        # используем безопасный fallback через временный файл и grep-like замену:
                        if [ $? -ne 0 ] || [ ! -s "${TARGET_FILE3}" ]; then
                            echo "WARNING: sed -F not supported or failed, using fallback..."
                            for file in "$TARGET_FILE3" "$TARGET_FILE4"; do
                                cp "$file" "${file}.tmp"
                                # Читаем построчно и делаем прямую замену известных строк
                                while IFS= read -r line; do
                                    case "$line" in
                                        'my $host = "localhost"; #') echo "$NEW_HOST";;
                                        'my $db = "hpinger"; #')   echo "$NEW_DB";;
                                        'my $pass = "pass"; #')    echo "$NEW_PASS";;
                                        *) echo "$line";;
                                    esac
                                done < "${file}.tmp" > "$file"
                                rm "${file}.tmp"
                            done
                        fi

                        # --- ОТЛАДКА ПОСЛЕ ---
                        echo "=== DEBUG: After patch (fetch.pl) ==="
                        grep -n -E 'host|db|pass' "$TARGET_FILE3" || true
                        echo "=== DEBUG: After patch (pingit.pl) ==="
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
