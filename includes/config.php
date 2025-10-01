<?php
// Mostrar errores durante el desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'pedidosdb'); // Asegúrate que este es el nombre correcto de tu base de datos
define('DB_USER', 'root'); // Usuario por defecto de XAMPP
define('DB_PASS', ''); // Por defecto no tiene contraseña en XAMPP

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Configurar PDO para lanzar excepciones en caso de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Verificar la conexión
    $pdo->query('SELECT 1');
    
} catch (PDOException $e) {
    // Guardar el error en el log
    error_log("Error de conexión: " . $e->getMessage());
    
    // Durante el desarrollo, mostrar el error específico
    die("Error de conexión: " . $e->getMessage());
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
