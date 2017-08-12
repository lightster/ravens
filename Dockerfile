FROM php:5.6.24-cli
MAINTAINER Matt Light <matt.light@lightdatasys.com>

RUN apt-get update -qq \
    && apt install -yqq \
        libpq-dev \
        git \
        postgresql-client \
    && docker-php-ext-install -j$(nproc) \
        # for phpunit
        bcmath \
        # for phpamqplib
        sockets \
        # for composer
        zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

ADD https://getcomposer.org/installer composer-setup.php
RUN php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer

COPY docker/fs /
COPY . /ravens

VOLUME [ "/ravens" ]
WORKDIR /ravens

ENTRYPOINT ["bash", "/usr/local/bin/entrypoint.sh"]
