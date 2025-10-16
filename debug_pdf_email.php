<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/libs/fpdf/fpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== DIAGNÓSTICO COMPLETO PDF + EMAIL ===\n\n";

// 1. Verificar configuración
echo "1. CONFIGURACIÓN:\n";
echo "   SMTP_ENABLED: " . (SMTP_ENABLED ? 'true' : 'false') . "\n";
echo "   SMTP_HOST: " . SMTP_HOST . "\n";
echo "   SMTP_PORT: " . SMTP_PORT . "\n";
echo "   SMTP_USER: " . SMTP_USER . "\n";
echo "   SMTP_FROM_EMAIL: " . SMTP_FROM_EMAIL . "\n\n";

// 2. Verificar directorio de pedidos
echo "2. DIRECTORIO DE PEDIDOS:\n";
$pdfDir = __DIR__ . '/pedidos/';
echo "   Ruta: $pdfDir\n";
echo "   Existe: " . (file_exists($pdfDir) ? 'SÍ' : 'NO') . "\n";
echo "   Es escribible: " . (is_writable($pdfDir) ? 'SÍ' : 'NO') . "\n";

if (!file_exists($pdfDir)) {
    echo "   Creando directorio...\n";
    if (mkdir($pdfDir, 0777, true)) {
        echo "   ✓ Directorio creado\n";
        chmod($pdfDir, 0777);
    } else {
        echo "   ✗ Error al crear directorio\n";
    }
}

// 3. Generar PDF de prueba
echo "\n3. GENERACIÓN DE PDF:\n";
try {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'PDF DE PRUEBA', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1);
    $pdf->Cell(0, 10, 'Este es un PDF de prueba para verificar la funcionalidad.', 0, 1);
    
    // Generar en memoria
    $pdfContent = $pdf->Output('', 'S');
    echo "   ✓ PDF generado en memoria (" . strlen($pdfContent) . " bytes)\n";
    
    // Guardar en archivo
    $testPdfPath = $pdfDir . 'test_' . date('YmdHis') . '.pdf';
    if (file_put_contents($testPdfPath, $pdfContent)) {
        chmod($testPdfPath, 0777);
        echo "   ✓ PDF guardado en: $testPdfPath\n";
        echo "   ✓ Tamaño del archivo: " . filesize($testPdfPath) . " bytes\n";
        echo "   ✓ Archivo existe: " . (file_exists($testPdfPath) ? 'SÍ' : 'NO') . "\n";
    } else {
        echo "   ✗ Error al guardar PDF\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error generando PDF: " . $e->getMessage() . "\n";
}

// 4. Probar envío de correo con PDF
echo "\n4. ENVÍO DE CORREO CON PDF:\n";

if (!SMTP_ENABLED) {
    echo "   ⚠ SMTP deshabilitado en configuración\n";
} else {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Sin debug para output limpio
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
        $mail->Subject = 'Test PDF - ' . date('d/m/Y H:i:s');
        $mail->Body = 'Este es un correo de prueba con PDF adjunto.<br><br>Generado desde debug_pdf_email.php';

        // Adjuntar PDF desde memoria
        if (isset($pdfContent)) {
            $mail->addStringAttachment($pdfContent, 'test_pdf.pdf', 'base64', 'application/pdf');
            echo "   ✓ PDF adjuntado desde memoria\n";
        }

        // Adjuntar PDF desde archivo si existe
        if (isset($testPdfPath) && file_exists($testPdfPath)) {
            $mail->addAttachment($testPdfPath, 'test_pdf_archivo.pdf');
            echo "   ✓ PDF adjuntado desde archivo\n";
        }

        if ($mail->send()) {
            echo "   ✓ CORREO ENVIADO EXITOSAMENTE\n";
            echo "   ✓ Destinatario: " . SMTP_FROM_EMAIL . "\n";
        } else {
            echo "   ✗ Error al enviar: " . $mail->ErrorInfo . "\n";
        }

    } catch (Exception $e) {
        echo "   ✗ Excepción al enviar correo: " . $e->getMessage() . "\n";
        echo "   ✗ Código: " . $e->getCode() . "\n";
    }
}

// 5. Verificar permisos del sistema
echo "\n5. PERMISOS DEL SISTEMA:\n";
echo "   Usuario PHP: " . get_current_user() . "\n";
echo "   UID: " . getmyuid() . "\n";
echo "   GID: " . getmygid() . "\n";
echo "   Directorio actual: " . getcwd() . "\n";
echo "   Directorio temporal: " . sys_get_temp_dir() . "\n";

// 6. Listar archivos en directorio pedidos
echo "\n6. ARCHIVOS EN DIRECTORIO PEDIDOS:\n";
if (is_dir($pdfDir)) {
    $files = scandir($pdfDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $pdfDir . $file;
            echo "   - $file (" . filesize($filePath) . " bytes)\n";
        }
    }
} else {
    echo "   Directorio no existe\n";
}

echo "\n=== FIN DIAGNÓSTICO ===\n";
?>