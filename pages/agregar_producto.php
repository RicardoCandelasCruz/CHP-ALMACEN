<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/conexion.php';

// Obtener todos los productos
try {
    $query = "SELECT id, nombre FROM productos";
    $stmt = $conn->query($query);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <h1>Agregar Producto</h1>

        <!-- Formulario para agregar producto -->
        <form action="procesar_producto.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Agregar Producto</button>
        </form>

        <!-- Lista de productos -->
        <h2 class="mt-5">Lista de Productos</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo $producto['id']; ?></td>
                        <td><?php echo $producto['nombre']; ?></td>
                        <td>
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este producto?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>        
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>