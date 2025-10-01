<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/conexion.php';

$id = $_GET['id'];

try {
    // Obtener el producto por ID
    $query = "SELECT id, nombre FROM productos WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <h1>Editar Producto</h1>
        <form action="procesar_editar_producto.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $producto['nombre']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>