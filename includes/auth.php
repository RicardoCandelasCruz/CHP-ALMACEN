<?php
class Auth {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->iniciarSesion();
    }

    private function iniciarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function hacerLogin($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, password, nombre, es_admin 
                FROM usuarios 
                WHERE username = :username
                LIMIT 1
            ");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $password === $usuario['password']) {
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['es_admin'] = (bool)$usuario['es_admin'];
                $_SESSION['logged_in'] = true;
                
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }

    public function verificarSesion() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function esAdmin() {
        return $this->verificarSesion() && $_SESSION['es_admin'] === true;
    }

    public function redirigirSegunRol() {
        if (!$this->verificarSesion()) {
            header("Location: login.php");
            exit();
        }

        if ($this->esAdmin()) {
            // Solo redirige a index.php si no estamos ya allí
            if (basename($_SERVER['PHP_SELF']) !== 'index.php') {
                header("Location: index.php");
                exit();
            }
        } else {
            // Solo redirige a formulario_pedidos.php si no estamos ya allí
            if (basename($_SERVER['PHP_SELF']) !== 'formulario_pedidos.php') {
                header("Location: formulario_pedidos.php");
                exit();
            }
        }
    }

    public function cerrarSesion() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>