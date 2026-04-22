<?php

namespace Controllers;

use Exception;
use Model\Chequeos;
use Model\Vehiculos;
use MVC\Router;

class ChequeoController
{
    // ── LISTAR CHEQUEOS DE UNA PLACA ─────────────────────────────────────────
    public static function listarAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_GET['placa'] ?? ''));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $chequeos = Chequeos::traerPorPlaca($placa);
            $tieneChequeoMes = Chequeos::tieneChequeoMesActual($placa);

            echo json_encode([
                'codigo'           => 1,
                'datos'            => $chequeos,
                'tiene_chequeo_mes' => $tieneChequeoMes,
                'items_definicion' => Chequeos::$ITEMS
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al listar chequeos',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── OBTENER UN CHEQUEO CON SUS ÍTEMS ─────────────────────────────────────
    public static function obtenerAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $chequeo = Chequeos::traerConItems($id);

            if (!$chequeo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Chequeo no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'codigo'  => 1,
                'datos'   => $chequeo,
                'items_definicion' => Chequeos::$ITEMS
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al obtener chequeo',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── CREAR CHEQUEO (inicia vacío) ──────────────────────────────────────────
    public static function crearAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim($_POST['placa'] ?? ''));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa requerida'], JSON_UNESCAPED_UNICODE);
            return;
        }
        

        try {
            $chequeo = new Chequeos([
                'placa'             => $placa,
                'fecha_chequeo'     => $_POST['fecha_chequeo']     ?? date('Y-m-d'),
                'km_al_chequeo'     => (int)($_POST['km_al_chequeo'] ?? 0),
                'realizado_por'     => htmlspecialchars($_POST['realizado_por']     ?? ''),
                'observaciones_gen' => htmlspecialchars($_POST['observaciones_gen'] ?? ''),
                'estado'            => 'Pendiente'
            ]);

            $resultado = $chequeo->crear();
            

            echo json_encode([
                'codigo'     => 1,
                'mensaje'    => 'Chequeo iniciado',
                'id_chequeo' => $resultado['id']
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al crear chequeo',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── GUARDAR ÍTEMS Y COMPLETAR CHEQUEO ────────────────────────────────────
    public static function completarAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $id    = (int)($_POST['id_chequeo'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID de chequeo requerido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (empty($items) || !is_array($items)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ítems requeridos'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $chequeo = Chequeos::find($id);

            if (!$chequeo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Chequeo no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Verificar que todos los ítems tengan resultado
            foreach ($items as $item) {
                if (empty($item['resultado'])) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo'  => 0,
                        'mensaje' => "El ítem {$item['numero_item']} no tiene resultado asignado"
                    ], JSON_UNESCAPED_UNICODE);
                    return;
                }
            }

            Chequeos::guardarItems($id, $items);

            $chequeo->sincronizar([
                'estado'            => 'Completado',
                'km_al_chequeo'     => !empty($_POST['km_al_chequeo']) ? (int)$_POST['km_al_chequeo'] : $chequeo->km_al_chequeo,
                'realizado_por'     => !empty($_POST['realizado_por']) ? htmlspecialchars($_POST['realizado_por']) : $chequeo->realizado_por,
                'observaciones_gen' => htmlspecialchars($_POST['observaciones_gen'] ?? $chequeo->observaciones_gen ?? '')
            ]);
            $chequeo->actualizar();

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Chequeo completado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al completar chequeo',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── ELIMINAR CHEQUEO ──────────────────────────────────────────────────────
    public static function eliminarAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');

        $id = (int)($_POST['id_chequeo'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $chequeo = Chequeos::find($id);

            if (!$chequeo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Chequeo no encontrado'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $chequeo->eliminar();

            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Chequeo eliminado'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al eliminar chequeo',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
