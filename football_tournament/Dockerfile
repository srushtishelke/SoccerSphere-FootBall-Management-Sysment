FROM php:8.2-apache

# Install PostgreSQL extensions
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql pdo_mysql

# Enable Apache mod_rewrite for nice URLs if needed
RUN a2enmod rewrite

# Copy project files into the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80 (Render forwards to the port the app binds to)
EXPOSE 80
