FROM php:8.4-apache-bookworm

# update debian & get required packages
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    unzip \
    p7zip-full \
    libzip-dev \
    libpq-dev

# set apache site
COPY ./resources/docker/apache.conf /etc/apache2/sites-available/apache_http.conf
RUN cd /etc/apache2/sites-available && a2dissite --quiet * \
    && a2enmod --quiet rewrite \
    && a2ensite --quiet apache_http.conf

# Use the default production php configuration
RUN mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"
RUN sed -i 's/expose_php = On/expose_php = Off/g' ${PHP_INI_DIR}/php.ini

# php extensions install
RUN docker-php-ext-install \
    zip \
    pdo_mysql \
	pdo \
    pgsql \
    pdo_pgsql

RUN  docker-php-ext-enable pdo_pgsql

# add composer
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

# install Node.js & npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get update && \
    apt-get install -y nodejs

# set max memory_limit: 128M
RUN echo "memory_limit=256M" >> "${PHP_INI_DIR}/php.ini"

# set max_execution_time: 30s
RUN echo "max_execution_time=30" >> "${PHP_INI_DIR}/php.ini"

# add rights for symfony dirs
#RUN mkdir -p var && chmod -R 777 var

# clean
RUN apt-get clean
RUN apt-get autoclean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

EXPOSE 80/tcp
WORKDIR /var/www/app
CMD ["bash", "-c", "exec apache2-foreground"]