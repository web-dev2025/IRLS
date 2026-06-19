FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_sqlite zip mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs
 
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
