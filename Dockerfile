FROM php:8.2-fpm

# Instalar dependencias del sistema y Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    libzip-dev \
    unzip \
    supervisor \
    openssl \
    libicu-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias para PHPMailer y la aplicación
RUN docker-php-ext-install pdo pdo_pgsql zip
RUN docker-php-ext-install -j$(nproc) iconv

# Habilitar extensión intl para soporte internacional
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# Instalar mbstring para manejo de cadenas multibyte
RUN docker-php-ext-configure mbstring
RUN docker-php-ext-install mbstring

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
    chmod -R 777 /app/pedidos

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