<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Models/Servicio.php';
require_once __DIR__ . '/../src/Models/Solicitud.php';
require_once __DIR__ . '/../src/Models/EstadoSolicitud.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Controllers/EmpresaController.php';
require_once __DIR__ . '/../src/Blockchain/MultiversxClient.php';

session_start();

$router = new Router();

// Rutas públicas
$router->get('/', fn() => view('login.php'));
$router->get('/login', fn() => view('login.php')); // <--- ADD THIS LINE

$router->post('/login', [Auth::class, 'login']);

$router->get('/register', fn() => view('register.php')); // <--- ADD THIS LINE
$router->post('/register', [Auth::class, 'register']);

$router->post('/logout', function() {
    session_destroy();
    header('Location: /');
    exit;
});

// Rutas admin
$router->get('/admin', [AdminController::class, 'dashboard'], 'admin');
$router->get('/admin/servicios', [AdminController::class, 'listarServicios'], 'admin');
$router->post('/admin/servicios', [AdminController::class, 'guardarServicio'], 'admin');
$router->post('/admin/servicios/{id}/toggle', [AdminController::class, 'toggleServicio'], 'admin');
$router->post('/admin/solicitudes/{id}/aceptar', [AdminController::class, 'aceptar'], 'admin');
$router->post('/admin/solicitudes/{id}/rechazar', [AdminController::class, 'rechazar'], 'admin');
$router->post('/admin/solicitudes/{id}/realizado', [AdminController::class, 'marcarRealizado'], 'admin');
$router->get('/admin/trazabilidad', [AdminController::class, 'trazabilidad'], 'admin');

// Rutas empresa
$router->get('/empresa/servicios', [EmpresaController::class, 'catalogo'], 'empresa');
$router->post('/empresa/solicitudes', [EmpresaController::class, 'solicitar'], 'empresa');
$router->get('/empresa/solicitudes', [EmpresaController::class, 'misSolicitudes'], 'empresa');
$router->post('/empresa/solicitudes/{id}/validar', [EmpresaController::class, 'validar'], 'empresa');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);