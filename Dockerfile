# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

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
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libgd-dev \
    libssl-dev \
    libicu-dev \
    pkg-config \
    mysql-client \
    mariadb-client \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions one by one to avoid conflicts
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

RUN docker-php-ext-install pdo_mysql

RUN docker-php-ext-install mysqli

RUN docker-php-ext-install mbstring

RUN docker-php-ext-install exif

RUN docker-php-ext-install pcntl

RUN docker-php-ext-install bcmath

RUN docker-php-ext-install zip

RUN docker-php-ext-install intl

RUN docker-php-ext-install opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache
RUN echo '<Directory /var/www/html>' >> /etc/apache2/apache2.conf \
    && echo '    AllowOverride All' >> /etc/apache2/apache2.conf \
    && echo '</Directory>' >> /etc/apache2/apache2.conf \
    && echo 'Timeout 600' >> /etc/apache2/apache2.conf \
    && echo 'KeepAliveTimeout 15' >> /etc/apache2/apache2.conf

# Configure PHP
RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memory.ini \
    && echo 'upload_max_filesize = 50M' >> /usr/local/etc/php/conf.d/docker-php-uploads.ini \
    && echo 'post_max_size = 50M' >> /usr/local/etc/php/conf.d/docker-php-uploads.ini \
    && echo 'max_execution_time = 600' >> /usr/local/etc/php/conf.d/docker-php-execution.ini \
    && echo 'date.timezone = UTC' >> /usr/local/etc/php/conf.d/docker-php-timezone.ini \
    && echo 'safe_mode = Off' >> /usr/local/etc/php/conf.d/docker-php-safemode.ini \
    && echo 'allow_url_fopen = On' >> /usr/local/etc/php/conf.d/docker-php-urlfopen.ini \
    && echo 'allow_url_include = Off' >> /usr/local/etc/php/conf.d/docker-php-urlinclude.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html/

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh


# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/application/cache \
    && chmod -R 777 /var/www/html/assets \
    && chmod -R 777 /var/www/html/application/modules/itemmanage/assets/images \
    && mkdir -p /var/www/html/uploads/resttable \
    && mkdir -p /var/www/html/uploads/signatures \
    && mkdir -p /var/www/html/uploads/products \
    && mkdir -p /var/www/html/uploads/employees \
    && mkdir -p /var/www/html/uploads/candidates \
    && mkdir -p /var/www/html/uploads/users \
    && chmod -R 777 /var/www/html/uploads \
    && ln -sf /var/www/html/uploads /var/www/html/assets/img/uploads

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["/usr/local/bin/entrypoint.sh"]
