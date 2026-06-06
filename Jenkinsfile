pipeline {
    agent any

    environment {
        // Подставьте ваши реальные значения или используйте Jenkins Credentials
        DB_ROOT_PASSWORD = credentials('db-root-password')
        DB_NAME = 'alwi_db'
        DB_USER = 'alwi_user'
        DB_PASSWORD = credentials('db-user-password')
        // Получаем UID пользователя jenkins на агенте (обычно 1000)
        JENKINS_UID = sh(script: 'id -u jenkins', returnStdout: true).trim()
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Modify App Configuration') {
            steps {
                script {
                    sh '''
                        # 1. Выставляем владельца и группу на файлы приложения.
                        # Это критически важно: владелец (UID) на хосте и в контейнере должны совпасть!
                        chown -R ${JENKINS_UID}:${JENKINS_UID} web/
                        
                        # 2. Выставляем стандартные права.
                        chmod -R 755 web/
                        
                        # 3. Ваши существующие замены конфигурации (пример)
                        # Например, если нужно подставить хост БД в конфиг приложения:
                        sed -i "s|DB_HOST_PLACEHOLDER|alwi-db|g" web/config.php
                        
                        echo "Permissions and ownership fixed for UID ${JENKINS_UID}"
                    '''
                }
            }
        }

        stage('Build and Deploy') {
            steps {
                script {
                    // Передаём JENKINS_UID в compose через переменную окружения
                    // Docker Compose подхватит её в build.args
                    sh '''
                        export JENKINS_UID=${JENKINS_UID}
                        docker compose down
                        docker compose build --no-cache
                        docker compose up -d
                    '''
                }
            }
        }
        
        stage('Verify Deployment') {
            steps {
                script {
                    sh '''
                        echo "Checking container logs..."
                        docker logs alwi-php --tail 50
                        
                        echo "Checking Cron jobs..."
                        docker exec alwi-php crontab -l
                        
                        echo "Checking Apache status..."
                        docker exec alwi-php apachectl status
                    '''
                }
            }
        }
    }

    post {
        always {
            echo "Pipeline finished."
        }
        failure {
            echo "Pipeline failed! Check logs."
        }
    }
}
