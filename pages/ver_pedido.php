<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_GET['id'])) {
    header("Location: lista_pedidos.php");
    exit();
}

$pedidoId = (int)$_GET['id'];
$pdfPath = __DIR__ . "/../pedidos/pedido_{$pedidoId}.pdf";

if (file_exists($pdfPath)) {
    // Agregar botón para volver al formulario
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ver Pedido #' . $pedidoId . '</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 20px; }
            .pdf-container { width: 100%; height: 80vh; }
            iframe { width: 100%; height: 100%; border: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Pedido #' . $pedidoId . '</h2>
                <a href="formulario_pedidos.php?reset=1" class="btn btn-primary">Volver al Formulario</a>
            </div>
            <div class="pdf-container">
                <iframe src="ver_pedido_iframe.php?id=' . $pedidoId . '" frameborder="0"></iframe>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
    exit();
} else {
    die("El pedido solicitado no existe o no tiene PDF generado.");
}
