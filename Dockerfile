FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar archivos
COPY . /var/www/html/

# Instalar dependencias de Composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Crear directorio pedidos y configurar permisos
RUN mkdir -p /var/www/html/pedidos && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html/pedidos

# Configurar Apache para Railway
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN sed -i 's/80/${PORT:-80}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE ${PORT:-80}

CMD ["apache2-foreground"]