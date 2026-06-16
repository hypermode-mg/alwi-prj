ARG JENKINS_UID=999
ARG JENKINS_GID=987

FROM ubuntu/apache2

ARG JENKINS_UID
ARG JENKINS_GID

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
    nmap \
    cron \
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

RUN if [ -n "${JENKINS_GID}" ]; then groupmod -g "${JENKINS_GID}" www-data || true; fi
RUN if [ -n "${JENKINS_UID}" ]; then usermod -u "${JENKINS_UID}" www-data || true; fi

COPY restrict-modules.conf /etc/apache2/conf-available/
COPY status.conf /etc/apache2/mods-enabled/

RUN a2enconf restrict-modules && \
    a2enmod rewrite

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
