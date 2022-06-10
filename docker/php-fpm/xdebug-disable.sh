#!/usr/bin/env bash

PHP_XDEBUG_INI_ACTIVE="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
PHP_XDEBUG_INI_DISABLED="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disabled"

if [ -f "$PHP_XDEBUG_INI_DISABLED" ]; then
    echo -e "xdebug already disabled"
elif [ -f "$PHP_XDEBUG_INI_ACTIVE" ]; then
    mv ${PHP_XDEBUG_INI_ACTIVE} ${PHP_XDEBUG_INI_DISABLED}
    kill -USR2 1
    echo -e "xdebug disabled"
else
    echo -e "xdebug not started"
fi