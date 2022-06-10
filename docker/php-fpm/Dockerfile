FROM php:8.1.0-fpm

# libc-client-dev and libkrb5-dev are for imap extension
RUN apt-get update && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev libxml2-dev libc-client-dev libkrb5-dev unzip iproute2 libonig-dev libzip-dev libjudy-dev

# XDEBUG
RUN curl --insecure -fsSL 'https://xdebug.org/files/xdebug-3.1.0.tgz' -o xdebug.tar.gz \
 && mkdir -p xdebug \
 && tar -xf xdebug.tar.gz -C xdebug --strip-components=1 \
 && rm xdebug.tar.gz \
 && (cd xdebug && phpize && ./configure --enable-xdebug && make -j$(nproc) && make install) \
 && rm -r xdebug

# INTL
RUN curl -sS -o /tmp/icu.tar.gz -L "https://github.com/unicode-org/icu/releases/download/release-69-1/icu4c-69_1-src.tgz" \
 && tar -zxf /tmp/icu.tar.gz -C /tmp && cd /tmp/icu/source && ./configure --prefix=/usr/local && make && make install \
 && docker-php-ext-configure intl && docker-php-ext-install intl \
 && rm -f /tmp/icu.tar.gz && rm -rf /tmp/icu

# GD with JPEG Support
RUN docker-php-ext-configure gd \
 && docker-php-ext-install gd

RUN docker-php-ext-install dom soap mbstring xml simplexml zip opcache pdo_mysql pcntl sockets

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

RUN apt update && apt install -y gnupg2 wget \
    && wget -q -O - https://packages.blackfire.io/gpg.key | apt-key add - \
    && echo "deb https://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list \
    && apt --allow-releaseinfo-change update \
    && apt install blackfire blackfire-php

# MEMPROF
RUN pecl install memprof

RUN apt install -y python3 python3-pip && pip3 install openpyxl

COPY --from=composer:2.1.5 /usr/bin/composer /usr/bin/composer
