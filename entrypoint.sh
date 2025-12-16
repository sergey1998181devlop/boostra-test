#!/bin/bash
set -e
echo ">>>>>> SET DOMAIN HOST <<<<<<"
HOST_DOMAIN="host.docker.internal"
if ! ping -q -c1 $HOST_DOMAIN > /dev/null 2>&1
then
 HOST_IP=$(ip route | awk 'NR==1 {print $3}')
 echo -e "$HOST_IP\t$HOST_DOMAIN" >> /etc/hosts
 echo "$HOST_IP\t$HOST_DOMAIN"
fi
echo ">>>>>> SET DOMAIN HOST DONE <<<<<<"

# Создаем или обновляем файл xdebug.ini с нужными настройками
echo "zend_extension = xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.idekey = PHPStorm" >> /usr/local/etc/php/conf.d/xdebug.ini
echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini
exec apache2-foreground