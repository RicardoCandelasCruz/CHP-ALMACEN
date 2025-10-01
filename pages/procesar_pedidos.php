<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/conexion.php'; // Asegúrate que la conexión es para PostgreSQL
require '../libs/fpdf/fpdf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cantidades = $_POST['cantidad'];

    // Filtrar productos con cantidad > 0
    $productos_seleccionados = array_filter($cantidades, function($cantidad) {
        return $cantidad > 0;
    });

    if (empty($productos_seleccionados)) {
        header("Location: formulario_pedidos.php");
        exit();
    }

    try {
        $conn->beginTransaction();

        $usuario_id = $_SESSION['usuario_id'];

        // 1. Insertar el pedido (PostgreSQL usa RETURNING para obtener el ID)
        $query = "INSERT INTO pedidos (usuario_id) VALUES (:usuario_id) RETURNING id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $pedido_id = $stmt->fetchColumn(); // Obtener el ID retornado

        // 2. Obtener el NOMBRE DEL USUARIO
        $queryUsuario = "SELECT nombre FROM usuarios WHERE id = :usuario_id";
        $stmtUsuario = $conn->prepare($queryUsuario);
        $stmtUsuario->bindParam(':usuario_id', $usuario_id);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        // 3. Obtener los NOMBRES DE LOS PRODUCTOS (optimizado para PostgreSQL)
        $idsProductos = array_keys($productos_seleccionados);
        $placeholders = implode(',', array_fill(0, count($idsProductos), '?'));
        $queryProductos = "SELECT id, nombre FROM productos WHERE id IN ($placeholders)";
        $stmtProductos = $conn->prepare($queryProductos);
        $stmtProductos->execute($idsProductos);
        $nombresProductos = $stmtProductos->fetchAll(PDO::FETCH_KEY_PAIR); // Formato: [id => nombre]

        // 4. Insertar los detalles del pedido
        $productosConInfo = [];
        foreach ($productos_seleccionados as $producto_id => $cantidad) {
            $queryDetalle = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad) VALUES (:pedido_id, :producto_id, :cantidad)";
            $stmtDetalle = $conn->prepare($queryDetalle);
            $stmtDetalle->bindParam(':pedido_id', $pedido_id);
            $stmtDetalle->bindParam(':producto_id', $producto_id);
            $stmtDetalle->bindParam(':cantidad', $cantidad);
            $stmtDetalle->execute();

            $productosConInfo[] = [
                'id' => $producto_id,
                'nombre' => $nombresProductos[$producto_id],
                'cantidad' => $cantidad
            ];
        }

        $conn->commit();

        // Obtener fecha del pedido para el XML/PDF
        $queryFecha = "SELECT fecha FROM pedidos WHERE id = :pedido_id";
        $stmtFecha = $conn->prepare($queryFecha);
        $stmtFecha->bindParam(':pedido_id', $pedido_id);
        $stmtFecha->execute();
        $fecha = $stmtFecha->fetchColumn();

        // 📌 Generar XML (con usuario y nombres de productos)
       $xml = new SimpleXMLElement('<pedido/>');
        $xml->addChild('usuario', htmlspecialchars($usuario['nombre']));
        $xml->addChild('pedido_id', $pedido_id);
        $xml->addChild('fecha', $fecha);
        $productosXml = $xml->addChild('productos');
        
        foreach ($productosConInfo as $producto) {
            $item = $productosXml->addChild('producto');
            $item->addChild('id', $producto['id']);
            $item->addChild('nombre', htmlspecialchars($producto['nombre']));
            $item->addChild('cantidad', $producto['cantidad']);
        }

        $xmlFile = "../documentos/pedido_$pedido_id.xml";
        $xml->asXML($xmlFile);

        // 📌 Generar PDF (actualizado con nombres)
        $pdfFile = "../documentos/pedido_$pedido_id.pdf";
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, "Pedido #$pedido_id - Usuario: " . $usuario['nombre'], 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Fecha: " . date('d/m/Y H:i', strtotime($fecha)), 0, 1);
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 10, "Producto", 1);
        $pdf->Cell(40, 10, "Cantidad", 1);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 12);
        foreach ($productosConInfo as $producto) {
            $pdf->Cell(100, 10, $producto['nombre'], 1);
            $pdf->Cell(40, 10, $producto['cantidad'], 1);
            $pdf->Ln();
        }
        $pdf->Output('F', $pdfFile);

        // 📌 Enviar correo
        require '../libs/phpmailer/src/Exception.php';
        require '../libs/phpmailer/src/PHPMailer.php';  
        require '../libs/phpmailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sistemacheesepizza@gmail.com';
            $mail->Password = 'bckv ubll ssga zucl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('sistemacheesepizza@gmail.com', 'Sistema de Pedidos');
            $mail->addAddress('cheesepizzarecepcion@gmail.com');
            $mail->isHTML(true);
            $mail->Subject = 'Nuevo Pedido - ' . $usuario['nombre'];
            $mail->Body = '<p>Se ha registrado un nuevo pedido por parte de <strong>' . $usuario['nombre'] . '</strong>.</p>';

            $mail->addAttachment($xmlFile);
            $mail->addAttachment($pdfFile);
            $mail->send();

            header("Location: pedido_exitoso.php");
            exit();
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
            echo "Error al enviar el correo. Por favor, inténtalo de nuevo.";
        }

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error en la transacción: " . $e->getMessage());
        echo "Error al procesar el pedido: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
    exit();
}
?>