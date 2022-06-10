#!/usr/bin/env bash

if [[ -z "${XDEBUG_REMOTE_HOST}" ]]; then
  REMOTE_HOST=`/sbin/ip route|awk '/default/ { print $3 }'`
else
  REMOTE_HOST="${XDEBUG_REMOTE_HOST}"
fi

sed -i -e "s/\$remote_host/${REMOTE_HOST}/g" /usr/local/etc/php/conf.d/xdebug.ini

PHP_XDEBUG_INI_ACTIVE="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"
PHP_XDEBUG_INI_DISABLED="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disabled"

if [ -f "$PHP_XDEBUG_INI_DISABLED" ]; then
   mv ${PHP_XDEBUG_INI_DISABLED} ${PHP_XDEBUG_INI_ACTIVE}
   kill -USR2 1
   echo -e "xdebug enabled"
elif [ -f "$PHP_XDEBUG_INI_ACTIVE" ]; then
   echo -e "xdebug already enabled"
else
    docker-php-ext-enable xdebug
    kill -USR2 1
    echo -e "xdebug enabled"
fi