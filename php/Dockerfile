FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    cron \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "* * * * * cd /var/www/html && /usr/local/bin/php artisan schedule:work >> /var/log/cron.log 2>&1" > /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler
RUN crontab /etc/cron.d/laravel-scheduler

RUN touch /var/log/cron.log

WORKDIR /var/www/html

CMD service cron start && php-fpm
