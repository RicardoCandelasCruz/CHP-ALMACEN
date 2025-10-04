<?php
echo "=== Diagnóstico de Red Docker ===\n";

// 1. Verificar conectividad básica
echo "1. Probando conectividad a Google DNS...\n";
$dns = fsockopen('8.8.8.8', 53, $errno, $errstr, 5);
echo $dns ? "✓ Internet: OK\n" : "✗ Internet: FALLO ($errno: $errstr)\n";
if ($dns) fclose($dns);

// 2. Verificar resolución DNS
echo "\n2. Probando resolución DNS...\n";
$ip = gethostbyname('smtp.gmail.com');
echo $ip !== 'smtp.gmail.com' ? "✓ DNS: smtp.gmail.com -> $ip\n" : "✗ DNS: FALLO\n";

// 3. Verificar conectividad SMTP
echo "\n3. Probando conectividad SMTP...\n";
$hosts = [
    'smtp.gmail.com:587',
    'smtp.gmail.com:465',
    'smtp.gmail.com:25'
];

foreach ($hosts as $host) {
    list($hostname, $port) = explode(':', $host);
    $conn = @fsockopen($hostname, (int)$port, $errno, $errstr, 10);
    if ($conn) {
        echo "✓ $host: OK\n";
        fclose($conn);
    } else {
        echo "✗ $host: FALLO ($errno: $errstr)\n";
    }
}

// 4. Verificar variables de entorno
echo "\n4. Variables de entorno:\n";
echo "SMTP_ENABLED: " . (getenv('SMTP_ENABLED') ?: 'no definida') . "\n";
echo "SMTP_HOST: " . (getenv('SMTP_HOST') ?: 'no definida') . "\n";
echo "SMTP_PORT: " . (getenv('SMTP_PORT') ?: 'no definida') . "\n";

// 5. Información del sistema
echo "\n5. Información del sistema:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Sistema: " . php_uname() . "\n";
echo "Usuario: " . get_current_user() . "\n";

// 6. Verificar extensiones PHP
echo "\n6. Extensiones PHP necesarias:\n";
$extensions = ['openssl', 'sockets', 'curl'];
foreach ($extensions as $ext) {
    echo extension_loaded($ext) ? "✓ $ext: OK\n" : "✗ $ext: FALTANTE\n";
}
?>