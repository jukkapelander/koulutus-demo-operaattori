FROM php:8.4-cli
RUN apt-get update \
    && apt-get install -y --no-install-recommends libxml2-dev libonig-dev \
    && docker-php-ext-install pdo_mysql dom simplexml \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
