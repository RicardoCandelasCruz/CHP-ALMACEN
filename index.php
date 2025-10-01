<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth($pdo);

if (!$auth->verificarSesion() || !$auth->esAdmin()) {
    header("Location: login.php");
    exit();
}

// Solo accesible para admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Sistema de Pedidos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></h1>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Realizar Pedido</h5>
                        <p class="card-text">Haz un nuevo pedido.</p>
                        <a href="pages/formulario_pedidos.php" class="btn btn-primary">Ir al Formulario</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Agregar Producto</h5>
                        <p class="card-text">Añade un nuevo producto al catálogo.</p>
                        <a href="pages/agregar_producto.php" class="btn btn-success">Agregar Producto</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Agregar Usuario</h5>
                        <p class="card-text">Registra un nuevo usuario.</p>
                        <a href="pages/agregar_usuario.php" class="btn btn-warning">Agregar Usuario</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>