#!/bin/bash

# Configurar el puerto dinámicamente
PORT=${PORT:-80}

# Reemplazar el puerto en la configuración de Nginx
sed -i "s/\${PORT:-80}/$PORT/g" /etc/nginx/sites-available/default

# Crear directorios necesarios
mkdir -p /var/log/nginx
mkdir -p /var/log/supervisor

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf