<?php
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\ExpedienteController;
use Controllers\FichaController;
use Controllers\UnidadesController;
use Controllers\VehiculosController;
use Controllers\SegurosController;
use Controllers\AccidentesController;
use Controllers\ChequeoController;

$router = new Router();
$router->setBaseURL("");

$router->get('/', [AppController::class, 'index']);

// ── VEHÍCULOS ────────────────────────────────────────────────────────────────
$router->get('/vehiculos',                [VehiculosController::class, 'index']);
$router->get('/API/vehiculos/buscar',     [VehiculosController::class, 'buscarAPI']);
$router->post('/API/vehiculos/guardar',   [VehiculosController::class, 'guardarAPI']);
$router->post('/API/vehiculos/modificar', [VehiculosController::class, 'modificarAPI']);
$router->post('/API/vehiculos/eliminar',  [VehiculosController::class, 'eliminarAPI']);
$router->get('/API/vehiculos/foto',       [VehiculosController::class, 'servirFoto']);

// ── FICHA — SERVICIOS ────────────────────────────────────────────────────────
$router->get('/API/vehiculos/ficha',               [FichaController::class, 'fichaAPI']);
$router->get('/API/vehiculos/tipos-servicio',      [FichaController::class, 'tiposServicioAPI']);
$router->post('/API/vehiculos/servicio/guardar',   [FichaController::class, 'guardarServicioAPI']);
$router->post('/API/vehiculos/servicio/eliminar',  [FichaController::class, 'eliminarServicioAPI']);

// ── FICHA — REPARACIONES ─────────────────────────────────────────────────────
$router->get('/API/vehiculos/tipos-reparacion',        [FichaController::class, 'tiposReparacionAPI']);
$router->post('/API/vehiculos/reparacion/guardar',     [FichaController::class, 'guardarReparacionAPI']);
$router->post('/API/vehiculos/reparacion/modificar',   [FichaController::class, 'modificarReparacionAPI']);
$router->post('/API/vehiculos/reparacion/eliminar',    [FichaController::class, 'eliminarReparacionAPI']);

// ── SEGUROS ──────────────────────────────────────────────────────────────────
$router->get('/API/vehiculos/seguros/listar',      [SegurosController::class, 'listarAPI']);
$router->post('/API/vehiculos/seguros/guardar',    [SegurosController::class, 'guardarAPI']);
$router->post('/API/vehiculos/seguros/modificar',  [SegurosController::class, 'modificarAPI']);
$router->post('/API/vehiculos/seguros/cancelar',   [SegurosController::class, 'cancelarAPI']);
$router->post('/API/vehiculos/seguros/eliminar',   [SegurosController::class, 'eliminarAPI']);

// ── ACCIDENTES ───────────────────────────────────────────────────────────────
$router->get('/API/vehiculos/accidentes/listar',     [AccidentesController::class, 'listarAPI']);
$router->post('/API/vehiculos/accidentes/guardar',   [AccidentesController::class, 'guardarAPI']);
$router->post('/API/vehiculos/accidentes/modificar', [AccidentesController::class, 'modificarAPI']);
$router->post('/API/vehiculos/accidentes/eliminar',  [AccidentesController::class, 'eliminarAPI']);

// ── UNIDADES ─────────────────────────────────────────────────────────────────
$router->get('/API/unidades/destacamentos', [UnidadesController::class, 'destacamentosAPI']);
$router->get('/API/unidades/lista',         [UnidadesController::class, 'unidadesAPI']);

// ── CHEQUEOS ──────────────────────────────────────────────────────────────────
$router->get('/API/vehiculos/chequeos/listar',      [ChequeoController::class, 'listarAPI']);
$router->get('/API/vehiculos/chequeos/obtener',     [ChequeoController::class, 'obtenerAPI']);
$router->post('/API/vehiculos/chequeos/crear',      [ChequeoController::class, 'crearAPI']);
$router->post('/API/vehiculos/chequeos/completar',  [ChequeoController::class, 'completarAPI']);
$router->post('/API/vehiculos/chequeos/eliminar',   [ChequeoController::class, 'eliminarAPI']);

// ── EXPEDIENTE PDF ───────────────────────────────────────────────────────────
$router->get('/vehiculos/expediente', [ExpedienteController::class, 'generarPDF']);

$router->comprobarRutas();
