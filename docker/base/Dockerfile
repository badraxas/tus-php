FROM php:8.3-fpm-alpine3.19

LABEL maintainer="hello@ankit.pl,samundra@msn.com" \
  description="This builds tus-php-base image"

ENV LANG=C.UTF-8
ENV LC_ALL=C.UTF-8
ENV PHP_INI_DIR=/usr/local/etc/php
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN ln -sf /usr/share/zoneinfo/Asia/Kathmandu /etc/localtime

RUN apk update && apk add --no-cache \
  make \
  supervisor \
  nginx \
  composer \
  curl \
  ca-certificates \
  linux-headers

# Install required extensions.
RUN docker-php-ext-install pcntl

# Install extensions not available with docker-php-ext.
COPY ./docker/base/bin/php-ext.sh /bin/php-ext
RUN chmod +x /bin/php-ext && php-ext

COPY ./docker/base/bin/xdebug.sh /bin/xdebug
RUN chmod +x /bin/xdebug

# There are some default configurations from the image that will override
# our php-fpm settings and we don't want that. To avoid that, we will
# delete all configurations available by default. Also, we will
# create a folder for the php-fpm socket.
RUN mkdir -p /var/run/php-fpm && rm -rf /usr/local/etc/php-fpm.d/*.conf

COPY ./docker/base/configs/supervisord.conf /etc/supervisord.conf
COPY ./docker/base/configs/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/base/configs/php.ini $PHP_INI_DIR/php.ini
COPY ./docker/base/configs/xdebug.3.ini $PHP_INI_DIR/conf.d/00_xdebug.disable
COPY ./docker/base/configs/www.conf /usr/local/etc/php-fpm.d/www.conf

ENTRYPOINT [ "sh" ]
