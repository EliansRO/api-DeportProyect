<?php

// Carga de dependencias y configuración
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/auth.php';

use App\Config\Database;
use App\Controllers\{
    UsuarioController,
    EquipoController,
    AuthController,
    SolicitudAmistadController,
    AmistadController,
    InvitacionEquipoController,
    MiembrosEquipoController,
};

// Base de datos
$db = (new Database())->getConnection();

// 🔍 Parsear URI
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);
$indexPos = array_search('index.php', $parts);

$resource = $indexPos !== false ? $parts[$indexPos + 1] ?? null : $parts[2] ?? null;
$id       = $indexPos !== false ? $parts[$indexPos + 2] ?? null : $parts[3] ?? null;
$method   = $_SERVER['REQUEST_METHOD'];

// 🚪 Rutas públicas
$auth = new AuthController($db);
$publicRoutes = [
    'login'    => fn() => $auth->login(),
    'register' => fn() => $auth->register(),
    'me'       => fn() => $auth->me(),
];

if (array_key_exists($resource, $publicRoutes)) {
    $publicRoutes[$resource]();
    exit;
}

// 🔐 Verificación de autenticación con token
$user = getAuthUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado o token inválido']);
    exit;
}

// —> Nueva ruta para buscar por email vía POST
if ($resource === 'usuarios' && $method === 'POST' && isset($_SERVER['CONTENT_TYPE'])
    && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')
    && isset($parts[4]) && $parts[4] === 'email'
) {
    // Leer body
    $body = json_decode(file_get_contents('php://input'), true);
    (new UsuarioController($db))->showByEmail($body['email'] ?? '');
    exit;
}

// —> Nueva ruta para buscar equipos por nombre vía POST
if ($resource === 'equipos' && $method === 'POST' && isset($parts[4]) && $parts[4] === 'buscar') {
    (new EquipoController($db))->buscarPorNombre();
    exit;
}

// —> Nueva ruta para obtener los equipos del usuario autenticado
if ($resource === 'equipos' && $method === 'GET' && isset($parts[4]) && $parts[4] === 'mis-equipos') {
    (new EquipoController($db))->misEquipos();
    exit;
}


// 🚦 Mapeo RESTful
$map = [
    'usuarios'              => UsuarioController::class,
    'equipos'               => EquipoController::class,
    'solicitudes-amistad'   => SolicitudAmistadController::class,
    'amistades'             => AmistadController::class,
    'invitaciones-equipo'   => InvitacionEquipoController::class,
    'miembros-equipo'       => MiembrosEquipoController::class,
    // Rutas de autenticación
    'auth'                  => AuthController::class,
];

// Validación de recurso
if (!isset($map[$resource])) {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
    exit;
}

// Instanciar controlador
$ctrl = new $map[$resource]($db);

// Enrutamiento por método
switch ($method) {
    case 'GET':
        $id ? $ctrl->show((int)$id) : $ctrl->index();
        break;
    case 'POST':
        $ctrl->store();
        break;
    case 'PUT':
    case 'PATCH':
        $id ? $ctrl->update((int)$id) : http_response_code(400) && print(json_encode(['error' => 'ID requerido']));
        break;

    case 'DELETE':
        $id ? $ctrl->delete((int)$id) : http_response_code(400) && print(json_encode(['error' => 'ID requerido']));
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
