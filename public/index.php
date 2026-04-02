<?php 
require_once __DIR__ . '/../includes/app.php';


use MVC\Router;
use Controllers\AppController;
use Controllers\VehiculosController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

$router->get('/', [AppController::class,'index']);

//vehiculos
$router->get('/vehiculos', [VehiculosController::class, 'index']);
//$router->get('/API/cursos/buscar', [CursosController::class, 'buscarAPI']);
//$router->post('/API/cursos/guardar', [CursosController::class, 'guardarAPI']);
//$router->post('/API/cursos/modificar', [CursosController::class, 'modificarAPI']);
//$router->post('/API/cursos/eliminar', [CursosController::class, 'eliminarAPI']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();