FROM webdevops/php-nginx:8.2

# Instalar PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copiar archivos
COPY . /app/

# Configurar Nginx para puerto 8080
COPY nginx-8080.conf /opt/docker/etc/nginx/vhost.conf

EXPOSE 8080

# Configurar puerto para Railway
ENV PORT=8080
CMD ["/opt/docker/bin/entrypoint.sh", "supervisord"]