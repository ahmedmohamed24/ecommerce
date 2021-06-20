FROM php:7.4-fpm-alpine

ADD ./php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

RUN mkdir -p /var/www/html

RUN chown laravel:laravel /var/www/html
WORKDIR /var/www/html

RUN docker-php-ext-install pdo_mysql

#install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apk update
#install gd for image manipulating
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev 
RUN docker-php-ext-configure gd --enable-gd --with-jpeg
RUN docker-php-ext-install gd
