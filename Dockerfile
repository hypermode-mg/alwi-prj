FROM ubuntu/apache2

# Обновление списка пакетов и установка необходимых утилит
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    php \
    php-pdo \
    php-gd \
    php-common \
    libapache2-mod-php \
    php-mysql \
    php-mysqli \
    php-calendar \
    wget \
    locales \
    libdbd-mysql-perl \
    python3 \
    python3-pip \
    python3-pygal \
    python3-numpy && \
    echo "ru_RU.UTF-8 UTF-8" > /etc/locale.gen && \
    locale-gen && \
    update-locale LANG=ru_RU.UTF-8 && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Настройка Python
RUN ln -sf /usr/bin/python3 /usr/bin/python

# Включение необходимых модулей Apache
RUN a2enmod rewrite
