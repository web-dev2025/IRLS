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
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    tesseract-ocr \
    tesseract-ocr-eng \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_sqlite zip mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN composer install --no-interaction --prefer-dist