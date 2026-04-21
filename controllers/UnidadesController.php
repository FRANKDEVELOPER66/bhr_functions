<?php

namespace Controllers;

use Exception;
use Model\Unidades;
use Model\Destacamentos;
use MVC\Router;

class UnidadesController
{
    // Traer todos los destacamentos
    public static function destacamentosAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $datos = Destacamentos::traerTodos();
            echo json_encode(['codigo' => 1, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error'], JSON_UNESCAPED_UNICODE);
        }
    }

    // Traer unidades (todas o filtradas por destacamento)
    public static function unidadesAPI(Router $router)
    {
        isAuthApi();
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $idDestacamento = (int)($_GET['id_destacamento'] ?? 0);
            $datos = $idDestacamento
                ? Unidades::traerPorDestacamento($idDestacamento)
                : Unidades::traerTodos();
            echo json_encode(['codigo' => 1, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error'], JSON_UNESCAPED_UNICODE);
        }
    }
}
