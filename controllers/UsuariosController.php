<?php

namespace Controllers;

use MVC\Router;
use Model\Usuarios;
use Model\TokensAcceso;

class UsuariosController
{
    // ── Vista principal ───────────────────────────────────────────────────────
    public static function index(Router $router): void
    {
        isAuth();
        if (($_SESSION['auth_rol'] ?? '') !== 'SUPERUSUARIO') {
            header('Location: ' . ($_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '') . '/vehiculos');
            exit;
        }
        $router->render('usuarios/index', []);
    }

    // ── API: listar usuarios ──────────────────────────────────────────────────
    public static function listarAPI(): void
    {
        isAuthApi();
        if (($_SESSION['auth_rol'] ?? '') !== 'SUPERUSUARIO') {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sin permisos']);
            exit;
        }

        $usuarios = Usuarios::fetchArray("
            SELECT catalogo, grado, arma_servicio, nombre_completo, 
                   plaza, correo, rol, activo, primer_ingreso
            FROM usuarios
            ORDER BY FIELD(rol, 'SUPERUSUARIO','COMTE_BHR','COMTE_CIA','COMTE_PTN'), nombre_completo
        ");

        echo json_encode(['codigo' => 1, 'datos' => $usuarios], JSON_UNESCAPED_UNICODE);
    }

    // ── API: crear usuario ────────────────────────────────────────────────────
    public static function crearAPI(): void
    {
        isAuthApi();
        if (($_SESSION['auth_rol'] ?? '') !== 'SUPERUSUARIO') {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sin permisos']);
            exit;
        }

        $catalogo       = trim($_POST['catalogo']       ?? '');
        $grado          = trim($_POST['grado']          ?? '');
        $arma_servicio  = trim($_POST['arma_servicio']  ?? '');
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $plaza          = trim($_POST['plaza']          ?? '');
        $rol            = trim($_POST['rol']            ?? 'COMTE_PTN');

        if (!$catalogo || !$grado || !$arma_servicio || !$nombre_completo || !$plaza) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Todos los campos son obligatorios']);
            exit;
        }

        $existe = Usuarios::buscarPorCatalogo($catalogo);
        if ($existe) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un usuario con ese catálogo']);
            exit;
        }

        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("
            INSERT INTO usuarios (catalogo, grado, arma_servicio, nombre_completo, plaza, rol, activo, primer_ingreso)
            VALUES (?, ?, ?, ?, ?, ?, 1, 1)
        ");
        $stmt->execute([$catalogo, $grado, $arma_servicio, $nombre_completo, $plaza, $rol]);

        echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario creado correctamente']);
    }

    // ── API: actualizar usuario ───────────────────────────────────────────────
    public static function actualizarAPI(): void
    {
        isAuthApi();
        if (($_SESSION['auth_rol'] ?? '') !== 'SUPERUSUARIO') {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sin permisos']);
            exit;
        }

        $catalogo       = trim($_POST['catalogo']       ?? '');
        $grado          = trim($_POST['grado']          ?? '');
        $arma_servicio  = trim($_POST['arma_servicio']  ?? '');
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $plaza          = trim($_POST['plaza']          ?? '');
        $rol            = trim($_POST['rol']            ?? '');

        if (!$catalogo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Catálogo requerido']);
            exit;
        }

        // No puede cambiar su propio rol
        if ($catalogo === $_SESSION['auth_user'] && $rol !== $_SESSION['auth_rol']) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'No puedes cambiar tu propio rol']);
            exit;
        }

        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("
            UPDATE usuarios SET 
                grado = ?, arma_servicio = ?, nombre_completo = ?, plaza = ?, rol = ?
            WHERE catalogo = ?
        ");
        $stmt->execute([$grado, $arma_servicio, $nombre_completo, $plaza, $rol, $catalogo]);

        echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario actualizado correctamente']);
    }

    // ── API: activar / desactivar ─────────────────────────────────────────────
    public static function toggleActivoAPI(): void
    {
        isAuthApi();
        if (($_SESSION['auth_rol'] ?? '') !== 'SUPERUSUARIO') {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sin permisos']);
            exit;
        }

        $catalogo = trim($_POST['catalogo'] ?? '');
        $activo   = (int)($_POST['activo']  ?? 0);

        if (!$catalogo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Catálogo requerido']);
            exit;
        }

        // No puede desactivarse a sí mismo
        if ($catalogo === $_SESSION['auth_user']) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'No puedes desactivar tu propio usuario']);
            exit;
        }

        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("UPDATE usuarios SET activo = ? WHERE catalogo = ?");
        $stmt->execute([$activo, $catalogo]);

        $msg = $activo ? 'Usuario activado' : 'Usuario desactivado';
        echo json_encode(['codigo' => 1, 'mensaje' => $msg]);
    }

    // ── API: resetear contraseña ──────────────────────────────────────────────
    public static function resetPasswordAPI(): void
    {
        isAuthApi();
        if (($_SESSION['auth_rol'] ?? '') !== 'SUPERUSUARIO') {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sin permisos']);
            exit;
        }

        $catalogo = trim($_POST['catalogo'] ?? '');

        if (!$catalogo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Catálogo requerido']);
            exit;
        }

        $usuario = Usuarios::buscarPorCatalogo($catalogo);
        if (!$usuario || !$usuario->correo) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no tiene correo registrado']);
            exit;
        }

        // Generar token
        $token = TokensAcceso::generar($catalogo);
        $token->guardar();

        // Resetear primer_ingreso para que pueda crear nueva contraseña
        $db = \Model\ActiveRecord::getDB();
        $stmt = $db->prepare("UPDATE usuarios SET primer_ingreso = 1, password = NULL WHERE catalogo = ?");
        $stmt->execute([$catalogo]);

        // Enviar email
        $base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';
        $link = ($_ENV['APP_URL'] ?? 'http://localhost:9002') . $base . '/auth/setup?token=' . $token->token;

        $enviado = AuthController::enviarEmailPublico(
            $usuario->correo,
            'Restablecer contraseña — VEHICULOS BHR',
            "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;
                background:#0f1117;color:#e8eaf0;padding:2rem;border-radius:12px;'>
                <h2 style='color:#e8b84b;'>Restablecer contraseña</h2>
                <p>Hola, <strong>{$usuario->grado} {$usuario->nombre_completo}</strong>.</p>
                <p style='color:#7c8398;'>
                    El administrador ha solicitado el restablecimiento de tu contraseña.<br>
                    Haz clic en el siguiente enlace para crear una nueva:
                </p>
                <a href='{$link}' style='display:inline-block;background:#e8b84b;color:#000;
                    padding:12px 28px;border-radius:8px;text-decoration:none;
                    font-weight:bold;margin:1rem 0;'>
                    Crear nueva contraseña
                </a>
                <p style='color:#555;font-size:12px;margin-top:1rem;'>
                    Este enlace expira en 2 horas.
                </p>
            </div>
            "
        );

        if (!$enviado) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al enviar el correo']);
            exit;
        }

        echo json_encode(['codigo' => 1, 'mensaje' => 'Correo de restablecimiento enviado a ' . $usuario->correo]);
    }
}
