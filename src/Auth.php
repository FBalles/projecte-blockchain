<?php
declare(strict_types=1);

class Auth
{
    public static function login(): void
    {
        $email = $_POST['email'] ?? '';
        $pass  = $_POST['password'] ?? '';

        $stmt = db()->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['rol']     = $user['rol'];
            header('Location: /' . ($user['rol'] === 'admin' ? 'admin' : 'empresa/servicios'));
            exit;
        }

        view('login.php', ['error' => 'Credenciales incorrectas']);
    }

    public static function register(): void
    {
        $nombre = $_POST['empresa'] ?? '';
        $email  = $_POST['email'] ?? '';
        $pass   = password_hash($_POST['password'] ?? '', PASSWORD_ARGON2ID);

        $stmt = db()->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?,?,?,'empresa')");
        $stmt->execute([$nombre, $email, $pass]);

        header('Location: /login');
        exit;
    }

    public static function require(string $rol): void
    {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rol) {
            header('Location: /login');
            exit;
        }
    }
}