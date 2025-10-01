<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth($pdo);

if (!$auth->verificarSesion()) {
    header("Location: login.php");
    exit();
}

// Solo accesible para encargado
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'encargado') {
    header("Location: login.php");
    exit();
}

include '../includes/conexion.php';

// Obtener todos los productos
try {
    $query = "SELECT id, nombre FROM productos";
    $stmt = $conn->query($query);
    if ($stmt === false) {
        throw new Exception("Error en la consulta SQL.");
    }
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Depuración: Verifica si $productos tiene datos
    if (empty($productos)) {
        echo "No se encontraron productos en la base de datos.";
    }
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Pedidos</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <h1>Formulario de Pedidos</h1>


        <!-- Buscador de productos -->
        <div class="form-group mb-4">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar producto...">
        </div>

       
        <!-- Tabla de productos -->
        <form action="../pages/procesar_pedidos.php" method="POST">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody id="tabla-productos">
                    <?php if (!empty($productos) && is_array($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo $producto['nombre']; ?></td>
                                <td>
                                    <input type="number" name="cantidad[<?php echo $producto['id']; ?>]" class="form-control" min="0" value="0">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No hay productos disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Botón para generar pedido -->
            <button type="submit" class="btn btn-primary mt-3">Generar Pedido</button>
        </form>
    </div>

    <!-- Script para el buscador -->
    <script>
        $(document).ready(function() {
            $('#buscador').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('#tabla-productos tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>