<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);

    try {
        // Insertar el producto en la base de datos
        $query = "INSERT INTO productos (nombre) VALUES (:nombre)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);

        if ($stmt->execute()) {
            header("Location: agregar_producto.php?success=1");
            exit();
        } else {
            header("Location: agregar_producto.php?error=1");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>