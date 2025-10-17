<?php
// Configuración de la base de datos PostgreSQL
// Configuración de base de datos
define('DB_HOST', getenv('DB_HOST') ?: 'maglev.proxy.rlwy.net');
define('DB_PORT', getenv('DB_PORT') ?: '39710');
define('DB_NAME', getenv('DB_NAME') ?: 'railway');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'mccvNsRssKCAbVKdlBAffRDYpvjslpfZ');

// Configuración de la aplicación
define('APP_DEBUG', true);
define('SESSION_TIMEOUT', 1800);

// Configuración SMTP
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_USER', getenv('SMTP_USER') ?: 'sistemacheesepizza@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'opkj posq xeht qqvw');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'cheesepizzarecepcion@gmail.com');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'sistemacheesepizza@gmail.com');
define('SMTP_ENABLED', getenv('SMTP_ENABLED') === 'true');

// Configuración SendGrid API para Railway
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY') ?: '');
define('USE_SENDGRID', !empty(getenv('SENDGRID_API_KEY')));

// Configuración alternativa para Railway (SendGrid/Mailgun)
define('SMTP_ALT_HOST', getenv('SMTP_ALT_HOST') ?: '');
define('SMTP_ALT_USER', getenv('SMTP_ALT_USER') ?: '');
define('SMTP_ALT_PASS', getenv('SMTP_ALT_PASS') ?: '');
define('SMTP_ALT_PORT', getenv('SMTP_ALT_PORT') ?: 587);


?>