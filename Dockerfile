# 1. Use the official PHP image bundled with the Apache web server
FROM php:8.2-apache
RUN a2enmod rewrite

# RUN THIS COMMAND: Installs the required extensions to talk to a MySQL Database
RUN docker-php-ext-install pdo pdo_mysql

COPY index.php /var/www/html/index.php
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80

# 2. Enable Apache mod_rewrite (useful for clean URL routing)
RUN a2enmod rewrite

# 3. Copy your script into Apache's default public directory
# Renaming it to index.php ensures it loads automatically at your main URL
COPY index.php /var/www/html/index.php

# 4. Set correct file permissions so the web server can read it safely
RUN chown -R www-data:www-data /var/www/html

# 5. Tell the container to open Port 80 for incoming web traffic
EXPOSE 80
