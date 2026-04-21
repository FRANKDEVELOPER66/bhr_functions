<?php session_start();

use Dotenv\Dotenv;
use Model\ActiveRecord;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

ini_set('display_errors', $_ENV['DEBUG_MODE']);
ini_set('display_startup_errors', $_ENV['DEBUG_MODE']);
error_reporting(-$_ENV['DEBUG_MODE']);

require 'funciones.php';
require 'database.php';

// Conectarnos a la base de datos
ActiveRecord::setDB($db);

// ── Expirar sesión por inactividad ────────────────────────────────────────────
$tiempoLimite = 15 * 60; // 15 minutos
if (isset($_SESSION['auth_user'])) {
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $tiempoLimite) {
        session_destroy();
        $base = $_ENV['APP_NAME'] ? '/' . $_ENV['APP_NAME'] : '';
        header('Location: ' . $base . '/');
        exit;
    }
    $_SESSION['ultimo_acceso'] = time();
}
