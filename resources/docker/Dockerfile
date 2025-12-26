FROM php:8.5-apache-bookworm

# update debian & get required packages
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    unzip \
    p7zip-full \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    zlib1g-dev \
    g++ \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# set apache site
COPY ./resources/docker/apache.conf /etc/apache2/sites-available/apache_http.conf
RUN cd /etc/apache2/sites-available && a2dissite --quiet * \
    && a2enmod --quiet rewrite \
    && a2ensite --quiet apache_http.conf

# Use the default production php configuration
RUN mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"
RUN sed -i 's/expose_php = On/expose_php = Off/g' ${PHP_INI_DIR}/php.ini

# -----------------------
# PHP Extensions
# -----------------------
RUN docker-php-ext-install \
    zip \
    pdo_mysql \
    pdo \
    pgsql \
    pdo_pgsql \
    intl \
    bcmath

RUN docker-php-ext-enable pdo_pgsql

# -----------------------
# OPCACHE
# -----------------------
RUN echo "opcache.enable=1" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.enable_cli=0" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.memory_consumption=128" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.interned_strings_buffer=8" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.max_accelerated_files=10000" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.validate_timestamps=0" >> "${PHP_INI_DIR}/php.ini" && \
    echo "opcache.revalidate_freq=0" >> "${PHP_INI_DIR}/php.ini"

# -----------------------
# Composer
# -----------------------
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

# -----------------------
# Node.js & npm
# -----------------------
RUN curl -fsSL https://deb.nodesource.com/setup_25.x | bash - && \
    apt-get update && \
    apt-get install -y nodejs

# -----------------------
# PHP config
# -----------------------
RUN echo "memory_limit=256M" >> "${PHP_INI_DIR}/php.ini"
RUN echo "max_execution_time=30" >> "${PHP_INI_DIR}/php.ini"

# Clean
RUN apt-get clean && \
    apt-get autoclean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

EXPOSE 80/tcp
WORKDIR /var/www/app
CMD ["bash", "-c", "exec apache2-foreground"]