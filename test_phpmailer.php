<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== Test PHPMailer ===\n";
echo "SMTP_ENABLED: " . (SMTP_ENABLED ? 'true' : 'false') . "\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Puerto: " . SMTP_PORT . "\n";
echo "Usuario: " . SMTP_USER . "\n\n";

if (!SMTP_ENABLED) {
    echo "SMTP deshabilitado en configuración\n";
    exit(0);
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) { 
        echo "DEBUG: $str\n"; 
    };
    
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->Timeout = 30;
    
    echo "Intentando conectar...\n";
    
    $mail->setFrom(SMTP_USER, 'Test');
    $mail->addAddress(SMTP_FROM_EMAIL);
    $mail->Subject = 'Test de conectividad';
    $mail->Body = 'Test desde Docker';
    
    if ($mail->send()) {
        echo "✓ Correo enviado exitosamente\n";
    } else {
        echo "✗ Error al enviar: " . $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Excepción: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}
?>