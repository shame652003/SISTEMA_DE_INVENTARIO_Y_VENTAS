<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$scriptDir = dirname($_SERVER['SCRIPT_NAME'], 2);
define('BASE_URL', ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir);

use App\Core\Router;

$router = new Router();

// ═══════════════════════════════════════════
// RUTAS PÚBLICAS (sin autenticación)
// ═══════════════════════════════════════════

// Autenticación JWT
$router->post('/auth/login',   'AuthController', 'login');
$router->post('/auth/refresh', 'AuthController', 'refresh');

// Vistas públicas
$router->get('/',         'LoginController', 'index');
$router->get('/login',    'LoginController', 'index');
$router->get('/recuperar','LoginController', 'recuperar');
$router->post('/recuperar', 'LoginController', 'enviarRecuperacion');

// ═══════════════════════════════════════════
// RUTAS PROTEGIDAS (requieren Bearer token)
// ═══════════════════════════════════════════

// Auth
$router->post('/auth/logout', 'AuthController', 'logout', 'auth');
$router->get('/auth/me',      'AuthController', 'me',     'auth');

// Dashboard
$router->get('/dashboard', 'DashboardController', 'index', 'auth');

// Usuarios
$router->get('/usuarios',                'UsuarioController', 'index',        'auth');
$router->post('/usuarios/listar',   'UsuarioController', 'listar',  'auth');
$router->post('/usuarios/obtener',  'UsuarioController', 'obtener', 'auth');
$router->post('/usuarios/guardar',      'UsuarioController', 'guardar',      'auth');
$router->post('/usuarios/eliminar',     'UsuarioController', 'eliminar',     'auth');
$router->post('/usuarios/roles/listar',  'UsuarioController', 'listarRoles',  'auth');
$router->post('/usuarios/roles/guardar', 'UsuarioController', 'guardarRol',   'auth');
$router->post('/usuarios/roles/eliminar','UsuarioController', 'eliminarRol',  'auth');

// Perfil
$router->get('/perfil',                  'PerfilController', 'index',        'auth');
$router->post('/perfil/obtener',        'PerfilController', 'obtener',       'auth');
$router->post('/perfil/actualizar',     'PerfilController', 'actualizar',    'auth');
$router->post('/perfil/cambiar-clave', 'PerfilController', 'cambiarClave',  'auth');

// Bitácora
$router->get('/bitacora',          'BitacoraController', 'index',  'auth');
$router->post('/bitacora/listar',  'BitacoraController', 'listar', 'auth');

// Ayuda
$router->get('/ayuda', 'AyudaController', 'index', 'auth');

// Clientes
$router->get('/clientes',           'ClienteController', 'index',   'auth');
$router->post('/clientes/listar',   'ClienteController', 'listar',  'auth');
$router->post('/clientes/guardar',  'ClienteController', 'guardar', 'auth');
$router->post('/clientes/eliminar', 'ClienteController', 'eliminar','auth');

// Productos
$router->get('/productos',           'ProductoController', 'index',   'auth');
$router->post('/productos/listar',   'ProductoController', 'listar',  'auth');
$router->post('/productos/guardar',  'ProductoController', 'guardar', 'auth');
$router->post('/productos/eliminar', 'ProductoController', 'eliminar','auth');

// Entradas
$router->get('/entradas',          'EntradaController', 'index',  'auth');
$router->post('/entradas/listar',  'EntradaController', 'listar', 'auth');
$router->post('/entradas/guardar', 'EntradaController', 'guardar','auth');

// Salidas
$router->get('/salidas',          'SalidaController', 'index',  'auth');
$router->post('/salidas/listar',  'SalidaController', 'listar', 'auth');
$router->post('/salidas/guardar', 'SalidaController', 'guardar','auth');

// Ventas
$router->get('/ventas',          'VentaController', 'index',  'auth');
$router->post('/ventas/listar',  'VentaController', 'listar', 'auth');
$router->post('/ventas/guardar', 'VentaController', 'guardar','auth');

// Reportes
$router->get('/reportes/pagos',            'ReportePagoController', 'index',   'auth');
$router->post('/reportes/pagos/generar',   'ReportePagoController', 'generar', 'auth');
$router->get('/reportes/generales',         'ReporteGeneralController', 'index',   'auth');
$router->post('/reportes/generales/generar','ReporteGeneralController', 'generar', 'auth');

$router->dispatch();
