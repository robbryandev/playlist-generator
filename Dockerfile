# Use the official PHP 8.2-fpm base image
FROM php:8.2-fpm

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the application files to the container
COPY . /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
  git \
  zip \
  unzip \
  nodejs \
  npm \
  && docker-php-ext-install pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Laravel dependencies
RUN composer install --no-interaction --no-scripts --no-suggest

# Install Node.js dependencies and run Vite build
RUN npm install
RUN npm run build

# Expose the port for Laravel's built-in server
EXPOSE 8000

# Start the Laravel application
CMD php artisan serve --host=0.0.0.0 --port=8000
