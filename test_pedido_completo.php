<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/EmailService.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/libs/fpdf/fpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== TEST PEDIDO COMPLETO ===\n\n";

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✓ Conexión a base de datos exitosa\n";

    // Simular datos de pedido
    $usuarioId = 1; // Usuario de prueba
    $nombreUsuario = "Usuario Test";
    $productos = [
        1 => 2, // Producto ID 1, cantidad 2
        2 => 1  // Producto ID 2, cantidad 1
    ];

    echo "✓ Datos de prueba preparados\n";

    // Crear directorio si no existe
    $pdfDir = __DIR__ . '/pedidos/';
    if (!file_exists($pdfDir)) {
        mkdir($pdfDir, 0777, true);
        chmod($pdfDir, 0777);
        echo "✓ Directorio de pedidos creado\n";
    }

    $pdo->beginTransaction();

    // Insertar pedido de prueba
    $stmtPedido = $pdo->prepare(
        "INSERT INTO pedidos (id, usuario_id, fecha) 
         VALUES ((SELECT COALESCE(MAX(id), 0) + 1 FROM pedidos), :usuario_id, NOW()) 
         RETURNING id"
    );
    $stmtPedido->execute(['usuario_id' => $usuarioId]);
    $pedidoId = $stmtPedido->fetchColumn();

    if (!$pedidoId) {
        throw new Exception('Error al crear el pedido de prueba');
    }
    echo "✓ Pedido #{$pedidoId} creado en base de datos\n";

    // Insertar detalles de productos
    foreach ($productos as $productoId => $cantidad) {
        $stmtDetalle = $pdo->prepare(
            "INSERT INTO detalles_pedido (id, pedido_id, producto_id, cantidad) 
             VALUES ((SELECT COALESCE(MAX(id), 0) + 1 FROM detalles_pedido), :pedido_id, :producto_id, :cantidad)"
        );
        $stmtDetalle->execute([
            'pedido_id' => $pedidoId,
            'producto_id' => $productoId,
            'cantidad' => $cantidad
        ]);
    }
    echo "✓ Detalles de productos insertados\n";

    // Generar PDF
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

    // Agregar productos
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'ID', 1, 0, 'C');
    $pdf->Cell(100, 5, 'Producto', 1, 0, 'C');
    $pdf->Cell(20, 5, 'Cantidad', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 7);
    foreach ($productos as $productoId => $cantidad) {
        // Obtener nombre del producto
        $stmtProducto = $pdo->prepare("SELECT nombre FROM productos WHERE id = :id");
        $stmtProducto->execute(['id' => $productoId]);
        $nombreProducto = $stmtProducto->fetchColumn() ?: 'Producto #' . $productoId;

        $pdf->Cell(20, 5, $productoId, 1, 0, 'C');
        $pdf->Cell(100, 5, $nombreProducto, 1, 0, 'L');
        $pdf->Cell(20, 5, $cantidad, 1, 1, 'C');
    }

    // Generar PDF en memoria
    $pdfContent = $pdf->Output('', 'S');
    echo "✓ PDF generado en memoria (" . strlen($pdfContent) . " bytes)\n";

    // Guardar PDF
    $pdfFilename = "pedido_{$pedidoId}.pdf";
    $pdfPath = $pdfDir . $pdfFilename;
    
    if (file_put_contents($pdfPath, $pdfContent)) {
        chmod($pdfPath, 0777);
        echo "✓ PDF guardado en: $pdfPath\n";
        echo "✓ Tamaño del archivo: " . filesize($pdfPath) . " bytes\n";
    } else {
        echo "✗ Error al guardar PDF\n";
    }

    $pdo->commit();
    echo "✓ Transacción de base de datos confirmada\n";

    // Intentar enviar correo
    echo "\n--- INTENTANDO ENVÍO DE CORREO ---\n";
    
    $emailSent = false;
    
    // Método 1: EmailService
    try {
        $emailSent = EmailService::enviar($pdfContent, $pedidoId, $nombreUsuario);
        if ($emailSent) {
            echo "✓ Correo enviado con EmailService\n";
        } else {
            echo "✗ Fallo con EmailService\n";
        }
    } catch (Exception $e) {
        echo "✗ Error con EmailService: " . $e->getMessage() . "\n";
    }

    // Método 2: PHPMailer directo si el anterior falló
    if (!$emailSent && SMTP_ENABLED) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
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

            $mail->addStringAttachment($pdfContent, "pedido_{$pedidoId}.pdf", 'base64', 'application/pdf');

            if ($mail->send()) {
                echo "✓ Correo enviado con PHPMailer directo\n";
                $emailSent = true;
            } else {
                echo "✗ Error con PHPMailer: " . $mail->ErrorInfo . "\n";
            }
        } catch (Exception $e) {
            echo "✗ Excepción con PHPMailer: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== RESUMEN ===\n";
    echo "Pedido ID: $pedidoId\n";
    echo "PDF generado: " . (file_exists($pdfPath) ? 'SÍ' : 'NO') . "\n";
    echo "PDF visible: " . (file_exists($pdfPath) ? "http://localhost/pages/ver_pedido.php?id=$pedidoId" : 'NO') . "\n";
    echo "Correo enviado: " . ($emailSent ? 'SÍ' : 'NO') . "\n";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "✗ Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "✗ Error general: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST ===\n";
?>