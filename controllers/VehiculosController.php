<?php

namespace Controllers;

use Exception;
use Model\Vehiculos;
use MVC\Router;

class VehiculosController
{
    public static function index(Router $router)
    {
        $router->render('vehiculos/index', [
            'titulo' => 'Gestión de Vehículos'
        ]); 
    }
}