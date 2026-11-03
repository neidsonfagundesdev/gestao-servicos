# Usa PHP 8.5 com Apache.
# É a mesma família de versão que estamos usando localmente.
FROM php:8.5-apache

# Instala bibliotecas necessárias para o Laravel
# e extensões do PHP.
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        intl \
        zip \
        bcmath \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Copia o Composer de uma imagem oficial.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Pasta onde nossa aplicação ficará dentro do container.
WORKDIR /var/www/html

# Copia o projeto para dentro do container.
COPY . .

# Instala somente as dependências necessárias em produção.
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# O Laravel precisa conseguir escrever nestas pastas.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Permite que o Apache/PHP leia os Secret Files do Render.
RUN usermod -a -G 1000 www-data

# Faz o Apache apontar para a pasta public do Laravel.
RUN sed -ri \
    's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

# Permite que o Laravel use o arquivo public/.htaccess.
RUN sed -ri \
    's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# O Render informa a porta através da variável PORT.
#
# Antes de iniciar o Apache:
# - ajustamos a porta
# - limpamos/cacheamos configurações
# - executamos as migrations
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-10000}/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT:-10000}>/\" /etc/apache2/sites-available/000-default.conf && php artisan config:cache && php artisan migrate --force && php artisan db:seed --force && apache2-foreground"]