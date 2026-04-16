<?php

namespace Controllers;

use Exception;
use Model\Seguros;
use Model\Vehiculos;
use MVC\Router;

class SegurosController
{
    // ── LISTAR SEGUROS DE UNA PLACA ──────────────────────────────────────────
    public static function listarAPI(Router $router)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_GET['placa'] ?? ''));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Actualizar estados vencidos antes de listar
            Seguros::actualizarEstadosVencidos($placa);

            $seguros = Seguros::traerPorPlaca($placa);

            // Agregar flags útiles para el frontend
            $urlBase = rtrim($_ENV['SFTP_PUBLIC_URL'] ?? '', '/');
            foreach ($seguros as &$s) {
                $s['poliza_url'] = $s['archivo_poliza']
                    ? "{$urlBase}/{$s['archivo_poliza']}"
                    : null;
                $s['proximo_vencer']   = false;
                $s['dias_para_vencer'] = null;

                if ($s['estado'] === 'Vigente' && $s['fecha_vencimiento']) {
                    $vence = new \DateTime($s['fecha_vencimiento']);
                    $hoy   = new \DateTime();
                    if ($vence > $hoy) {
                        $diff                  = (int)$hoy->diff($vence)->days;
                        $s['dias_para_vencer'] = $diff;
                        $s['proximo_vencer']   = $diff <= 30;
                    }
                }
            }
            unset($s);

            echo json_encode([
                'codigo'  => 1,
                'datos'   => $seguros,
                'vigente' => Seguros::traerVigente($placa)
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al listar seguros',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── GUARDAR SEGURO ───────────────────────────────────────────────────────
    public static function guardarAPI(Router $router)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim(htmlspecialchars($_POST['placa'] ?? '')));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validar campos obligatorios
        foreach (['aseguradora', 'numero_poliza', 'fecha_inicio', 'fecha_vencimiento'] as $campo) {
            if (empty($_POST[$campo])) {
                http_response_code(400);
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => "El campo '{$campo}' es obligatorio"
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        // Validar que no exista la póliza
        if (Seguros::existePoliza(trim($_POST['numero_poliza']))) {
            http_response_code(409);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Ya existe un seguro registrado con ese número de póliza'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validar fechas
        if ($_POST['fecha_vencimiento'] <= $_POST['fecha_inicio']) {
            http_response_code(400);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'La fecha de vencimiento debe ser posterior a la fecha de inicio'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Subir archivo de póliza si viene
            $nombreArchivo = null;
            if (!empty($_FILES['archivo_poliza']['name'])) {
                $ext = strtolower(pathinfo($_FILES['archivo_poliza']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'El archivo de póliza debe ser PDF'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $nombreArchivo = Vehiculos::subirArchivoSFTP(
                    $_FILES['archivo_poliza'],
                    'polizas',
                    $placa . '_POL'
                );
                if (!$nombreArchivo) {
                    http_response_code(500);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'Error al subir el archivo de póliza'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            $seguro = new Seguros([
                'placa'             => $placa,
                'aseguradora'       => htmlspecialchars(trim($_POST['aseguradora'])),
                'numero_poliza'     => htmlspecialchars(trim($_POST['numero_poliza'])),
                'tipo_cobertura'    => $_POST['tipo_cobertura']    ?? 'Básico',
                'fecha_inicio'      => $_POST['fecha_inicio'],
                'fecha_vencimiento' => $_POST['fecha_vencimiento'],
                'prima_anual'       => !empty($_POST['prima_anual'])
                    ? (float)$_POST['prima_anual']
                    : null,
                'agente_contacto'   => htmlspecialchars($_POST['agente_contacto']  ?? ''),
                'telefono_agente'   => htmlspecialchars($_POST['telefono_agente']  ?? ''),
                'archivo_poliza'    => $nombreArchivo,
                'estado'            => 'Vigente',
                'observaciones'     => htmlspecialchars($_POST['observaciones']    ?? '')
            ]);

            $seguro->crear();

            http_response_code(200);
            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Seguro registrado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al registrar el seguro',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── MODIFICAR SEGURO ─────────────────────────────────────────────────────
    public static function modificarAPI(Router $router)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_seguro'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID de seguro inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validar fechas si vienen ambas
        if (
            !empty($_POST['fecha_inicio']) &&
            !empty($_POST['fecha_vencimiento']) &&
            $_POST['fecha_vencimiento'] <= $_POST['fecha_inicio']
        ) {
            http_response_code(400);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'La fecha de vencimiento debe ser posterior a la fecha de inicio'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $seguro = Seguros::find($id);

            if (!$seguro) {
                http_response_code(404);
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => 'Seguro no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Validar unicidad de póliza si cambió
            $nuevaPoliza = trim($_POST['numero_poliza'] ?? '');
            if ($nuevaPoliza && $nuevaPoliza !== $seguro->numero_poliza) {
                if (Seguros::existePoliza($nuevaPoliza, $id)) {
                    http_response_code(409);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'Ese número de póliza ya está en uso'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            // Reemplazar PDF si se sube uno nuevo
            if (!empty($_FILES['archivo_poliza']['name'])) {
                $ext = strtolower(pathinfo($_FILES['archivo_poliza']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'El archivo de póliza debe ser PDF'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }

                // Eliminar el PDF anterior
                if ($seguro->archivo_poliza) {
                    Vehiculos::eliminarArchivoSFTP('polizas', $seguro->archivo_poliza);
                }

                $nuevoArchivo = Vehiculos::subirArchivoSFTP(
                    $_FILES['archivo_poliza'],
                    'polizas',
                    $seguro->placa . '_POL_' . time()
                );
                if ($nuevoArchivo) $_POST['archivo_poliza'] = $nuevoArchivo;
            } else {
                // Conservar el archivo actual
                unset($_POST['archivo_poliza']);
            }

            $seguro->sincronizar([
                'aseguradora'       => htmlspecialchars(trim($_POST['aseguradora']       ?? $seguro->aseguradora)),
                'numero_poliza'     => htmlspecialchars(trim($_POST['numero_poliza']      ?? $seguro->numero_poliza)),
                'tipo_cobertura'    => $_POST['tipo_cobertura']    ?? $seguro->tipo_cobertura,
                'fecha_inicio'      => $_POST['fecha_inicio']      ?? $seguro->fecha_inicio,
                'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? $seguro->fecha_vencimiento,
                'prima_anual'     => !empty($_POST['prima_anual']) && $_POST['prima_anual'] !== ''
                    ? (float)$_POST['prima_anual']
                    : null,
                'agente_contacto' => htmlspecialchars(trim($_POST['agente_contacto'] ?? $seguro->agente_contacto ?? '')),
                'telefono_agente' => htmlspecialchars(trim($_POST['telefono_agente'] ?? $seguro->telefono_agente ?? '')),
                'archivo_poliza'    => $_POST['archivo_poliza']    ?? $seguro->archivo_poliza,
                'estado'            => $_POST['estado']            ?? $seguro->estado,
                'observaciones'     => htmlspecialchars($_POST['observaciones']    ?? $seguro->observaciones    ?? '')
            ]);

            $seguro->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Seguro actualizado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al modificar el seguro',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── CANCELAR SEGURO ──────────────────────────────────────────────────────
    public static function cancelarAPI(Router $router)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_seguro'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $seguro = Seguros::find($id);

            if (!$seguro) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Seguro no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $seguro->sincronizar(['estado' => 'Cancelado']);
            $seguro->actualizar();

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Seguro cancelado correctamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al cancelar el seguro',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── ELIMINAR SEGURO ──────────────────────────────────────────────────────
    public static function eliminarAPI(Router $router)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_seguro'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $seguro = Seguros::find($id);

            if (!$seguro) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Seguro no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Eliminar PDF del SFTP si existe
            if ($seguro->archivo_poliza) {
                Vehiculos::eliminarArchivoSFTP('polizas', $seguro->archivo_poliza);
            }

            $seguro->eliminar();

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Seguro eliminado correctamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al eliminar el seguro',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
