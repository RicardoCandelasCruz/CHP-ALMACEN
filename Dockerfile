FROM php:8.2-fpm

# Instalar dependencias del sistema y Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    libzip-dev \
    unzip \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar archivos de la aplicación
COPY . /app/
WORKDIR /app

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Crear directorio pedidos y configurar permisos
RUN mkdir -p /app/pedidos && \
    chown -R www-data:www-data /app && \
    chmod -R 755 /app/pedidos

# Copiar configuración de Nginx
COPY nginx.conf /etc/nginx/sites-available/default

# Copiar configuración de Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar script de inicio
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Configurar PHP-FPM
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf

EXPOSE ${PORT:-80}

CMD ["/start.sh"]