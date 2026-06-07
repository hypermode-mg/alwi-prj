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
    
    # --- 1. Генерируем .env файл ---
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

    chmod u+w "$TARGET_FILE1" "$TARGET_FILE2" "$TARGET_FILE3" "$TARGET_FILE4"

    # --- 3. Замены через sed (с безопасным экранированием) ---
    sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"
    sed -i 's|value="hpinger"|value="alertsonwings"|g' "$TARGET_FILE2"
    sed -i 's|value="localhost"|value="alwi-db"|g' "$TARGET_FILE2"
    sed -i 's|value="pass"|value="Enter user password"|g' "$TARGET_FILE2"

    echo "DEBUG: Patching DB vars. Host='${DB_HOST_VAL}'"
    
    # Экранируем спецсимволы на случай, если DB_HOST/DB_NAME их содержат
    HOST_ESC=$(printf '%s\n' "${DB_HOST_VAL}" | sed 's/[&/\]/\\&/g')
    DB_ESC=$(printf '%s\n' "${DB_NAME_VAL}" | sed 's/[&/\]/\\&/g')

    sed -i "s|my *\\$host *= *'localhost'|my \$host = '${HOST_ESC}'|g" "$TARGET_FILE3"
    sed -i "s|my *\\$host *= *'localhost'|my \$host = '${HOST_ESC}'|g" "$TARGET_FILE4"
    sed -i "s|my *\\$db *= *'hpinger'|my \$db = '${DB_ESC}'|g" "$TARGET_FILE3"
    sed -i "s|my *\\$db *= *'hpinger'|my \$db = '${DB_ESC}'|g" "$TARGET_FILE4"
    sed -i 's|my *\\$pass *= *['"'"']pass['"'"']|my $pass = $ENV{DB_PASS}|g' "$TARGET_FILE3"
    sed -i 's|my *\\$pass *= *['"'"']pass['"'"']|my $pass = $ENV{DB_PASS}|g' "$TARGET_FILE4"

    grep -n -E 'host|db|pass' "$TARGET_FILE3" || true
    grep -n -E 'host|db|pass' "$TARGET_FILE4" || true

    # --- Проверка и копирование run-modules.sh ---
    if [ ! -f run-modules.sh ]; then
        echo "ERROR: run-modules.sh not found in workspace!"
        exit 1
    fi
    cp run-modules.sh web/modules/pingit/
    
    # Даём права на выполнение скриптам
    find web/modules/pingit -type f \( -name '*.sh' -o -name '*.pl' \) -exec chmod +x {} \; || true

    # --- 4. Синхронизация прав ---
    export JENKINS_UID_VAL=${JENKINS_UID}
    export JENKINS_GID_VAL=${JENKINS_GID}
    echo "Setting ownership to UID ${JENKINS_UID_VAL}..."
    chown -R ${JENKINS_UID_VAL}:${JENKINS_GID_VAL} web/
    echo "Permissions fixed for UID ${JENKINS_UID_VAL}"
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

                        docker compose -f "${DOCKER_COMPOSE_FILE}" up -d --build --no-cache --force-recreate alwi-php
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
