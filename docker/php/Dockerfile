FROM php:8.3-cli-alpine

ARG DOCKER_UID_GID
ARG ENABLE_XDEBUG
ARG APP_DIR

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache \
        git \
        bash \
    && install-php-extensions \
        pdo_mysql \
        zip \
        pcov \
        xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g $DOCKER_UID_GID appuser \
    && adduser -S -D -u $DOCKER_UID_GID -G appuser -s /bin/bash -h /home/appuser appuser

COPY ./docker/php/xdebug/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY ./docker/php/xdebug/docker-php-ext-xdebug-disabled.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug-disabled.ini

RUN if [ "${ENABLE_XDEBUG}" = "0" ] ; then \
        rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
        echo "XDebug is OFF" \
    ; else \
        rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug-disabled.ini && \
        echo "XDebug is ON" \
    ; fi

USER appuser

WORKDIR $APP_DIR
