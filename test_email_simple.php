<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "Probando envío de correo...\n";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 2; // Mostrar debug
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 30;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom(SMTP_USER, 'Cheese Pizza Almacen - Test');
    $mail->addAddress(SMTP_FROM_EMAIL);

    $mail->isHTML(true);
    $mail->Subject = 'Test de correo - ' . date('Y-m-d H:i:s');
    $mail->Body = 'Este es un correo de prueba enviado el ' . date('d/m/Y H:i:s');

    if ($mail->send()) {
        echo "✅ Correo enviado exitosamente!\n";
    } else {
        echo "❌ Error al enviar correo\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>