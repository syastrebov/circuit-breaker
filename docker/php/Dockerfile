FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    libmemcached-dev \
    libpq-dev \
    zlib1g-dev \
    libssl-dev \
    && pecl install redis \
    && pecl install -f memcached-3.2.0 \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable memcached \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql \
    && docker-php-ext-enable pdo_mysql

WORKDIR /var/www
ENTRYPOINT []
CMD ["bash"]
