<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Sistema de Ventas e Inventarios') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/png" href="https://img.icons8.com/?size=100&id=104073&format=png&color=000000">
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">

    <?= $extraCSS ?? '' ?>
</head>
<body>
<?php
use App\Core\AuthMiddleware;
use App\Core\UrlCipher;

$usuario = AuthMiddleware::usuario();
$tieneSesion = !empty($usuario);
$iniciales = '';
if ($tieneSesion) {
    $nombre = $usuario['nombre'] ?? '';
    $partes = explode(' ', trim($nombre));
    $iniciales = strtoupper(
        (substr($partes[0] ?? '', 0, 1)) .
        (substr($partes[1] ?? '', 0, 1))
    );
}
?>
<?php if ($tieneSesion): ?>
<header class="app-header">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebar-toggle" type="button" aria-label="Abrir menú">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="fw-semibold text-white d-none d-md-block" id="page-title">
            <?= htmlspecialchars($titulo ?? 'Dashboard') ?>
        </span>
    </div>

    <div class="ms-auto d-flex align-items-center gap-3">
        <!-- Dropdown de perfil -->
        <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2 p-1 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img id="header-avatar-img" src="<?= htmlspecialchars($usuario['img'] ?? '') ?>" alt="" class="profile-avatar" style="object-fit: cover; <?= !empty($usuario['img']) ? '' : 'display: none;' ?>">
                <div id="header-avatar-text" class="profile-avatar" style="<?= !empty($usuario['img']) ? 'display: none;' : '' ?>">
                    <?= $iniciales ?>
                </div>
                <div class="text-start d-none d-lg-block">
                    <div id="header-nombre" class="fw-semibold text-white" style="font-size: 0.9rem; line-height: 1.2;">
                        <?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?>
                    </div>
                    <div id="header-rol" class="text-white-50" style="font-size: 0.75rem; line-height: 1.2;">
                        <?= htmlspecialchars($usuario['nombreRol'] ?? 'Rol') ?>
                    </div>
                </div>
                <i class="bi bi-chevron-down text-white-50 small d-none d-lg-block"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="min-width: 200px;">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL . UrlCipher::encrypt('/perfil') ?>">
                        <i class="bi bi-person-circle text-white"></i> Mi Perfil
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item d-flex align-items-center gap-2 text-white" onclick="window.AUTH.logout()">
                        <i class="bi bi-box-arrow-right text-danger"></i> Cerrar Sesión
                    </button>
                </li>
            </ul>
        </div>
    </div>
</header>
<?php endif; ?>
