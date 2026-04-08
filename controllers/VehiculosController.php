<?php

namespace Controllers;

use Exception;
use Model\Destacamentos;
use Model\Vehiculos;
use MVC\Router;

class VehiculosController
{
    public static function index(Router $router)
    {
        $destacamentos = Destacamentos::obtenerUnidades();

        $router->render('vehiculos/index', [
            'destacamentos' => $destacamentos,
        ]);
    }

    // ── GUARDAR ──────────────────────────────────────────────────────────────
    public static function guardarAPI()
    {
        // Esto debe ir PRIMERO, antes de cualquier otra cosa
        ob_start();
        header('Content-Type: application/json; charset=UTF-8');

        // Limpiar cualquier output previo (warnings de PHP, etc.)
        ob_clean();
        header('Content-Type: application/json; charset=UTF-8');

        $_POST['placa']         = strtoupper(trim(htmlspecialchars($_POST['placa']         ?? '')));
        $_POST['numero_serie']  = strtoupper(trim(htmlspecialchars($_POST['numero_serie']  ?? '')));
        $_POST['marca']         = htmlspecialchars($_POST['marca']         ?? '');
        $_POST['modelo']        = htmlspecialchars($_POST['modelo']        ?? '');
        $_POST['color']         = htmlspecialchars($_POST['color']         ?? '');
        $_POST['tipo']          = htmlspecialchars($_POST['tipo']          ?? '');
        $_POST['observaciones'] = htmlspecialchars($_POST['observaciones'] ?? '');
        $_POST['km_actuales']   = (int)($_POST['km_actuales'] ?? 0);

        // Validar obligatorios
        foreach (['placa', 'numero_serie', 'marca', 'modelo', 'anio', 'color', 'tipo', 'estado', 'fecha_ingreso'] as $campo) {
            if (empty($_POST[$campo])) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "El campo '{$campo}' es obligatorio"], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        if (Vehiculos::existePlaca($_POST['placa'])) {
            http_response_code(409);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un vehículo con ese Catalogo'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (Vehiculos::existeNumeroSerie($_POST['numero_serie'])) {
            http_response_code(409);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un vehículo con ese número de serie'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // ── Subir foto de frente ─────────────────────────────────────────
            $nombreFoto = null;
            if (!empty($_FILES['foto_frente']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto_frente']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La foto debe ser JPG, PNG o WEBP'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $nombreFoto = Vehiculos::subirArchivoSFTP($_FILES['foto_frente'], 'fotos', $_POST['placa']);
                if (!$nombreFoto) {
                    http_response_code(500);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'Error al subir la foto al servidor'], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            // ── Subir tarjeta PDF ────────────────────────────────────────────
            $nombrePdf = null;
            if (!empty($_FILES['tarjeta_pdf']['name'])) {
                $ext = strtolower(pathinfo($_FILES['tarjeta_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La tarjeta de circulación debe ser PDF'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $nombrePdf = Vehiculos::subirArchivoSFTP($_FILES['tarjeta_pdf'], 'tarjetas', $_POST['placa']);
                if (!$nombrePdf) {
                    http_response_code(500);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'Error al subir el PDF al servidor'], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            $_POST['foto_frente'] = $nombreFoto;
            $_POST['tarjeta_pdf'] = $nombrePdf;

            $vehiculo = new Vehiculos($_POST);
            $vehiculo->crear();

            http_response_code(200);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Vehículo registrado exitosamente'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al registrar el vehículo', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── BUSCAR ───────────────────────────────────────────────────────────────
    public static function buscarAPI()
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $vehiculos = Vehiculos::traerVehiculos();

            // Construir URL pública de la foto
            $urlBase = rtrim($_ENV['SFTP_PUBLIC_URL'] ?? '', '/');
            foreach ($vehiculos as &$v) {
                $v['foto_url'] = $v['foto_frente']
                    ? "{$urlBase}/{$v['foto_frente']}"   // ← solo el nombre del archivo
                    : null;
                $v['pdf_url'] = $v['tarjeta_pdf']
                    ? "{$urlBase}/{$v['tarjeta_pdf']}"
                    : null;
            }
            unset($v);

            http_response_code(200);
            echo json_encode(['codigo' => 1, 'datos' => $vehiculos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al buscar vehículos', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── MODIFICAR ────────────────────────────────────────────────────────────
    public static function modificarAPI()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim(htmlspecialchars($_POST['placa'] ?? '')));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa no válida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $_POST['marca']         = htmlspecialchars($_POST['marca']         ?? '');
        $_POST['modelo']        = htmlspecialchars($_POST['modelo']        ?? '');
        $_POST['color']         = htmlspecialchars($_POST['color']         ?? '');
        $_POST['tipo']          = htmlspecialchars($_POST['tipo']          ?? '');
        $_POST['observaciones'] = htmlspecialchars($_POST['observaciones'] ?? '');
        $_POST['km_actuales']   = (int)($_POST['km_actuales'] ?? 0);

        if (!empty($_POST['numero_serie'])) {
            $_POST['numero_serie'] = strtoupper(trim(htmlspecialchars($_POST['numero_serie'])));
            if (Vehiculos::existeNumeroSerie($_POST['numero_serie'], $placa)) {
                http_response_code(409);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Número de serie ya en uso'], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        try {
            $vehiculo = Vehiculos::find($placa);

            if (!$vehiculo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Vehículo no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ── Reemplazar foto si se sube una nueva ─────────────────────────
            if (!empty($_FILES['foto_frente']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto_frente']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La foto debe ser JPG, PNG o WEBP'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                // Eliminar foto anterior
                if ($vehiculo->foto_frente) {
                    Vehiculos::eliminarArchivoSFTP('fotos', $vehiculo->foto_frente);
                }
                $nuevaFoto = Vehiculos::subirArchivoSFTP($_FILES['foto_frente'], 'fotos', $placa);
                if ($nuevaFoto) $_POST['foto_frente'] = $nuevaFoto;
            } else {
                // Conservar la foto actual
                unset($_POST['foto_frente']);
            }

            // ── Reemplazar PDF si se sube uno nuevo ──────────────────────────
            if (!empty($_FILES['tarjeta_pdf']['name'])) {
                $ext = strtolower(pathinfo($_FILES['tarjeta_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La tarjeta debe ser PDF'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($vehiculo->tarjeta_pdf) {
                    Vehiculos::eliminarArchivoSFTP('tarjetas', $vehiculo->tarjeta_pdf);
                }
                $nuevoPdf = Vehiculos::subirArchivoSFTP($_FILES['tarjeta_pdf'], 'tarjetas', $placa);
                if ($nuevoPdf) $_POST['tarjeta_pdf'] = $nuevoPdf;
            } else {
                unset($_POST['tarjeta_pdf']);
            }

            $vehiculo->sincronizar($_POST);
            $vehiculo->actualizar();

            http_response_code(200);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Vehículo modificado exitosamente'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar el vehículo', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── ELIMINAR ─────────────────────────────────────────────────────────────
    public static function eliminarAPI()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim(htmlspecialchars($_POST['placa'] ?? '')));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa no válida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $vehiculo = Vehiculos::find($placa);

            if (!$vehiculo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Vehículo no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Eliminar archivos del SFTP antes de borrar el registro
            if ($vehiculo->foto_frente) Vehiculos::eliminarArchivoSFTP('fotos',    $vehiculo->foto_frente);
            if ($vehiculo->tarjeta_pdf) Vehiculos::eliminarArchivoSFTP('tarjetas', $vehiculo->tarjeta_pdf);

            $vehiculo->eliminar();

            http_response_code(200);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Vehículo eliminado exitosamente'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar el vehículo', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }


    public static function servirFoto(Router $router)
    {
        // Limpiar cualquier output buffer del router
        if (ob_get_level()) ob_end_clean();

        $archivo = basename($_GET['archivo'] ?? '');

        if (!$archivo) {
            http_response_code(400);
            exit;
        }

        $carpeta  = strpos($archivo, '_fotos_') !== false ? 'fotos' : 'tarjetas';
        $rutaBase = rtrim($_ENV['SFTP_PATH'] ?? '/vehiculos', '/');

        $sftp = new \phpseclib3\Net\SFTP($_ENV['SFTP_HOST'], (int)$_ENV['SFTP_PORT']);
        $sftp->login($_ENV['SFTP_USER'], $_ENV['SFTP_PASS']);

        $contenido = $sftp->get("{$rutaBase}/{$carpeta}/{$archivo}");

        if (!$contenido) {
            http_response_code(404);
            exit;
        }

        $ext  = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            'pdf'         => 'application/pdf',
            default       => 'application/octet-stream'
        };

        header("Content-Type: {$mime}");
        header("Cache-Control: max-age=86400");
        echo $contenido;
        exit;
    }
}
