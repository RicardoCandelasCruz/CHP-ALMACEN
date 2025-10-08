<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/EmailService.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/libs/fpdf/fpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "Probando generación de PDF y envío de correo...\n";

// Simular datos de pedido
$pedidoId = 999;
$nombreUsuario = "Usuario de Prueba";

// Directorio para PDFs
$pdfDir = __DIR__ . '/pedidos/';
$pdfFilename = "pedido_test_{$pedidoId}.pdf";
$pdfPath = $pdfDir . $pdfFilename;

echo "Generando PDF en: $pdfPath\n";

// Generar PDF de prueba
$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'CHEESE PIZZA ALMACEN - PEDIDO DE PRUEBA', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, 'Pedido #' . $pedidoId, 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 4, 'Cliente:', 0);
$pdf->Cell(0, 4, $nombreUsuario, 0, 1);
$pdf->Cell(30, 4, 'Fecha:', 0);
$pdf->Cell(0, 4, date('d/m/Y H:i:s'), 0, 1);
$pdf->Ln(3);

// Productos de prueba
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, 'ID', 1, 0, 'C');
$pdf->Cell(100, 5, 'Producto', 1, 0, 'C');
$pdf->Cell(20, 5, 'Cantidad', 1, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->Cell(20, 5, '1', 1, 0, 'C');
$pdf->Cell(100, 5, 'Pizza Margherita', 1, 0, 'L');
$pdf->Cell(20, 5, '2', 1, 1, 'C');

$pdf->Cell(20, 5, '2', 1, 0, 'C');
$pdf->Cell(100, 5, 'Pizza Pepperoni', 1, 0, 'L');
$pdf->Cell(20, 5, '1', 1, 1, 'C');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 4, 'Gracias por su pedido. Para cualquier consulta contacte a sistemacheesepizza@gmail.com', 0, 1, 'C');

// Generar PDF en memoria
$pdfContent = $pdf->Output('', 'S');

// Guardar PDF
file_put_contents($pdfPath, $pdfContent);
chmod($pdfPath, 0777);

if (file_exists($pdfPath)) {
    echo "✅ PDF generado exitosamente: " . filesize($pdfPath) . " bytes\n";
} else {
    echo "❌ Error al generar PDF\n";
    exit;
}

// Probar envío de correo
echo "Intentando enviar correo...\n";

// Función de envío directo con PHPMailer
function enviarCorreoTest($pdfPath, $pedidoId, $nombreUsuario) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 1;
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

        $mail->setFrom(SMTP_USER, 'Cheese Pizza Almacen');
        $mail->addAddress(SMTP_FROM_EMAIL);

        $mail->isHTML(true);
        $mail->Subject = "Pedido de Prueba #{$pedidoId} - Cheese Pizza Almacen";
        $mail->Body = "Se ha generado un pedido de prueba:<br><br>"
                    . "Número de Pedido: {$pedidoId}<br>"
                    . "Cliente: {$nombreUsuario}<br>"
                    . "Fecha: " . date('d/m/Y H:i:s');

        $mail->addAttachment($pdfPath, "pedido_{$pedidoId}.pdf");

        if ($mail->send()) {
            echo "✅ Correo enviado exitosamente!\n";
            return true;
        }
    } catch (Exception $e) {
        echo "❌ Error al enviar correo: " . $e->getMessage() . "\n";
    }
    return false;
}

// Intentar envío
$emailSent = enviarCorreoTest($pdfPath, $pedidoId, $nombreUsuario);

if ($emailSent) {
    echo "🎉 Prueba completa exitosa!\n";
} else {
    echo "⚠️ PDF generado pero correo falló. El PDF está disponible en: pedidos/$pdfFilename\n";
}

echo "\nConfiguración SMTP actual:\n";
echo "Host: " . SMTP_HOST . "\n";
echo "User: " . SMTP_USER . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "To: " . SMTP_FROM_EMAIL . "\n";
?>