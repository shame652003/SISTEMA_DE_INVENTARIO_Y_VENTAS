# Sistema de Ventas e Inventarios

Sistema web de gestión empresarial con control de inventario, ventas, clientes, pagos y reportes. Construido con PHP puro bajo el patrón MVC, autenticación JWT con cookies httpOnly y URLs encriptadas.

---

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2 (Vanilla, patrón MVC + PSR-4) |
| Autoload | Composer (`App\` → `app/`) |
| Frontend | jQuery 3.7 + AJAX + Bootstrap 5.3 |
| Notificaciones | SweetAlert2 |
| Autenticación | JWT (firebase/php-jwt) + cookies httpOnly |
| Seguridad URLs | AES-256-CBC (openssl) |
| Base de Datos | MySQL 8 / MariaDB (InnoDB) |
| Servidor | Apache 2.4 (XAMPP) |

---

## Estructura de Directorios

```
├── public/                     ← DocumentRoot (único punto de entrada)
│   ├── index.php               ← Front Controller + 40 rutas
│   └── .htaccess               ← Rewrite a index.php
│
├── app/
│   ├── config/
│   │   ├── database.php        ← Credenciales MySQL
│   │   └── jwt.php             ← Clave secreta + TTL tokens
│   │
│   ├── core/
│   │   ├── Router.php          ← Enrutador (regex + middleware + decrypt URLs)
│   │   ├── Controller.php      ← Clase base (render vistas, JSON, redirect)
│   │   ├── Model.php           ← Clase base (PDO singleton, queries genéricos)
│   │   ├── View.php            ← Renderizador standalone
│   │   ├── JwtHandler.php      ← Generar/validar access + refresh tokens
│   │   ├── AuthMiddleware.php  ← Verificar JWT (cookie httpOnly > header)
│   │   └── UrlCipher.php       ← Encrypt/decrypt rutas (AES-256-CBC)
│   │
│   ├── controllers/            ← 13 controladores
│   │   ├── AuthController.php      ← Login / Refresh / Logout / Me (JWT)
│   │   ├── LoginController.php     ← Vista login + recuperar
│   │   ├── DashboardController.php ← Panel principal
│   │   ├── UsuarioController.php   ← CRUD usuarios
│   │   ├── PerfilController.php    ← Editar perfil propio
│   │   ├── BitacoraController.php  ← Auditoría
│   │   ├── AyudaController.php     ← Centro de ayuda
│   │   ├── ClienteController.php   ← CRUD clientes
│   │   ├── ProductoController.php  ← CRUD productos
│   │   ├── EntradaController.php   ← Entradas de inventario
│   │   ├── SalidaController.php    ← Salidas de inventario
│   │   ├── VentaController.php     ← Ventas
│   │   ├── ReportePagoController.php    ← Reportes de pagos
│   │   └── ReporteGeneralController.php ← Reportes generales
│   │
│   ├── models/                 ← 8 modelos (1 por entidad)
│   │   ├── Usuario.php, Cliente.php, Producto.php,
│   │   ├── Entrada.php, Salida.php, Venta.php,
│   │   ├── Bitacora.php, Reporte.php, RefreshToken.php
│   │
│   └── views/
│       ├── layouts/
│       │   ├── header.php      ← Bootstrap 5 + SweetAlert2 + CSS
│       │   ├── sidebar.php     ← Menú navegación (URLs encriptadas)
│       │   └── footer.php      ← jQuery + global_ajax + validations + AUTH
│       └── [13 módulos]/       ← 1 vista por módulo (CRUD con modales)
│
├── assets/
│   ├── css/ (global.css + login.css)
│   ├── js/
│   │   ├── global_ajax.js      ← Interceptor AJAX (Bearer cookie automático)
│   │   ├── validations.js      ← Validaciones reutilizables
│   │   └── [11 módulos].js     ← Lógica CRUD por módulo
│   └── img/
│
├── bases-de-datos/
│   ├── inventario-sistema.sql  ← Schema completo (18 tablas, 24 triggers)
│   └── refresh_tokens.sql      ← Tabla para refresh tokens JWT
│
├── .htaccess                   ← Redirige peticiones a public/
├── composer.json               ← PSR-4 + dependencia firebase/php-jwt
└── vendor/                     ← Autoload generado
```

---

## Flujo de Trabajo (Arquitectura)

### 1. Petición HTTP entrante

```
Navegador → http://localhost/Sistema_de_Ventas_y_inventarios/r/Xk9zM2...
                                                          ↓
RAÍZ .htaccess  →  !-f (no es archivo real)  →  Rewrite a public/r/Xk9zM2...
                                                          ↓
PUBLIC .htaccess →  !-f !-d  →  Rewrite a index.php
                                                          ↓
public/index.php →  Front Controller
  ├─ define('BASE_URL', '/Sistema_de_Ventas_y_inventarios')
  ├─ $router->get('/dashboard', 'DashboardController', 'index', 'auth')
  └─ $router->dispatch()
```

### 2. Router::dispatch()

```
1. Extrae URI de REQUEST_URI
2. Recorta BASE_URL del principio
3. Recorta /public si está presente
4. rtrim '/'  →  '/r/Xk9zM2pQwN7v'
5. Detecta prefijo /r/  →  UrlCipher::decrypt()  →  '/dashboard'
6. Itera rutas registradas con preg_match
7. Si coincide: ejecuta middleware 'auth' → ejecuta acción del controlador
8. Si no coincide: 404
```

### 3. Middleware de Autenticación (AuthMiddleware)

```
AuthMiddleware::verificar()
  ├─ 1. Lee cookie httpOnly 'jwt_token' (navegador la envía automático)
  ├─ 2. Fallback: header Authorization Bearer
  ├─ 3. Valida JWT con firebase/php-jwt
  ├─ 4. Si expirado y es AJAX → JSON 401 (interceptor JS refresca)
  ├─ 5. Si expirado y es página → redirect a /login?expired=1
  └─ 6. Si válido → guarda datos en $_REQUEST['auth']
```

### 4. Flujo de Login

```
┌─────────────────────────────────────────────────┐
│ USUARIO: ingresa cédula + contraseña            │
│ FORM: JS previene submit → AJAX POST            │
│       a /auth/login con datos del form          │
└──────────────────┬──────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────┐
│ AuthController::login()                         │
│  1. Valida cédula + password_verify()           │
│  2. Genera access_token (JWT, 15 min)           │
│  3. Genera refresh_token (JWT, 7 días)          │
│  4. Guarda hash SHA256 del refresh en BD        │
│  5. setcookie('jwt_token', access, httpOnly)    │
│  6. JSON: { refresh_token, usuario }            │
└──────────────────┬──────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────┐
│ login.js recibe respuesta                       │
│  1. guarda refresh_token en localStorage        │
│  2. guarda datos usuario en localStorage        │
│  3. window.location → /r/... (dashboard encr.)  │
└──────────────────┬──────────────────────────────┘
                   ▼
┌─────────────────────────────────────────────────┐
│ Dashboard carga                                 │
│  1. Router decrypt /r/... → /dashboard          │
│  2. AuthMiddleware lee cookie → token válido ✓  │
│  3. DashboardController → Reporte::resumen()    │
│  4. Vista con cards de productos, ventas, etc.  │
│  5. Footer: /auth/me verifica token silencioso  │
└─────────────────────────────────────────────────┘
```

### 5. Renovación Silenciosa de Token (Refresh)

```
Llamada AJAX a endpoint protegido
  → AuthMiddleware detecta token expirado
  → 401 JSON: { ok: false, code: "token_expirado" }

global_ajax.js interceptor (ajaxError)
  → Detecta 401
  → Toma refresh_token de localStorage
  → POST /auth/refresh
      → AuthController::refresh()
          → Valida refresh JWT
          → Verifica hash en BD (refresh_tokens)
          → ROTACIÓN: elimina hash viejo, genera nuevos tokens
          → setcookie con nuevo access_token
          → JSON: { refresh_token: nuevo }
  → Actualiza refresh_token en localStorage
  → Reintenta la petición original
```

### 6. Encriptación de URLs

```
UrlCipher usa AES-256-CBC con clave derivada de JWT secret
  clave = hash('sha256', JWT_SECRET, binary=true)
  IV    = fixed (derivado, determinístico)

encrypt('/dashboard') → '/r/T0FuRytFU0dsUCtVUC9TVHdjR2lJdz09'
decrypt('/r/T0FuRytFU0dsUCtVUC9TVHdjR2lJdz09') → '/dashboard'

Dónde se aplica:
  ✅ Sidebar links       (barra de navegación)
  ✅ AuthMiddleware redir (redirect PHP)
  ✅ Login page links    (recuperar/volver)
  ❌ AJAX calls          (van en texto plano, invisibles al usuario)
  ❌ Assets CSS/JS       (archivos físicos)
```

### 7. Layouts (renderizado de páginas)

```
Controller::render($view, $data, $layout)

$layout === 'auth' (login/recuperar):
  header.php → Bootstrap 5 + SweetAlert2 + CSS
  login.php  → formulario
  footer.php → jQuery + AUTH.init + global_ajax + validations + extraJS

$layout === 'app' (dashboard y módulos):
  header.php → Bootstrap 5 + SweetAlert2 + CSS
  sidebar.php → menú lateral con URLs encriptadas
  <main> vista.php </main>
  footer.php → jQuery + AUTH.check + global_ajax + validations + extraJS

Variable $esPublico controla si el footer ejecuta verificación de token:
  'auth' → $esPublico = true  → no verifica
  'app'  → $esPublico = false → verifica con /auth/me
```

### 8. Base de Datos (18 tablas)

```
Módulo Seguridad:
  rol, usuario (PK: cédula), bitacora, refresh_tokens

Módulo Configuración:
  bcv_tasas, margen_ganancia

Módulo Inventario:
  tipo_productos, producto, producto_precio_historial,
  entradaproducto, detalleEntradaA,
  tipoSalidas, salidas_de_productos

Módulo Clientes:
  equipos_cliente, cliente (PK: cédula)

Módulo Ventas:
  ventas_encabezado, ventas_detalle,
  tipo_de_pagos, pagos, creditos

Vistas predefinidas:
  vw_stock_disponible, vw_stock_bajo, vw_stock_agotado,
  vw_ventas_diarias/semanales/mensuales,
  vw_ventas_por_producto/tipo/cliente/usuario,
  vw_pagos_por_tipo/moneda/diarios,
  vw_movimientos_inventario, vw_creditos_pendientes,
  vw_resumen_dashboard, vw_productos_mas_vendidos

Triggers:
  24 triggers para bitácora automática, cálculo de precios,
  ajuste de stock, totales de ventas.
```

---

## Instalación

### Requisitos

- XAMPP (PHP 8.0+, MySQL, Apache)
- Composer
- Git (opcional)

### Paso 1: Clonar o copiar proyecto

Colocar en `C:\xampp\htdocs\Sistema_de_Ventas_y_inventarios\`

### Paso 2: Instalar dependencias

```bash
cd C:\xampp\htdocs\Sistema_de_Ventas_y_inventarios
composer install
```

### Paso 3: Base de datos

1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Importar `bases-de-datos/inventario-sistema.sql`
3. Importar `bases-de-datos/refresh_tokens.sql`
4. Insertar usuario administrador de prueba:

```sql
USE sistemainventario;
INSERT INTO usuario (cedula, nombre, apellido, correo, telefono, clave, idRol, status)
VALUES (12345678, 'Admin', 'Sistema', 'admin@sistema.com', '04120000000',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);
```

Clave: `password`

### Paso 4: Configurar

- `app/config/database.php` — credenciales MySQL
- `app/config/jwt.php` — clave secreta JWT (cambiar en producción)

### Paso 5: Acceder

```
http://localhost/Sistema_de_Ventas_y_inventarios/
```

Cédula: `12345678` | Clave: `password`

---

## Rutas del Sistema

### Públicas (sin token)

| Método | Ruta | Acción |
|---|---|---|
| GET | `/` | Vista login |
| GET | `/login` | Vista login |
| GET | `/recuperar` | Vista recuperar contraseña |
| POST | `/auth/login` | Autenticar (devuelve refresh_token) |
| POST | `/auth/refresh` | Renovar access token |
| POST | `/recuperar` | Enviar correo recuperación |

### Protegidas (requieren cookie httpOnly)

| Módulo | GET | POST |
|---|---|---|
| Auth | `/auth/me`, `/auth/logout` | — |
| Dashboard | `/dashboard` | — |
| Usuarios | `/usuarios` | `/usuarios/listar`, `/guardar`, `/eliminar` |
| Perfil | `/perfil` | `/perfil/actualizar` |
| Bitácora | `/bitacora` | `/bitacora/listar` |
| Ayuda | `/ayuda` | — |
| Clientes | `/clientes` | `/clientes/listar`, `/guardar`, `/eliminar` |
| Productos | `/productos` | `/productos/listar`, `/guardar`, `/eliminar` |
| Entradas | `/entradas` | `/entradas/listar`, `/guardar` |
| Salidas | `/salidas` | `/salidas/listar`, `/guardar` |
| Ventas | `/ventas` | `/ventas/listar`, `/guardar` |
| Reportes | `/reportes/pagos`, `/reportes/generales` | `/pagos/generar`, `/generales/generar` |

---

## Seguridad

| Mecanismo | Detalle |
|---|---|---|
| **JWT Access Token** | Cookie httpOnly + SameSite Lax (inmune a XSS) |
| **JWT Refresh Token** | localStorage + hash SHA256 en BD + rotación automática + leeway 60s |
| **URL Encryption** | AES-256-CBC, IV aleatorio, rutas visibles solo como `/r/...` |
| **Passwords** | `password_hash()` BCRYPT |
| **CSRF** | SameSite Lax en cookie |
| **SQL Injection** | PDO prepared statements |
| **Bitácora** | 24 triggers registran toda acción automáticamente |

---

## Módulos del Sistema

| Módulo | Descripción | CRUD |
|---|---|---|
| Dashboard | Panel con resumen de productos, ventas, créditos | Vista |
| Usuarios | Gestión de empleados con roles | Completo (modal) |
| Perfil | Editar datos del usuario logueado | Formulario |
| Bitácora | Registro de auditoría automático | Solo lectura |
| Ayuda | Documentación de uso del sistema | Vista |
| Clientes | Registro de clientes + equipos | Completo (modal) |
| Productos | Catálogo con stock, precios USD/VES | Completo (modal) |
| Entradas | Ingreso de mercancía al inventario | Registro |
| Salidas | Egreso de mercancía (ventas/pérdidas) | Registro |
| Ventas | Registro de ventas con detalle múltiple | Registro |
| Reportes Pagos | Pagos por período, tipo y moneda | Consulta |
| Reportes Generales | Ventas, inventario, productos más vendidos | Consulta |

---

## Changelog

### v2.1 (2026-06-12)
- **Fix:** Bucle infinito del refresh token corregido.
  - `AuthMiddleware.php`: Ahora redirige con `?expired=1` también cuando la cookie está ausente (no solo cuando expiró).
  - `login.js`: Antes de redirigir al dashboard, se refresca la cookie primero. Si falla, limpia tokens y se queda en login.
- **Fix:** Revocación agresiva de tokens removida.
  - `AuthController.php`: Ya no borra todos los tokens del usuario al detectar uno inválido (multi-tab seguro).
- **Fix:** `SameSite=Strict` cambiado a `Lax` para compatibilidad con redirecciones.
- **Fix:** `JWT::$leeway = 60s` agregado para tolerancia de desfase de reloj.
- **Fix:** `UrlCipher` ahora usa IV aleatorio con retrocompatibilidad.
- **Fix:** `global_ajax.js` previene bucle infinito en reintentos con flag `refreshFailed`.
- **Fix:** Respuesta de retry ya no se descarta (dispara evento `ajax-retry-success`).
- **Fix:** `catch (\Throwable)` en lugar de `\Exception` para capturar errores graves.
- **Fix:** Borrado de cookie JS ahora incluye `SameSite=Lax` para coincidir con servidor.
- **Fix:** Redirecciones usan `window.location.replace()` para no acumular historial.
