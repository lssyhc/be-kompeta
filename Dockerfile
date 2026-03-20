FROM php:8.3-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libpq-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    bcmath \
    mbstring \
    intl \
    pcntl \
    xml \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /app

RUN chmod +x /app/start.sh

EXPOSE 10000

CMD ["/app/start.sh"]
