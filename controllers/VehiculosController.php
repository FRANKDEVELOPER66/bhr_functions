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
        isAuth();
        $destacamentos = Destacamentos::obtenerUnidades();
        $router->render('vehiculos/index', [
            'destacamentos' => $destacamentos,
        ]);
    }

    // ── GUARDAR ──────────────────────────────────────────────────────────────
    public static function guardarAPI()
    {
        ob_start();
        header('Content-Type: application/json; charset=UTF-8');
        ob_clean();

        try {
            $db = \Model\ActiveRecord::getDB();
            $db->beginTransaction();

            // Sanitizar
            $_POST['placa']        = strtoupper(trim($_POST['placa']        ?? ''));
            $_POST['numero_serie'] = strtoupper(trim($_POST['numero_serie'] ?? ''));

            // Validar campos obligatorios
            foreach (['placa', 'numero_serie', 'marca', 'modelo', 'anio', 'color', 'tipo', 'estado', 'fecha_ingreso'] as $campo) {
                if (empty($_POST[$campo])) throw new \Exception("El campo {$campo} es obligatorio");
            }

            if (Vehiculos::existePlaca($_POST['placa']))
                throw new \Exception("Ya existe un vehículo con ese catálogo");

            if (Vehiculos::existeNumeroSerie($_POST['numero_serie']))
                throw new \Exception("Ya existe un vehículo con ese número de serie");

            // ── FOTO FRENTE ───────────────────────────────────────────────────
            $nombreFoto = null;
            if (!empty($_FILES['foto_frente']['name'])) {
                $nombreFoto = Vehiculos::subirArchivoSFTP($_FILES['foto_frente'], 'fotos', $_POST['placa']);
                if (!$nombreFoto) throw new \Exception("Error al subir la foto");
            }

            // ── FOTO LATERAL ──────────────────────────────────────────────────
            $nombreFotoLateral = null;
            if (!empty($_FILES['foto_lateral']['name'])) {
                $nombreFotoLateral = Vehiculos::subirArchivoSFTP($_FILES['foto_lateral'], 'fotos', $_POST['placa'] . '_lateral');
                if (!$nombreFotoLateral) throw new \Exception("Error al subir foto lateral");
            }

            // ── FOTO TRASERA ──────────────────────────────────────────────────
            $nombreFotoTrasera = null;
            if (!empty($_FILES['foto_trasera']['name'])) {
                $nombreFotoTrasera = Vehiculos::subirArchivoSFTP($_FILES['foto_trasera'], 'fotos', $_POST['placa'] . '_trasera');
                if (!$nombreFotoTrasera) throw new \Exception("Error al subir foto trasera");
            }

            // ── TARJETA PDF ───────────────────────────────────────────────────
            $nombreTarjeta = null;
            if (!empty($_FILES['tarjeta_pdf']['name'])) {
                $ext = strtolower(pathinfo($_FILES['tarjeta_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') throw new \Exception("La tarjeta debe ser PDF");
                $nombreTarjeta = Vehiculos::subirArchivoSFTP($_FILES['tarjeta_pdf'], 'tarjetas', $_POST['placa']);
                if (!$nombreTarjeta) throw new \Exception("Error al subir tarjeta PDF");
            }

            // ── CERT INVENTARIO ───────────────────────────────────────────────
            $nombreCertInventario = null;
            if (!empty($_FILES['cert_inventario']['name'])) {
                $ext = strtolower(pathinfo($_FILES['cert_inventario']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') throw new \Exception("La certificación de inventario debe ser PDF");
                $nombreCertInventario = Vehiculos::subirArchivoSFTP($_FILES['cert_inventario'], 'certificaciones', $_POST['placa'] . '_INV');
                if (!$nombreCertInventario) throw new \Exception("Error al subir certificación de inventario");
            }

            // ── CERT SICOIN ───────────────────────────────────────────────────
            $nombreCertSicoin = null;
            if (!empty($_FILES['cert_sicoin']['name'])) {
                $ext = strtolower(pathinfo($_FILES['cert_sicoin']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') throw new \Exception("La certificación SICOIN debe ser PDF");
                $nombreCertSicoin = Vehiculos::subirArchivoSFTP($_FILES['cert_sicoin'], 'certificaciones', $_POST['placa'] . '_SIC');
                if (!$nombreCertSicoin) throw new \Exception("Error al subir certificación SICOIN");
            }

            $_POST['foto_frente']    = $nombreFoto;
            $_POST['foto_lateral']   = $nombreFotoLateral;
            $_POST['foto_trasera']   = $nombreFotoTrasera;
            $_POST['tarjeta_pdf']    = $nombreTarjeta;
            $_POST['cert_inventario'] = $nombreCertInventario;
            $_POST['cert_sicoin']    = $nombreCertSicoin;

            // Guardar vehículo
            $vehiculo = new Vehiculos($_POST);
            $vehiculo->crear();

            // ── SEGURO ────────────────────────────────────────────────────────
            if (!empty($_POST['seg_aseguradora'])) {
                foreach (['seg_aseguradora', 'seg_numero_poliza', 'seg_fecha_inicio', 'seg_fecha_vencimiento'] as $campo) {
                    if (empty($_POST[$campo])) throw new \Exception("Faltan campos obligatorios del seguro");
                }
                if (\Model\Seguros::existePoliza($_POST['seg_numero_poliza']))
                    throw new \Exception("Ya existe esa póliza");

                $nombrePolizaPdf = null;
                if (!empty($_FILES['archivo_poliza']['name'])) {
                    $ext = strtolower(pathinfo($_FILES['archivo_poliza']['name'], PATHINFO_EXTENSION));
                    if ($ext !== 'pdf') throw new \Exception("La póliza debe ser PDF");
                    $nombrePolizaPdf = Vehiculos::subirArchivoSFTP($_FILES['archivo_poliza'], 'polizas', $_POST['placa'] . '_POL');
                    if (!$nombrePolizaPdf) throw new \Exception("Error al subir póliza");
                }

                $seguro = new \Model\Seguros([
                    'placa'             => $_POST['placa'],
                    'aseguradora'       => $_POST['seg_aseguradora'],
                    'numero_poliza'     => $_POST['seg_numero_poliza'],
                    'tipo_cobertura'    => $_POST['seg_tipo_cobertura'] ?? 'Básico',
                    'fecha_inicio'      => $_POST['seg_fecha_inicio'],
                    'fecha_vencimiento' => $_POST['seg_fecha_vencimiento'],
                    'prima_anual'       => $_POST['seg_prima_anual']       ?? null,
                    'agente_contacto'   => $_POST['seg_agente_contacto']   ?? null,
                    'telefono_agente'   => $_POST['seg_telefono_agente']   ?? null,
                    'archivo_poliza'    => $nombrePolizaPdf,
                    'estado'            => 'Vigente',
                    'observaciones'     => $_POST['seg_observaciones']     ?? null
                ]);
                $seguro->crear();
            }

            $db->commit();
            echo json_encode(['codigo' => 1, 'mensaje' => 'Vehículo registrado correctamente']);
        } catch (\Throwable $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al registrar', 'detalle' => $e->getMessage()]);
        }
    }

    // ── BUSCAR ───────────────────────────────────────────────────────────────
    public static function buscarAPI()
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $vehiculos = Vehiculos::traerVehiculos();
            $urlBase   = rtrim($_ENV['SFTP_PUBLIC_URL'] ?? '', '/');

            foreach ($vehiculos as &$v) {
                $v['foto_url']            = $v['foto_frente']    ? "{$urlBase}/{$v['foto_frente']}"    : null;
                $v['foto_lateral_url']    = $v['foto_lateral']   ? "{$urlBase}/{$v['foto_lateral']}"   : null;
                $v['foto_trasera_url']    = $v['foto_trasera']   ? "{$urlBase}/{$v['foto_trasera']}"   : null;
                $v['pdf_url']             = $v['tarjeta_pdf']    ? "{$urlBase}/{$v['tarjeta_pdf']}"    : null;
                $v['cert_inventario_url'] = $v['cert_inventario'] ? "{$urlBase}/{$v['cert_inventario']}" : null;
                $v['cert_sicoin_url']     = $v['cert_sicoin']    ? "{$urlBase}/{$v['cert_sicoin']}"    : null;
            }
            unset($v);

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

            // ── FOTO FRENTE ───────────────────────────────────────────────────
            if (!empty($_FILES['foto_frente']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto_frente']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La foto debe ser JPG, PNG o WEBP'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($vehiculo->foto_frente) Vehiculos::eliminarArchivoSFTP('fotos', $vehiculo->foto_frente);
                $nueva = Vehiculos::subirArchivoSFTP($_FILES['foto_frente'], 'fotos', $placa);
                if ($nueva) $_POST['foto_frente'] = $nueva;
            } else {
                unset($_POST['foto_frente']);
            }

            // ── FOTO LATERAL ──────────────────────────────────────────────────
            if (!empty($_FILES['foto_lateral']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto_lateral']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La foto lateral debe ser JPG, PNG o WEBP'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($vehiculo->foto_lateral) Vehiculos::eliminarArchivoSFTP('fotos', $vehiculo->foto_lateral);
                $nueva = Vehiculos::subirArchivoSFTP($_FILES['foto_lateral'], 'fotos', $placa . '_lateral');
                if ($nueva) $_POST['foto_lateral'] = $nueva;
            } else {
                unset($_POST['foto_lateral']);
            }

            // ── FOTO TRASERA ──────────────────────────────────────────────────
            if (!empty($_FILES['foto_trasera']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto_trasera']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La foto trasera debe ser JPG, PNG o WEBP'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($vehiculo->foto_trasera) Vehiculos::eliminarArchivoSFTP('fotos', $vehiculo->foto_trasera);
                $nueva = Vehiculos::subirArchivoSFTP($_FILES['foto_trasera'], 'fotos', $placa . '_trasera');
                if ($nueva) $_POST['foto_trasera'] = $nueva;
            } else {
                unset($_POST['foto_trasera']);
            }

            // ── TARJETA PDF ───────────────────────────────────────────────────
            if (!empty($_FILES['tarjeta_pdf']['name'])) {
                $ext = strtolower(pathinfo($_FILES['tarjeta_pdf']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La tarjeta debe ser PDF'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($vehiculo->tarjeta_pdf) Vehiculos::eliminarArchivoSFTP('tarjetas', $vehiculo->tarjeta_pdf);
                $nuevo = Vehiculos::subirArchivoSFTP($_FILES['tarjeta_pdf'], 'tarjetas', $placa);
                if ($nuevo) $_POST['tarjeta_pdf'] = $nuevo;
            } else {
                unset($_POST['tarjeta_pdf']);
            }

            // ── CERT INVENTARIO ───────────────────────────────────────────────
            if (!empty($_FILES['cert_inventario']['name'])) {
                $ext = strtolower(pathinfo($_FILES['cert_inventario']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La certificación de inventario debe ser PDF'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($vehiculo->cert_inventario) Vehiculos::eliminarArchivoSFTP('certificaciones', $vehiculo->cert_inventario);
                $nuevo = Vehiculos::subirArchivoSFTP($_FILES['cert_inventario'], 'certificaciones', $placa . '_INV');
                if ($nuevo) $_POST['cert_inventario'] = $nuevo;
            } else {
                unset($_POST['cert_inventario']);
            }

            // ── CERT SICOIN ───────────────────────────────────────────────────
            if (!empty($_FILES['cert_sicoin']['name'])) {
                $ext = strtolower(pathinfo($_FILES['cert_sicoin']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La certificación SICOIN debe ser PDF'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($vehiculo->cert_sicoin) Vehiculos::eliminarArchivoSFTP('certificaciones', $vehiculo->cert_sicoin);
                $nuevo = Vehiculos::subirArchivoSFTP($_FILES['cert_sicoin'], 'certificaciones', $placa . '_SIC');
                if ($nuevo) $_POST['cert_sicoin'] = $nuevo;
            } else {
                unset($_POST['cert_sicoin']);
            }

            $vehiculo->sincronizar($_POST);
            $vehiculo->actualizar();

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

            // Eliminar todos los archivos del SFTP
            if ($vehiculo->foto_frente)    Vehiculos::eliminarArchivoSFTP('fotos',           $vehiculo->foto_frente);
            if ($vehiculo->foto_lateral)   Vehiculos::eliminarArchivoSFTP('fotos',           $vehiculo->foto_lateral);
            if ($vehiculo->foto_trasera)   Vehiculos::eliminarArchivoSFTP('fotos',           $vehiculo->foto_trasera);
            if ($vehiculo->tarjeta_pdf)    Vehiculos::eliminarArchivoSFTP('tarjetas',        $vehiculo->tarjeta_pdf);
            if ($vehiculo->cert_inventario) Vehiculos::eliminarArchivoSFTP('certificaciones', $vehiculo->cert_inventario);
            if ($vehiculo->cert_sicoin)    Vehiculos::eliminarArchivoSFTP('certificaciones', $vehiculo->cert_sicoin);

            $vehiculo->eliminar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Vehículo eliminado exitosamente'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar el vehículo', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── SERVIR FOTO ───────────────────────────────────────────────────────────
    public static function servirFoto(Router $router)
    {
        if (ob_get_level()) ob_end_clean();

        $archivo = basename($_GET['archivo'] ?? '');

        if (!$archivo) {
            http_response_code(400);
            exit;
        }

        // Detectar carpeta según nombre del archivo
        if (strpos($archivo, '_fotos_') !== false || strpos($archivo, '_lateral_') !== false || strpos($archivo, '_trasera_') !== false) {
            $carpeta = 'fotos';
        } elseif (strpos($archivo, '_tarjetas_') !== false) {
            $carpeta = 'tarjetas';
        } elseif (strpos($archivo, '_POL_') !== false) {
            $carpeta = 'polizas';
        } elseif (strpos($archivo, '_INV_') !== false || strpos($archivo, '_SIC_') !== false || strpos($archivo, '_CERT_') !== false) {
            $carpeta = 'certificaciones';
        } elseif (strpos($archivo, '_ACC_') !== false || strpos($archivo, '_FOTO_') !== false || strpos($archivo, '_INF_') !== false) {
            $carpeta = 'accidentes';
        } else {
            $carpeta = 'fotos';
        }

        $rutaBase = rtrim($_ENV['SFTP_PATH'] ?? '/upload', '/');
        $sftp     = new \phpseclib3\Net\SFTP($_ENV['SFTP_HOST'], (int)$_ENV['SFTP_PORT']);
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
