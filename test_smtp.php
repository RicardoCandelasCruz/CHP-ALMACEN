<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== Diagnóstico de Conectividad SMTP ===\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Puerto: " . SMTP_PORT . "\n";
echo "Usuario: " . SMTP_USER . "\n";
echo "SMTP Habilitado: " . (SMTP_ENABLED ? 'Sí' : 'No') . "\n\n";

if (!SMTP_ENABLED) {
    echo "SMTP está deshabilitado en la configuración.\n";
    exit(0);
}

// Test de conectividad básica
echo "Probando conectividad a " . SMTP_HOST . ":" . SMTP_PORT . "...\n";
$connection = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
if ($connection) {
    echo "✓ Conectividad básica: OK\n";
    fclose($connection);
} else {
    echo "✗ Conectividad básica: FALLO ($errno: $errstr)\n";
    echo "Esto indica un problema de red o firewall.\n";
    exit(1);
}

// Test de PHPMailer
echo "\nProbando configuración de PHPMailer...\n";
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->Timeout = 10;
    
    // Solo probar la conexión, no enviar correo
    if ($mail->smtpConnect()) {
        echo "✓ Autenticación SMTP: OK\n";
        $mail->smtpClose();
    } else {
        echo "✗ Autenticación SMTP: FALLO\n";
    }
} catch (Exception $e) {
    echo "✗ Error de PHPMailer: " . $e->getMessage() . "\n";
}

echo "\n=== Fin del diagnóstico ===\n";
?>