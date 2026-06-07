FROM ubuntu/apache2

ARG JENKINS_UID=999
ARG JENKINS_GID=987

ENV DEBIAN_FRONTEND=noninteractive
ENV LANG=ru_RU.UTF-8
ENV LANGUAGE=ru_RU.UTF-8
ENV LC_ALL=ru_RU.UTF-8

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
    cron \
    locales \
    libdbd-mysql-perl \
    python3 \
    python3-pip \
    python3-pygal \
    python3-numpy && \
    sed -i '/ru_RU.UTF-8/s/^#//' /etc/locale.gen && \
    locale-gen ru_RU.UTF-8 && \
    update-locale LANG=ru_RU.UTF-8 && \
    echo '<Directory /var/www/html/modules/pingit>' > /etc/apache2/conf-available/restrict-pingit.conf && \
    echo '    Require all denied' >> /etc/apache2/conf-available/restrict-pingit.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/restrict-pingit.conf && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN usermod -u ${JENKINS_UID} www-data && \
    groupmod -g ${JENKINS_GID} www-data

RUN ln -sf /usr/bin/python3 /usr/bin/python
RUN a2enmod rewrite

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
