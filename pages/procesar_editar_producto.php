<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);

    try {
        // Actualizar el producto en la base de datos
        $query = "UPDATE productos SET nombre = :nombre WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: agregar_producto.php?success=1");
            exit();
        } else {
            header("Location: editar_producto.php?id=$id&error=1");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>