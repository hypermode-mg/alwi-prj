pipeline {
    agent none  // Важно: не выделяем агент сразу, сделаем это позже

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

    // Очистка ДО того, как Jenkins сделает git checkout
    options {
        skipDefaultCheckout()  // Отключаем стандартный checkout
    }

        stage('Pre-Cleanup') {
            agent { label 'docker_agent' }
            steps {
                script {
                    sh '''
docker kill alwi-php 2>/dev/null || true
docker rm -f alwi-php 2>/dev/null || true
sleep 3

if [ -d "web" ]; then
  # Сначала пробуем обычным способом
  if ! find web -mindepth 1 -delete; then
    echo "WARNING: Normal delete failed, trying with sudo..."
    # Если не вышло — делаем то же самое через sudo
    sudo find web -mindepth 1 -delete
    if [ $? -ne 0 ]; then
      echo "ERROR: Could not delete contents of 'web' even with sudo."
      exit 1
    fi
  fi
  rmdir web
  echo "Directory 'web' cleaned successfully."
else
  echo "No 'web' directory to clean."
fi

mkdir -p web
echo "Empty 'web' directory prepared."
'''
                }
            }
        }
        stage('Checkout Repository') {
            agent { label 'docker_agent' }
            steps {
                // Теперь чекаут делаем явно, когда workspace уже почищен
                git(
                    url: "${REPO_URL}",
                    branch: 'main'
                )
            }
        }

        stage('Modify App Configuration') {
            agent { label 'docker_agent' }
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
> "$ENV_FILE"
printf "TZ=%s\n" "${TZ}" >> "$ENV_FILE"
printf "DB_ROOT_PASS=%s\n" "${ROOT_PASSWORD}" >> "$ENV_FILE"
printf "DB_USER=%s\n" "${DB_USER}" >> "$ENV_FILE"
printf "DB_PASS=%s\n" "${DB_PASS}" >> "$ENV_FILE"
printf "DB_NAME=%s\n" "${DB_NAME}" >> "$ENV_FILE"

mkdir -p web/conf
{
printf "<?php return array (\n"
printf "  'enabled' => 1,\n"
printf "  'srvname' => 'SuperMonitoring',\n"
printf "  'db' => '%s',\n" "${DB_NAME}"
printf "  'user' => '%s',\n" "${DB_USER}"
printf "  'pass' => '%s',\n" "${DB_PASS}"
printf "  'address' => '%s',\n" "${DB_HOST}"
printf "  'srvdbtype' => '0',\n"
printf ");\n"
printf "?>\n"
} > "$DB_CONFIG_FILE"

if [ -f "$TARGET_FILE1" ]; then
  sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"
fi

if [ -f "$TARGET_FILE2" ]; then
  sed -i "s|value=\"hpinger\"|value=\"${DB_NAME}\"|g" "$TARGET_FILE2"
  sed -i "s|value=\"localhost\"|value=\"${DB_HOST}\"|g" "$TARGET_FILE2"
  sed -i "s|value=\"pass\"|value=\"${DB_PASS}\"|g" "$TARGET_FILE2"
fi

for perl_file in "$TARGET_FILE3" "$TARGET_FILE4"; do
  if [ ! -f "$perl_file" ]; then
    echo "ERROR: File not found at path: $perl_file"
    exit 1
  fi

  echo "=== BEFORE $perl_file ==="
  grep 'my $host' "$perl_file" || true
  grep 'my $db' "$perl_file" || true
  grep 'my $pass' "$perl_file" || true

  sed -i "s|my \\$\\$host = \"localhost\"|my \\$\\$host = \"${DB_HOST}\"|g" "$perl_file"
  sed -i "s|my \\$\\$db = \"hpinger\"|my \\$\\$db = \"${DB_NAME}\"|g" "$perl_file"
  sed -i "s|my \\$\\$pass = \"pass\"|my \\$\\$pass = \"${DB_PASS}\"|g" "$perl_file"

  if [ $? -ne 0 ]; then
    echo "ERROR: sed failed for $perl_file (exit code $?)"
    exit 1
  fi

  echo "=== AFTER $perl_file ==="
  grep 'my $host' "$perl_file" || true
  grep 'my $db' "$perl_file" || true
  grep 'my $pass' "$perl_file" || true
  echo "Successfully updated $perl_file"
done

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
            agent { label 'docker_agent' }
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
            agent { label 'docker_agent' }
            steps {
                sh '''
if [ ! -f "${DOCKER_COMPOSE_FILE}" ]; then
  echo "ERROR: ${DOCKER_COMPOSE_FILE} not found."
  exit 1
fi
docker compose -f "${DOCKER_COMPOSE_FILE}" up -d --force-recreate alwi-php
docker ps --filter "name=alwi-php" --filter "status=running"
docker ps --filter "name=alwi-db" --filter "status=running" || echo "WARNING: alwi-db not found or not running"
'''
            }
        }

        stage('Verify Deployment') {
            agent { label 'docker_agent' }
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

docker ps --filter "name=alwi-php"
docker logs alwi-php 2>&1 | grep -i "error\\|fail\\|exception\\|mysql\\|php\\|perl" || true
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
                echo 'Cleaning up application container only (DB is kept running)...'
                sh '''
docker stop alwi-php || true
docker rm alwi-php || true
'''
            }
        }
    }
}
