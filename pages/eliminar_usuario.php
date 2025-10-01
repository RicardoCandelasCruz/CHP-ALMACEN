<?php
session_start();
include '../includes/conexion.php';

$id = $_GET['id'];

try {
    // Eliminar el usuario por ID
    $query = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        header("Location: listar_usuarios.php?success=1");
        exit();
    } else {
        header("Location: listar_usuarios.php?error=1");
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>