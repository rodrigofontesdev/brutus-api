FROM alpine:3.20

WORKDIR /var/www/html

RUN apk add --no-cache \
        curl \
        g++ \
        libmemcached-dev \
        linux-headers \
        make \
        php83-dev \
        php83-ctype \
        php83-curl \
        php83-dom \
        php83-fileinfo \
        php83-fpm \
        php83-gd \
        php83-intl \
        php83-mbstring \
        php83-mysqli \
        php83-opcache \
        php83-openssl \
        php83-pecl-memcached \
        php83-pecl-redis \
        php83-pecl-xdebug \
        php83-pdo \
        php83-pdo_mysql \
        php83-pdo_pgsql \
        php83-pdo_sqlite \
        php83-pear \
        php83-phar \
        php83-session \
        php83-simplexml \
        php83-sqlite3 \
        php83-tokenizer \
        php83-xml \
        php83-xmlreader \
        php83-xmlwriter

RUN curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk add --no-cache nodejs npm && npm install -g npm

RUN apk del curl g++ linux-headers make

COPY /docker/php.ini /etc/php83/conf.d/custom.ini
COPY /docker/www.conf /etc/php83/php-fpm.d/www.conf

RUN adduser -S www-data -G www-data
RUN chown -R www-data:www-data /var/www/html /var/log /run

USER www-data

EXPOSE 9000

ENTRYPOINT [ "php-fpm83", "-F" ]
