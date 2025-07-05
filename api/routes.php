<?php
// Carga de dependencias vía Composer y configuración de BD
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use App\Config\Database;
use App\Controllers\UsuarioController;
use App\Controllers\EquipoController;
use App\Controllers\CampeonatoController;
use App\Controllers\ArenaDeportivaController;
use App\Controllers\ResultadoController;
use App\Controllers\InscripcionUsuarioController;
use App\Controllers\TablaCampeonatoController;
use App\Controllers\SportController;
use App\Controllers\FutbolController;
use App\Controllers\FixtureController;
use App\Controllers\InscripcionEquipoController;
use App\Controllers\AuthController; // ✅ Activado

// Conexión a la base de datos
$db = (new Database())->getConnection();

// -------------------------------------------------------------
// 👉 RUTAS con o sin index.php en la URL
// -------------------------------------------------------------
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);

// Detecta si la URL incluye "index.php"
$indexPos = array_search('index.php', $parts);
if ($indexPos !== false) {
    $resource = $parts[$indexPos + 1] ?? null;
    $id       = $parts[$indexPos + 2] ?? null;
} else {
    // Cuando se accede SIN index.php
    $resource = $parts[2] ?? null;  // api-DeportProyect/api/usuarios → posición 2
    $id       = $parts[3] ?? null;
}

// Verifica método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// -------------------------------------------------------------
// ✅ Rutas de autenticación
// -------------------------------------------------------------
if ($resource === 'login' && $method === 'POST') {
    (new AuthController($db))->login();
    exit;
}

if ($resource === 'me' && $method === 'GET') {
    (new AuthController($db))->me(); // esto es opcional
    exit;
}

if ($resource === 'logout' && $method === 'POST') {
    (new AuthController($db))->logout(); // esto también opcional
    exit;
}

// -------------------------------------------------------------
// Mapeo recurso → controlador
// -------------------------------------------------------------
$map = [
    'usuarios'              => UsuarioController::class,
    'equipos'               => EquipoController::class,
    'campeonatos'           => CampeonatoController::class,
    'arenas'                => ArenaDeportivaController::class,
    'resultados'            => ResultadoController::class,
    'inscripciones-usuario' => InscripcionUsuarioController::class,
    'tablas-campeonato'     => TablaCampeonatoController::class,
    'deportes'              => SportController::class,
    'futbol'                => FutbolController::class,
    'fixtures'              => FixtureController::class,
    'inscripciones-equipo'  => InscripcionEquipoController::class,
];

// -------------------------------------------------------------
// Validar si la ruta existe
// -------------------------------------------------------------
if (!isset($map[$resource])) {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
    exit;
}

// Instancia el controlador correspondiente
$ctrl = new $map[$resource]($db);

// -------------------------------------------------------------
// Enrutamiento RESTful
// -------------------------------------------------------------
switch ($method) {
    case 'GET':
        $id ? $ctrl->show((int)$id) : $ctrl->index();
        break;

    case 'POST':
        $ctrl->store();
        break;

    case 'PUT':
    case 'PATCH':
        if ($id !== null) $ctrl->update((int)$id);
        else {
            http_response_code(400);
            echo json_encode(['error'=>'ID requerido']);
        }
        break;

    case 'DELETE':
        if ($id !== null) $ctrl->delete((int)$id);
        else {
            http_response_code(400);
            echo json_encode(['error'=>'ID requerido']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error'=>'Método no permitido']);
        break;
}
