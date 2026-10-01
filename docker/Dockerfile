FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    iconv \
    freetype \
    libjpeg \
    libpng \
    libpq \
    nginx \
    supervisor \
    curl \
    wget \
    git \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql bcmath

# Set working directory
WORKDIR /var/www

# Install Composer
COMPOSER_ALLOW_SUPERUSER=1 curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user
RUN addgroup -g 1000 appgroup && adduser -u 1000 -G appgroup -s /bin/sh -D appgroup

# Copy application code
COPY . /var/www

# Change ownership
RUN chown -R appgroup:appgroup /var/www

# Expose port
EXPOSE 80

# Start PHP-FPM and Nginx
CMD ["php-fpm"]