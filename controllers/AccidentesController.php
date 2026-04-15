<?php

namespace Controllers;

use Exception;
use Model\Accidentes;
use Model\Seguros;
use Model\Vehiculos;
use MVC\Router;

class AccidentesController
{
    // ── LISTAR ACCIDENTES DE UNA PLACA ────────────────────────────────────────
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
            $accidentes  = Accidentes::traerPorPlaca($placa);
            $costoTotal  = Accidentes::costoTotal($placa);
            $activos     = Accidentes::contarActivos($placa);

            $urlBase = rtrim($_ENV['SFTP_PUBLIC_URL'] ?? '', '/');
            foreach ($accidentes as &$a) {
                $a['fotos_url']   = $a['archivo_fotos']
                    ? "{$urlBase}/{$a['archivo_fotos']}"
                    : null;
                $a['informe_url'] = $a['archivo_informe']
                    ? "{$urlBase}/{$a['archivo_informe']}"
                    : null;
            }
            unset($a);

            echo json_encode([
                'codigo'      => 1,
                'datos'       => $accidentes,
                'costo_total' => $costoTotal,
                'activos'     => $activos
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al listar accidentes',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── GUARDAR ACCIDENTE ─────────────────────────────────────────────────────
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
        foreach (['fecha_accidente', 'lugar', 'tipo_accidente', 'descripcion'] as $campo) {
            if (empty($_POST[$campo])) {
                http_response_code(400);
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => "El campo '{$campo}' es obligatorio"
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        try {
            // ── Subir archivo de fotos (ZIP o PDF) ───────────────────────────
            $nombreFotos = null;
            if (!empty($_FILES['archivo_fotos']['name'])) {
                $ext = strtolower(pathinfo($_FILES['archivo_fotos']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'zip', 'jpg', 'jpeg', 'png'])) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'El archivo de fotos debe ser PDF, ZIP, JPG o PNG'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $nombreFotos = Vehiculos::subirArchivoSFTP(
                    $_FILES['archivo_fotos'],
                    'accidentes',
                    $placa . '_FOTO_' . time()
                );
                if (!$nombreFotos) {
                    http_response_code(500);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'Error al subir el archivo de fotos'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            // ── Subir informe policial (PDF) ──────────────────────────────────
            $nombreInforme = null;
            if (!empty($_FILES['archivo_informe']['name'])) {
                $ext = strtolower(pathinfo($_FILES['archivo_informe']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'El informe debe ser PDF'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $nombreInforme = Vehiculos::subirArchivoSFTP(
                    $_FILES['archivo_informe'],
                    'accidentes',
                    $placa . '_INF_' . time()
                );
                if (!$nombreInforme) {
                    http_response_code(500);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'Error al subir el informe'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            // ── Resolver id_seguro ────────────────────────────────────────────
            // Si no viene explícito, intentar asociar el seguro vigente
            $idSeguro = !empty($_POST['id_seguro'])
                ? (int)$_POST['id_seguro']
                : null;

            if (!$idSeguro) {
                $vigente  = Seguros::traerVigente($placa);
                $idSeguro = $vigente ? (int)$vigente['id_seguro'] : null;
            }

            // Si no hay seguro vigente, el estado_caso pasa a Sin seguro
            $estadoCaso = $_POST['estado_caso'] ?? 'Reportado';
            if (!$idSeguro && $estadoCaso === 'Reportado') {
                $estadoCaso = 'Sin seguro';
            }

            $accidente = new Accidentes([
                'placa'                 => $placa,
                'id_seguro'             => $idSeguro,
                'fecha_accidente'       => $_POST['fecha_accidente'],
                'lugar'                 => htmlspecialchars(trim($_POST['lugar'])),
                'tipo_accidente'        => $_POST['tipo_accidente']        ?? 'Colisión',
                'descripcion'           => htmlspecialchars(trim($_POST['descripcion'])),
                'conductor_responsable' => htmlspecialchars($_POST['conductor_responsable'] ?? ''),
                'costo_estimado'        => !empty($_POST['costo_estimado'])
                    ? (float)$_POST['costo_estimado']
                    : null,
                'costo_real'            => !empty($_POST['costo_real'])
                    ? (float)$_POST['costo_real']
                    : null,
                'estado_caso'           => $estadoCaso,
                'numero_expediente'     => htmlspecialchars($_POST['numero_expediente'] ?? ''),
                'archivo_fotos'         => $nombreFotos,
                'archivo_informe'       => $nombreInforme,
                'observaciones'         => htmlspecialchars($_POST['observaciones']     ?? '')
            ]);

            $accidente->crear();

            http_response_code(200);
            echo json_encode([
                'codigo'      => 1,
                'mensaje'     => 'Accidente registrado exitosamente',
                'id_seguro'   => $idSeguro,
                'estado_caso' => $estadoCaso
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al registrar el accidente',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── MODIFICAR ACCIDENTE ───────────────────────────────────────────────────
    public static function modificarAPI(Router $router)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_accidente'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID de accidente inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $accidente = Accidentes::find($id);

            if (!$accidente) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Accidente no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ── Reemplazar fotos si se sube archivo nuevo ─────────────────────
            if (!empty($_FILES['archivo_fotos']['name'])) {
                $ext = strtolower(pathinfo($_FILES['archivo_fotos']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'zip', 'jpg', 'jpeg', 'png'])) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'El archivo de fotos debe ser PDF, ZIP, JPG o PNG'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($accidente->archivo_fotos) {
                    Vehiculos::eliminarArchivoSFTP('accidentes', $accidente->archivo_fotos);
                }
                $nuevoFotos = Vehiculos::subirArchivoSFTP(
                    $_FILES['archivo_fotos'],
                    'accidentes',
                    $accidente->placa . '_FOTO_' . time()
                );
                if ($nuevoFotos) $_POST['archivo_fotos'] = $nuevoFotos;
            } else {
                unset($_POST['archivo_fotos']);
            }

            // ── Reemplazar informe si se sube uno nuevo ───────────────────────
            if (!empty($_FILES['archivo_informe']['name'])) {
                $ext = strtolower(pathinfo($_FILES['archivo_informe']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    http_response_code(400);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => 'El informe debe ser PDF'
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($accidente->archivo_informe) {
                    Vehiculos::eliminarArchivoSFTP('accidentes', $accidente->archivo_informe);
                }
                $nuevoInforme = Vehiculos::subirArchivoSFTP(
                    $_FILES['archivo_informe'],
                    'accidentes',
                    $accidente->placa . '_INF_' . time()
                );
                if ($nuevoInforme) $_POST['archivo_informe'] = $nuevoInforme;
            } else {
                unset($_POST['archivo_informe']);
            }

            $accidente->sincronizar([
                'id_seguro'             => !empty($_POST['id_seguro'])
                    ? (int)$_POST['id_seguro']
                    : $accidente->id_seguro,
                'fecha_accidente'       => $_POST['fecha_accidente']       ?? $accidente->fecha_accidente,
                'lugar'                 => htmlspecialchars(trim($_POST['lugar']              ?? $accidente->lugar)),
                'tipo_accidente'        => $_POST['tipo_accidente']        ?? $accidente->tipo_accidente,
                'descripcion'           => htmlspecialchars(trim($_POST['descripcion']        ?? $accidente->descripcion)),
                'conductor_responsable' => htmlspecialchars($_POST['conductor_responsable']   ?? $accidente->conductor_responsable ?? ''),
                'costo_estimado'        => !empty($_POST['costo_estimado'])
                    ? (float)$_POST['costo_estimado']
                    : $accidente->costo_estimado,
                'costo_real'            => !empty($_POST['costo_real'])
                    ? (float)$_POST['costo_real']
                    : $accidente->costo_real,
                'estado_caso'           => $_POST['estado_caso']           ?? $accidente->estado_caso,
                'numero_expediente'     => htmlspecialchars($_POST['numero_expediente']       ?? $accidente->numero_expediente ?? ''),
                'archivo_fotos'         => $_POST['archivo_fotos']         ?? $accidente->archivo_fotos,
                'archivo_informe'       => $_POST['archivo_informe']       ?? $accidente->archivo_informe,
                'observaciones'         => htmlspecialchars($_POST['observaciones']           ?? $accidente->observaciones ?? '')
            ]);

            $accidente->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Accidente actualizado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al modificar el accidente',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── ELIMINAR ACCIDENTE ────────────────────────────────────────────────────
    public static function eliminarAPI(Router $router)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_accidente'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $accidente = Accidentes::find($id);

            if (!$accidente) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Accidente no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Eliminar archivos del SFTP si existen
            if ($accidente->archivo_fotos) {
                Vehiculos::eliminarArchivoSFTP('accidentes', $accidente->archivo_fotos);
            }
            if ($accidente->archivo_informe) {
                Vehiculos::eliminarArchivoSFTP('accidentes', $accidente->archivo_informe);
            }

            $accidente->eliminar();

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Accidente eliminado correctamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al eliminar el accidente',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
