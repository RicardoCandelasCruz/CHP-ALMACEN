<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth($pdo);
$auth->cerrarSesion();

header("Location: login.php");
exit();
?>