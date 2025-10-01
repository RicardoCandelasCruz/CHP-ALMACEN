<?php
require_once __DIR__ . '/includes/config.php';

echo "<h2>Solucionador de problemas SMTP</h2>";

// Verificaciones básicas
echo "<h3>1. Verificaciones básicas:</h3>";

// Verificar extensiones
$extensiones = ['openssl', 'sockets', 'curl'];
foreach ($extensiones as $ext) {
    echo "Extensión $ext: " . (extension_loaded($ext) ? '✅' : '❌') . "<br>";
}

// Verificar configuración
echo "<br><h3>2. Configuración actual:</h3>";
echo "SMTP_HOST: " . SMTP_HOST . "<br>";
echo "SMTP_PORT: " . SMTP_PORT . "<br>";
echo "SMTP_USER: " . SMTP_USER . "<br>";
echo "SMTP_ENABLED: " . (SMTP_ENABLED ? 'Sí' : 'No') . "<br>";

// Test de conectividad
echo "<br><h3>3. Test de conectividad:</h3>";
$hosts_puertos = [
    ['smtp.gmail.com', 587],
    ['smtp.gmail.com', 465],
    ['smtp-mail.outlook.com', 587]
];

foreach ($hosts_puertos as $config) {
    list($host, $puerto) = $config;
    $connection = @fsockopen($host, $puerto, $errno, $errstr, 10);
    if ($connection) {
        echo "✅ $host:$puerto - Conectado<br>";
        fclose($connection);
    } else {
        echo "❌ $host:$puerto - Error: $errstr ($errno)<br>";
    }
}

// Soluciones sugeridas
echo "<br><h3>4. Soluciones sugeridas:</h3>";
echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
echo "<strong>Si Gmail no funciona:</strong><br>";
echo "1. Verificar que la contraseña de aplicación sea correcta<br>";
echo "2. Habilitar 'Acceso de aplicaciones menos seguras' (no recomendado)<br>";
echo "3. Usar autenticación de 2 factores + contraseña de aplicación<br><br>";

echo "<strong>Configuración alternativa (variables de entorno):</strong><br>";
echo "Crear archivo .env con:<br>";
echo "<code>";
echo "SMTP_HOST=smtp.gmail.com<br>";
echo "SMTP_PORT=587<br>";
echo "SMTP_USER=tu_email@gmail.com<br>";
echo "SMTP_PASS=tu_contraseña_de_aplicacion<br>";
echo "SMTP_ENABLED=true<br>";
echo "</code><br><br>";

echo "<strong>Para deshabilitar temporalmente el SMTP:</strong><br>";
echo "Cambiar en config.php: <code>define('SMTP_ENABLED', false);</code>";
echo "</div>";

// Botón para test rápido
echo "<br><h3>5. Test rápido:</h3>";
echo "<a href='test_smtp.php' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ejecutar diagnóstico completo</a>";
?>