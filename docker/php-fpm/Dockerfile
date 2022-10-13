FROM php:8.1.0-fpm

# libc-client-dev and libkrb5-dev are for imap extension
RUN apt-get update && apt-get install -y libfreetype6-dev \
    libjpeg62-turbo-dev  \
    libmcrypt-dev  \
    libpng-dev  \
    libxml2-dev  \
    libc-client-dev  \
    libkrb5-dev  \
    unzip  \
    iproute2  \
    libonig-dev  \
    libzip-dev  \
    libjudy-dev  \
    librdkafka-dev \
    librabbitmq-dev \
    libssl-dev \
    libicu-dev

# XDEBUG
RUN curl --insecure -fsSL 'https://xdebug.org/files/xdebug-3.1.0.tgz' -o xdebug.tar.gz \
 && mkdir -p xdebug \
 && tar -xf xdebug.tar.gz -C xdebug --strip-components=1 \
 && rm xdebug.tar.gz \
 && (cd xdebug && phpize && ./configure --enable-xdebug && make -j$(nproc) && make install) \
 && rm -r xdebug

# INTL
RUN apt-get -y update \
&& docker-php-ext-configure intl \
&& docker-php-ext-install intl

# GD with JPEG Support
RUN docker-php-ext-configure gd \
 && docker-php-ext-install gd

RUN docker-php-ext-install dom \
    soap  \
    mbstring  \
    xml  \
    simplexml  \
    zip  \
    opcache  \
    pdo_mysql  \
    pcntl  \
    sockets

# ini files should be applied for command line and php-fpm
ADD app.ini /usr/local/etc/php/conf.d/
ADD app.pool.conf /etc/php7/php-fpm.d/

# Xdebug
ADD xdebug.ini /usr/local/etc/php/conf.d/
ADD xdebug-disable.sh /
ADD xdebug-enable.sh /
RUN chmod +x /xdebug-disable.sh
RUN chmod +x /xdebug-enable.sh

# it should resolve problem with cache & logs permissions
RUN usermod -u 1000 www-data

RUN pecl install  \
    memprof \
    rdkafka \
    amqp

RUN echo "extension=rdkafka.so" > /usr/local/etc/php/conf.d/rdkafka.ini
RUN echo "extension=memprof.so" > /usr/local/etc/php/conf.d/memprof.ini
RUN echo "extension=amqp.so" > /usr/local/etc/php/conf.d/amqp.ini

COPY --from=composer:2.1.5 /usr/bin/composer /usr/bin/composer
