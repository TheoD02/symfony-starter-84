#syntax=docker/dockerfile:1.4

# Versions
FROM dunglas/frankenphp:1-php8.4.1-bookworm AS frankenphp_upstream

ARG SERVER_NAME=":80"
ENV SERVER_NAME=${SERVER_NAME}
ARG BUILD_TIME
ENV BUILD_TIME=$BUILD_TIME

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target

# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

VOLUME /app/var/

# persistent / runtime deps
# hadolint ignore=DL3008
RUN apt-get update && apt-get install -y --no-install-recommends \
        acl \
        file \
        gettext \
        git \
        gosu \
    ;

RUN rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    install-php-extensions \
        @composer \
        apcu \
        intl \
        opcache \
        zip \
        pdo_pgsql \
    ;

ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

###> recipes ###
###> doctrine/doctrine-bundle ###
###< doctrine/doctrine-bundle ###
###< recipes ###

COPY --link .docker/php/conf.d/common $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 .docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link .docker/php/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

# Dev FrankenPHP image
FROM frankenphp_base AS frankenphp_dev

ENV PHPSTAN_PRO_WEB_PORT=11115
EXPOSE 11115

COPY --link .docker/php/conf.d/dev $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 .docker/php/docker-entrypoint-dev.sh /usr/local/bin/docker-entrypoint

ENV XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
    install-php-extensions \
        xdebug \
    ;

# Install fish shell
ENV HOME=/home/www-data
ENV XDG_CONFIG_HOME=$HOME/.config XDG_DATA_HOME=$HOME/.local/share XDG_RUNTIME_DIR=$HOME/.local/run XDG_CACHE_HOME=$HOME/.cache

RUN mkdir -p ${XDG_CONFIG_HOME} ${XDG_DATA_HOME} ${XDG_RUNTIME_DIR} ${XDG_CACHE_HOME} ${COMPOSER_CACHE_DIR}

RUN apt-get update && apt-get install -y fish

# Init non-root user
ARG USER=www-data

# Add build arguments for user and group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

# Remove default user and group
RUN deluser www-data || true \
    && delgroup www-data || true

# Create new user and group with the specified IDs, if they don't already exist
RUN if ! getent group ${GROUP_ID} ; then groupadd -g ${GROUP_ID} www-data ; else groupadd -g 9999 www-data ; fi \
    && if ! id -u ${USER_ID} > /dev/null 2>&1 ; then useradd -u ${USER_ID} -ms /bin/bash -g www-data www-data ; fi


RUN chown -R ${USER}:${USER} /home /tmp /app /home/${USER} ${XDG_CONFIG_HOME} ${XDG_DATA_HOME}

RUN mkdir -p /autorun
COPY .docker/php/autorun/dev /autorun/

RUN chmod +x /autorun/* && chown -R ${USER}:${USER} /autorun

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV APP_RUNTIME="Runtime\FrankenPhpSymfony\Runtime"
ENV MAX_REQUESTS=200

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link .docker/php/conf.d/prod $PHP_INI_DIR/app.conf.d/
COPY --link .docker/php/worker.Caddyfile /etc/caddy/worker.Caddyfile

# prevent the reinstallation of vendors at every changes in the source code
COPY --link ./composer.* ./symfony.* ./
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link . ./

RUN rm -Rf .docker/

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chmod +x bin/console; sync;
