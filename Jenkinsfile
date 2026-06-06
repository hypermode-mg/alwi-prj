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
        DB_PASSWORD = "${DB_PASS}" 
    }

    stages {
        stage('Pre-Cleanup') {
            steps {
                script {
                    sh '''
                        docker stop alwi-php || true
                        docker rm alwi-php || true

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
                            > "$ENV_FILE"
                            printf "TZ=%s\n" "${TZ}" >> "$ENV_FILE"
                            printf "DB_ROOT_PASS=%s\n" "${ROOT_PASSWORD}" >> "$ENV_FILE"
                            printf "DB_USER=%s\n" "${DB_USER}" >> "$ENV_FILE"
                            printf "DB_PASSWORD=%s\n" "${DB_PASSWORD}" >> "$ENV_FILE"
                            printf "DB_NAME=%s\n" "${DB_NAME}" >> "$ENV_FILE"

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

                            if [ -f "$TARGET_FILE1" ]; then
                                sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' "$TARGET_FILE1"
                            fi

                            if [ -f "$TARGET_FILE2" ]; then
                                sed -i "s|value=\"hpinger\"|value=\"${DB_NAME}\"|g" "$TARGET_FILE2"
                                sed -i "s|value=\"localhost\"|value=\"${DB_HOST}\"|g" "$TARGET_FILE2"
                                sed -i "s|value=\"pass\"|value=\"${DB_PASSWORD}\"|g" "$TARGET_FILE2"
                            fi

                            for
