<?php

namespace Controllers;

use Exception;
use Model\Vehiculos;
use MVC\Router;

class VehiculosController
{
    public static function index(Router $router)
    {
        $router->render('vehiculos/index', []);
    }

    // ----------------------------------------------------------------
    // API: Guardar vehículo nuevo
    // ----------------------------------------------------------------
    public static function guardarAPI()
    {
        header('Content-Type: application/json; charset=UTF-8');

        // Sanitizar entradas
        $_POST['placa']         = strtoupper(trim(htmlspecialchars($_POST['placa']         ?? '')));
        $_POST['numero_serie']  = strtoupper(trim(htmlspecialchars($_POST['numero_serie']  ?? '')));
        $_POST['marca']         = htmlspecialchars($_POST['marca']        ?? '');
        $_POST['modelo']        = htmlspecialchars($_POST['modelo']       ?? '');
        $_POST['color']         = htmlspecialchars($_POST['color']        ?? '');
        $_POST['tipo']          = htmlspecialchars($_POST['tipo']         ?? '');
        $_POST['observaciones'] = htmlspecialchars($_POST['observaciones'] ?? '');
        $_POST['km_actuales']   = (int) ($_POST['km_actuales'] ?? 0);

        // Validar campos obligatorios
        $requeridos = ['placa', 'numero_serie', 'marca', 'modelo', 'anio', 'color', 'tipo', 'estado', 'fecha_ingreso'];
        foreach ($requeridos as $campo) {
            if (empty($_POST[$campo])) {
                http_response_code(400);
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => "El campo '{$campo}' es obligatorio",
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
        }

        // Verificar duplicados
        if (Vehiculos::existePlaca($_POST['placa'])) {
            http_response_code(409);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Ya existe un vehículo con esa placa',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (Vehiculos::existeNumeroSerie($_POST['numero_serie'])) {
            http_response_code(409);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Ya existe un vehículo con ese número de serie',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $vehiculo  = new Vehiculos($_POST);
            $vehiculo->crear();

            http_response_code(200);
            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Vehículo registrado exitosamente',
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al registrar el vehículo',
                'detalle' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ----------------------------------------------------------------
    // API: Buscar / listar todos los vehículos
    // ----------------------------------------------------------------
    public static function buscarAPI()
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $vehiculos = Vehiculos::traerVehiculos();

            http_response_code(200);
            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Datos encontrados',
                'datos'   => $vehiculos,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al buscar vehículos',
                'detalle' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ----------------------------------------------------------------
    // API: Modificar vehículo
    // ----------------------------------------------------------------
    public static function modificarAPI()
    {
        header('Content-Type: application/json; charset=UTF-8');

        $placa = strtoupper(trim(htmlspecialchars($_POST['placa'] ?? '')));

        if (!$placa) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Placa no válida'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $_POST['marca']         = htmlspecialchars($_POST['marca']        ?? '');
        $_POST['modelo']        = htmlspecialchars($_POST['modelo']       ?? '');
        $_POST['color']         = htmlspecialchars($_POST['color']        ?? '');
        $_POST['tipo']          = htmlspecialchars($_POST['tipo']         ?? '');
        $_POST['observaciones'] = htmlspecialchars($_POST['observaciones'] ?? '');
        $_POST['km_actuales']   = (int) ($_POST['km_actuales'] ?? 0);

        // Verificar número de serie único (excluyendo la placa actual)
        if (!empty($_POST['numero_serie'])) {
            $_POST['numero_serie'] = strtoupper(trim(htmlspecialchars($_POST['numero_serie'])));
            if (Vehiculos::existeNumeroSerie($_POST['numero_serie'], $placa)) {
                http_response_code(409);
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => 'Ya existe otro vehículo con ese número de serie',
                ], JSON_UNESCAPED_UNICODE);
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

            $vehiculo->sincronizar($_POST);
            $vehiculo->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Vehículo modificado exitosamente',
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al modificar el vehículo',
                'detalle' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ----------------------------------------------------------------
    // API: Eliminar vehículo
    // ----------------------------------------------------------------
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

            $vehiculo->eliminar();

            http_response_code(200);
            echo json_encode([
                'codigo'  => 1,
                'mensaje' => 'Vehículo eliminado exitosamente',
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo'  => 0,
                'mensaje' => 'Error al eliminar el vehículo',
                'detalle' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
