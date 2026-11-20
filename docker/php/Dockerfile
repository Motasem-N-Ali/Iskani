FROM php:8.4-cli

# PHP extensions that Laravel, Filament and our spatial/image work need.
# install-php-extensions installs the system libraries for each one too.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo_mysql intl gd bcmath zip exif pcntl opcache \
    && apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Run as the same user id as the host user, so files created in the
# mounted project folder (vendor, storage, cache) are not owned by root.
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} app 2>/dev/null || true \
    && useradd -m -u ${UID} -g ${GID} app 2>/dev/null || true

USER ${UID}:${GID}
WORKDIR /var/www/html

EXPOSE 8000
