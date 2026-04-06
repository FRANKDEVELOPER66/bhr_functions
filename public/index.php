<?php 
require_once __DIR__ . '/../includes/app.php';


use MVC\Router;
use Controllers\AppController;
use Controllers\VehiculosController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

$router->get('/', [AppController::class,'index']);
// VEHÍCULOS
$router->get('/vehiculos', [VehiculosController::class, 'index']);
$router->get('/API/vehiculos/buscar', [VehiculosController::class, 'buscarAPI']);
$router->post('/API/vehiculos/guardar', [VehiculosController::class, 'guardarAPI']);
$router->post('/API/vehiculos/modificar', [VehiculosController::class, 'modificarAPI']);
$router->post('/API/vehiculos/eliminar', [VehiculosController::class, 'eliminarAPI']);

$router->get('/API/vehiculos/foto', [VehiculosController::class, 'servirFoto']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();