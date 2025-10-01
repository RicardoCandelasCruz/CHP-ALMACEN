<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        if (!empty($password)) {
            // Hashear la nueva contraseña
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET nombre = :nombre, email = :email, password = :password WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':password', $password_hashed);
        } else {
            // No actualizar la contraseña
            $query = "UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id";
            $stmt = $conn->prepare($query);
        }

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: listar_usuarios.php?success=1");
            exit();
        } else {
            header("Location: editar_usuario.php?id=$id&error=1");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>