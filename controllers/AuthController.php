<?php

namespace Controllers;

use MVC\Router;
use Model\Usuarios;
use Model\TokensAcceso;

class AuthController
{
    // ── Vista login ───────────────────────────────────────────────────────────
    public static function login(Router $router): void
    {
        if (isset($_SESSION['auth_user'])) {
            header('Location: ' . ($_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '') . '/vehiculos');
            exit;
        }
        // Carga la vista SIN layout
        include __DIR__ . '/../views/auth/login.php';
    }

    // ── API: verificar catálogo ───────────────────────────────────────────────
    public static function verificarCatalogoAPI(): void
    {
        getHeadersApi();
        $catalogo = trim($_POST['catalogo'] ?? '');

        if (!$catalogo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ingresa tu catálogo']);
            exit;
        }

        $usuario = Usuarios::buscarPorCatalogo($catalogo);

        if (!$usuario) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Catálogo no encontrado']);
            exit;
        }

        if (!$usuario->activo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario inactivo']);
            exit;
        }

        // Primer ingreso — necesita registrar correo
        if ($usuario->primer_ingreso) {
            echo json_encode([
                'codigo'  => 2,
                'mensaje' => 'primer_ingreso',
                'nombre' => mb_convert_encoding($usuario->grado . ' ' . $usuario->nombre_completo, 'UTF-8', 'UTF-8')
            ]);
            exit;
        }

        // Ya tiene contraseña — continuar con login normal
        echo json_encode([
            'codigo'  => 1,
            'mensaje' => 'ok',
            'nombre' => mb_convert_encoding($usuario->grado . ' ' . $usuario->nombre_completo, 'UTF-8', 'UTF-8')
        ]);
    }

    // ── API: registrar correo y enviar token ──────────────────────────────────
    public static function registrarCorreoAPI(): void
    {
        getHeadersApi();
        $catalogo = trim($_POST['catalogo'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');

        if (!$catalogo || !$correo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
            exit;
        }

        $usuario = Usuarios::buscarPorCatalogo($catalogo);
        if (!$usuario) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
            exit;
        }

        // Guardar correo
        // Actualizar correo — usar UPDATE no INSERT
        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("UPDATE usuarios SET correo = ? WHERE catalogo = ?");
        $stmt->execute([$correo, $catalogo]);

        // Generar token
        $token = TokensAcceso::generar($catalogo);
        $token->guardar();

        // Enviar email
        $base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';
        $link = ($_ENV['APP_URL'] ?? 'http://localhost:9002') . $base . '/auth/setup?token=' . $token->token;

        $enviado = self::enviarEmail(
            $correo,
            'Crear contraseña — VEHICULOS BHR',
            "
            <h2>Bienvenido/a al sistema VEHICULOS BHR</h2>
            <p>Haz clic en el siguiente enlace para crear tu contraseña:</p>
            <a href='{$link}' style='background:#e8b84b;color:#000;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                Crear contraseña
            </a>
            <p style='color:#888;font-size:12px;margin-top:16px;'>Este enlace expira en 2 horas.</p>
            "
        );

        echo json_encode([
            'codigo' => 1,
            'mensaje' => 'Correo enviado correctamente',
            'debug_link' => $link  // ← solo para desarrollo local
        ]);
    }

    // ── Vista setup (crear contraseña) ────────────────────────────────────────
    public static function setup(Router $router): void
    {
        $token = trim($_GET['token'] ?? '');
        if (!$token) {
            header('Location: /');
            exit;
        }

        $tokenObj = TokensAcceso::buscarValido($token);
        if (!$tokenObj) {
            include __DIR__ . '/../views/auth/token_invalido.php';
            return;
        }

        include __DIR__ . '/../views/auth/setup.php';
    }

    // ── API: guardar contraseña ───────────────────────────────────────────────
    public static function guardarPasswordAPI(): void
    {
        getHeadersApi();
        $token    = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (!$token || !$password || !$confirm) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            exit;
        }

        if (strlen($password) < 8) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres']);
            exit;
        }

        if ($password !== $confirm) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Las contraseñas no coinciden']);
            exit;
        }

        $tokenObj = TokensAcceso::buscarValido($token);
        if (!$tokenObj) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Token inválido o expirado']);
            exit;
        }

        // Guardar contraseña con UPDATE directo
        $usuario = Usuarios::buscarPorCatalogo($tokenObj->catalogo);
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("UPDATE usuarios SET password = ?, primer_ingreso = 0 WHERE catalogo = ?");
        $stmt->execute([$passwordHash, $tokenObj->catalogo]);

        // Marcar token como usado con UPDATE directo
        $stmt2 = $db->prepare("UPDATE tokens_acceso SET usado = 1 WHERE id = ?");
        $stmt2->execute([$tokenObj->id]);

        echo json_encode(['codigo' => 1, 'mensaje' => 'Contraseña creada correctamente']);
    }

    // ── API: hacer login ──────────────────────────────────────────────────────
    public static function loginAPI(): void
    {
        getHeadersApi();
        $catalogo = trim($_POST['catalogo'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$catalogo || !$password) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            exit;
        }

        $usuario = Usuarios::buscarPorCatalogo($catalogo);

        if (!$usuario || !$usuario->verificarPassword($password)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Catálogo o contraseña incorrectos']);
            exit;
        }

        if (!$usuario->activo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario inactivo']);
            exit;
        }

        // Crear sesión
        $_SESSION['auth_user']   = $usuario->catalogo;
        $_SESSION['auth_rol']    = $usuario->rol;
        $_SESSION['auth_plaza']  = mb_convert_encoding($usuario->plaza, 'UTF-8', 'UTF-8');
        $_SESSION['auth_nombre'] = mb_convert_encoding($usuario->nombre_completo, 'UTF-8', 'UTF-8');
        $_SESSION['auth_grado']  = mb_convert_encoding($usuario->grado, 'UTF-8', 'UTF-8');
        $_SESSION['auth_arma']   = mb_convert_encoding($usuario->arma_servicio, 'UTF-8', 'UTF-8');
        $_SESSION[$usuario->rol] = true;

        echo json_encode([
            'codigo'  => 1,
            'mensaje' => $usuario->saludo(),
            'rol'     => $usuario->rol
        ]);
    }

    // ── API: logout ───────────────────────────────────────────────────────────
    public static function logoutAPI(): void
    {
        session_destroy();
        echo json_encode(['codigo' => 1, 'mensaje' => 'Sesión cerrada']);
    }

    // ── Helper: enviar email ──────────────────────────────────────────────────
    private static function enviarEmail(string $to, string $subject, string $html): bool
    {
        // Por ahora usamos mail() nativo
        // Después integramos Resend o Mailgun
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: VEHICULOS BHR <noreply@bhr.mil.gt>\r\n";

        return mail($to, $subject, $html, $headers);
    }


    public static function logoutGET(): void
    {
        session_destroy();
        $base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';
        header('Location: ' . $base . '/');
        exit;
    }
}
