pipeline {
    agent { label 'test_agent' }

    environment {
        ENV_FILE = ".env"
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
#                        git fetch --all
                        git checkout -f origin/devel
                        git pull origin devel
                        echo "Workspace ready."
                    '''
                }
            }
        }
        
        stage('Prepare App Configuration & Secrets') {
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
                            # 1. Создаем чистый .env файл с нужными переменными
                            cat > "$ENV_FILE" <<EOF
TZ=${TZ}
DB_HOST=${DB_HOST}
DB_USER=${DB_USER}
DB_NAME=${DB_NAME}
DB_ROOT_PASS=${ROOT_PASSWORD}
DB_PASS=${DB_PASS}
EOF
                            chmod 600 "$ENV_FILE"
                            
                            # 2. Копируем конфиги приложения
                            cp web/config.php.default web/config.php
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

                            # 3. Копирование run-modules.sh и права доступа
                            if [ ! -f run-modules.sh ]; then
                                echo "ERROR: run-modules.sh not found!"
                                exit 1
                            fi
                            cp run-modules.sh web/modules/pingit/
                            find web/modules/pingit -type f \\( -name '*.sh' -o -name '*.pl' \\) -exec chmod +x {} \\; || true

                            # 4. Проверяем синтаксис perl-скриптов
                            # echo "Validating Perl scripts syntax..."
                            # perl -c web/modules/pingit/pingit.pl || { echo "FATAL: pingit.pl has syntax errors!"; exit 1; }
                            # perl -c web/modules/pingit/fetch.pl  || { echo "FATAL: fetch.pl has syntax errors!"; exit 1; }
                            
                            # 5. Смена владельца файлов
                            export JENKINS_UID_VAL=${JENKINS_UID}
                            export JENKINS_GID_VAL=${JENKINS_GID}
                            chown -R ${JENKINS_UID_VAL}:${JENKINS_GID_VAL} web/
                        '''
                    }
                }
            }
        }

        stage('Deploy Application') {
            steps {
                script {
                    sh """
echo "Deploying alwi-php (Docker Compose V2 via env-file)..."
docker compose -f "${DOCKER_COMPOSE_FILE}" \\
  --env-file "${ENV_FILE}" \\
  up -d --build --force-recreate alwi-php
echo "Deployment executed."
"""
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
