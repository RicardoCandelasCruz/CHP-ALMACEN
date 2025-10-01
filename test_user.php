<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Prueba de conexión a la base de datos</h2>";

try {
    // Intentar conexión
    $dsn = "mysql:host=localhost;dbname=pedidosdb;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '');
    
    echo "<p style='color:green'>✅ Conexión exitosa a la base de datos</p>";
    
    // Verificar si las tablas existen
    $tablas = ['usuarios', 'productos','detalles_pedido','pedidos'];
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✅ Tabla '$tabla' existe</p>";
        } else {
            echo "<p style='color:red'>❌ Tabla '$tabla' no existe</p>";
        }
    }
    
    // Verificar usuarios en la base de datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "<p>Total de usuarios en la base de datos: " . $result['total'] . "</p>";
    
    // Mostrar información de la conexión
    echo "<h3>Información de la conexión:</h3>";
    echo "<pre>";
    print_r([
        'PHP Version' => phpversion(),
        'PDO Drivers' => PDO::getAvailableDrivers(),
        'MySQL Server Version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'Connection Status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)
    ]);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    
    // Verificar si la base de datos existe
    try {
        $pdo = new PDO("mysql:host=localhost", 'root', '');
        $stmt = $pdo->query("SHOW DATABASES LIKE 'validacion_folios'");
        if ($stmt->rowCount() === 0) {
            echo "<p style='color:red'>❌ La base de datos 'validacion_folios' no existe</p>";
        }
    } catch (PDOException $e2) {
        echo "<p style='color:red'>❌ Error al verificar la base de datos: " . $e2->getMessage() . "</p>";
    }
}
