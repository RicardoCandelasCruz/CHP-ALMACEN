<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== DIAGNÓSTICO SMTP ===\n\n";

$configuraciones = [
    [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => PHPMailer::ENCRYPTION_STARTTLS,
        'name' => 'Gmail STARTTLS'
    ],
    [
        'host' => 'smtp.gmail.com', 
        'port' => 465,
        'secure' => PHPMailer::ENCRYPTION_SMTPS,
        'name' => 'Gmail SSL'
    ]
];

foreach ($configuraciones as $config) {
    echo "Probando {$config['name']}...\n";
    
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        $mail->Timeout = 10;
        
        $mail->setFrom(SMTP_USER, 'Test');
        $mail->addAddress(SMTP_FROM_EMAIL);
        $mail->Subject = 'Test SMTP';
        $mail->Body = 'Prueba de conectividad SMTP';
        
        if ($mail->send()) {
            echo "✓ ÉXITO con {$config['name']}\n\n";
            break;
        }
    } catch (Exception $e) {
        echo "✗ ERROR con {$config['name']}: " . $e->getMessage() . "\n\n";
    }
}

echo "=== FIN DIAGNÓSTICO ===\n";
?>