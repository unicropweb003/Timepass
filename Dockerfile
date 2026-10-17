FROM node:20 AS assets
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

FROM php:8.3-cli
RUN apt-get update && apt-get install -y git unzip libpq-dev libzip-dev \
        libjpeg62-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo_pgsql zip gd opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# Production PHP settings. OPcache keeps compiled code in memory so Laravel is
# not re-compiled on every request (the code never changes inside the image,
# so timestamp checks are off). The built-in server ("cli-server" SAPI) uses
# opcache.enable; artisan commands (CLI) are unaffected.
# Upload limits: PHP's defaults (2 MB per file, 8 MB per form) are below the
# admin's 5 MB images and 40 MB product videos (see CloudinaryUploader).
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'realpath_cache_size=4096K'; \
        echo 'realpath_cache_ttl=600'; \
        echo 'expose_php=Off'; \
        echo 'upload_max_filesize=48M'; \
        echo 'post_max_size=56M'; \
    } > /usr/local/etc/php/conf.d/zz-production.ini

# Serve requests in parallel: 4 worker processes instead of one at a time
# (requires `serve --no-reload`).
ENV PHP_CLI_SERVER_WORKERS=4

# Logs go to the container's output (shown in Render → Logs), not to a file on
# the ephemeral disk. An explicit LOG_CHANNEL in Render's Environment overrides it.
ENV LOG_CHANNEL=stderr

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY . .
COPY --from=assets /app/public/build public/build
RUN composer install --no-dev --optimize-autoloader
# RUN_MIGRATIONS=false on extra instances behind a load balancer, so only one
# instance migrates/seeds the shared database on start (see LOAD-BALANCING.md).
# --isolated: with a shared cache (Redis/database) instances starting together
# take a lock, so only one runs the migrations.
CMD sh -c "if [ \"${RUN_MIGRATIONS:-true}\" = true ]; then php artisan migrate --force --isolated && (php artisan db:seed --force || echo 'db:seed failed - starting anyway'); fi && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache && php artisan serve --no-reload --host=0.0.0.0 --port=${PORT:-10000}"
