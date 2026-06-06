pipeline {
    agent any

    environment {
        DOCKER_COMPOSE_FILE = 'docker-compose.yml'
        DB_HOST_VAL = 'alwi-db'
        DB_NAME_VAL = 'alertsonwings'
        // Убедитесь, что у вас в Jenkins создан credentialsId 'db-app-credentials'
        // Он должен содержать пароль пользователя БД (или root, если они совпадают)
        DB_CREDENTIALS_ID = 'db-app-credentials' 
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Prepare Files & Patch Code') {
            steps {
                script {
                    // Определяем пути к файлам относительно рабочей директории
                    def TARGET_FILE1 = 'web/install/step1.php'
                    def TARGET_FILE2 = 'web/install/step2.php'
                    def TARGET_FILE3 = 'web/modules/pingit/pingit.pl'
                    def TARGET_FILE4 = 'web/modules/pingit/fetch.pl'

                    echo "Starting file patching process..."

                    // 1. Даем права на запись тем файлам, которые будем менять
                    sh """
                        chmod u+w ${TARGET_FILE1} ${TARGET_FILE2} ${TARGET_FILE3} ${TARGET_FILE4}
                    """

                    // 2. Исправление пути в step1.php (Apache modules path)
                    // Используем | как разделитель, чтобы не экранировать слэши
                    sh """
                        sed -i 's|/etc/httpd/modules/|/usr/lib/apache2/modules/|g' ${TARGET_FILE1}
                    """

                    // 3. Исправление значений в step2.php (UI labels/defaults)
                    sh """
                        sed -i 's|value="hpinger"|value="alertsonwings"|g' ${TARGET_FILE2}
                        sed -i 's|value="localhost"|value="alwi-db"|g' ${TARGET_FILE2}
                        sed -i 's|value="pass"|value="Enter user password"|g' ${TARGET_FILE2}
                    """

                    echo "DEBUG: DB_HOST_VAL='${DB_HOST_VAL}', DB_NAME_VAL='${DB_NAME_VAL}'"

                    // 4. Замена host в pingit.pl и fetch.pl
                    // Шаблон ловит и "localhost", и 'localhost' благодаря хитрой конструкции ['\'']
                    sh """
                        sed -i 's|my *\\$host *= *["'\'']localhost["'\'']|my \\$host = "'${DB_HOST_VAL}'"|g' ${TARGET_FILE3}
                        sed -i 's|my *\\$host *= *["'\'']localhost["'\'']|my \\$host = "'${DB_HOST_VAL}'"|g' ${TARGET_FILE4}
                    """
                    
                    // 5. Замена db в pingit.pl и fetch.pl
                    sh """
                        sed -i 's|my *\\$db *= *["'\'']hpinger["'\'']|my \\$db = "'${DB_NAME_VAL}'"|g' ${TARGET_FILE3}
                        sed -i 's|my *\\$db *= *["'\'']hpinger["'\'']|my \\$db = "'${DB_NAME_VAL}'"|g' ${TARGET_FILE4}
                    """

                    // 6. Замена пароля на переменную окружения (КРИТИЧНО)
                    // Было: my $pass = "pass"
                    // Стало: my $pass = $ENV{DB_PASS}
                    // Обратите внимание на экранирование $ для bash (\$) и отсутствие экранирования для perl ($ENV)
                    sh """
                        sed -i 's|my *\\$pass *= *["'\'']pass["'\'']|my \\$pass = \$ENV{DB_PASS}|g' ${TARGET_FILE3}
                        sed -i 's|my *\\$pass *= *["'\'']pass["'\'']|my \\$pass = \$ENV{DB_PASS}|g' ${TARGET_FILE4}
                    """

                    // Финальная проверка замен (вывод в лог)
                    echo "--- Final check in ${TARGET_FILE3} ---"
                    sh "grep -n -E 'host|db|pass' ${TARGET_FILE3}"
                    echo "--- Final check in ${TARGET_FILE4} ---"
                    sh "grep -n -E 'host|db|pass' ${TARGET_FILE4}"
                }
            }
        }

        stage('Build Docker Image') {
            steps {
                // Сборка образа. Контекст - текущая папка (там Dockerfile и web/)
                sh 'docker build -t alwi-php:latest .'
            }
        }

        stage('Deploy via Docker Compose') {
            steps {
                withCredentials([
                    usernamePassword(
                        credentialsId: "${DB_CREDENTIALS_ID}",
                        usernameVariable: 'DB_USER',
                        passwordVariable: 'DB_PASS'
                    )
                ]) {
                    sh '''
                        if [ ! -f "${DOCKER_COMPOSE_FILE}" ]; then
                            echo "ERROR: ${DOCKER_COMPOSE_FILE} not found."
                            exit 1
                        fi
                        
                        # Экспортируем переменные для docker compose
                        export DB_USER="${DB_USER}"
                        export DB_PASS="${DB_PASS}"
                        
                        # Передаем переменные в compose. 
                        # В docker-compose.yml должно быть: - DB_PASS=${DB_PASS} в секции alwi-php
                        echo "Deploying with DB_USER=${DB_USER} (password hidden)"
                        
                        docker compose -f "${DOCKER_COMPOSE_FILE}" up -d --force-recreate alwi-php alwi-db
                    '''
                }
            }
        }
    }

    post {
        always {
            echo 'Pipeline finished. Checking status...'
            // Можно добавить очистку workspace, если нужно
            // cleanWs() 
        }
        failure {
            echo 'Pipeline failed! Check the logs above for the specific error (likely in sed or docker build).'
        }
    }
}
