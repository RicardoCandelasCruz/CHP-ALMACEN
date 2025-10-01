<?php
session_start();
include '../includes/conexion.php';

$id = $_GET['id'];

try {
    // Eliminar el producto por ID
    $query = "DELETE FROM productos WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);

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
?>