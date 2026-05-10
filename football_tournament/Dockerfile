FROM php:8.2-apache

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configure Apache to prevent directory listing and secure sessions
RUN echo "Options -Indexes" >> /etc/apache2/conf-available/security.conf && \
    a2enconf security

# Copy application files
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80
EXPOSE 80
