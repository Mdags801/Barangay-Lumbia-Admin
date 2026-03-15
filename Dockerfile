# Use the official PHP Apache image
FROM php:8.2-apache

# Enable Apache Mod Rewrite for pretty URLs
RUN a2enmod rewrite

# Install necessary PHP extensions for Supabase/Curl
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev && \
    docker-php-ext-install curl session

# Copy current project files to the Apache web root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80
EXPOSE 80
