FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-xml --ignore-platform-req=ext-dom --ignore-platform-req=ext-xmlwriter --no-scripts

# Copy application code
COPY . .

# Configure Apache to serve Laravel from the public directory
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/public#' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/cargopulse.conf \
    && a2enconf cargopulse

# Generate application key if not set
RUN if [ -z "$APP_KEY" ]; then php -r "echo 'base64:' . base64_encode(random_bytes(32));" > /tmp/key.txt && sed -i "s|APP_KEY=.*|APP_KEY=$(cat /tmp/key.txt)|" .env; fi

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]