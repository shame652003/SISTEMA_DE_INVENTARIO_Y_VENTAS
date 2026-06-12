<?php
use App\Core\UrlCipher;
use App\Core\AuthMiddleware;

$url = fn($path) => BASE_URL . UrlCipher::encrypt($path);

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
<aside class="sidebar d-flex flex-column">
    <div class="sidebar-brand">
        <i class="bi bi-box-seam fs-3"></i>
        <h5 class="mb-0">Inventario App</h5>
        <small>Sistema de Gestión</small>
    </div>

    <nav class="sidebar-nav flex-grow-1">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="<?= $url('/dashboard') ?>" class="nav-link <?= ($seccion ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="sidebar-section-title">Administración</li>
            <li class="nav-item">
                <a href="<?= $url('/usuarios') ?>" class="nav-link <?= ($seccion ?? '') === 'usuarios' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/perfil') ?>" class="nav-link <?= ($seccion ?? '') === 'perfil' ? 'active' : '' ?>">
                    <i class="bi bi-person-circle"></i> Perfil
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/bitacora') ?>" class="nav-link <?= ($seccion ?? '') === 'bitacora' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i> Bitácora
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/ayuda') ?>" class="nav-link <?= ($seccion ?? '') === 'ayuda' ? 'active' : '' ?>">
                    <i class="bi bi-question-circle"></i> Ayuda
                </a>
            </li>

            <li class="sidebar-section-title">Operaciones</li>
            <li class="nav-item">
                <a href="<?= $url('/clientes') ?>" class="nav-link <?= ($seccion ?? '') === 'clientes' ? 'active' : '' ?>">
                    <i class="bi bi-person-lines-fill"></i> Clientes
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/productos') ?>" class="nav-link <?= ($seccion ?? '') === 'productos' ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/entradas') ?>" class="nav-link <?= ($seccion ?? '') === 'entradas' ? 'active' : '' ?>">
                    <i class="bi bi-box-arrow-in-down"></i> Entradas
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/salidas') ?>" class="nav-link <?= ($seccion ?? '') === 'salidas' ? 'active' : '' ?>">
                    <i class="bi bi-box-arrow-up"></i> Salidas
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/ventas') ?>" class="nav-link <?= ($seccion ?? '') === 'ventas' ? 'active' : '' ?>">
                    <i class="bi bi-cart-check"></i> Ventas
                </a>
            </li>

            <li class="sidebar-section-title">Reportes</li>
            <li class="nav-item">
                <a href="<?= $url('/reportes/pagos') ?>" class="nav-link <?= ($seccion ?? '') === 'reportes_pagos' ? 'active' : '' ?>">
                    <i class="bi bi-cash-stack"></i> Reportes de Pagos
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= $url('/reportes/generales') ?>" class="nav-link <?= ($seccion ?? '') === 'reportes_generales' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Reportes Generales
                </a>
            </li>
        </ul>
    </nav>

    <?php if ($tieneSesion): ?>
    <div class="sidebar-footer">
        <a href="<?= $url('/perfil') ?>" class="d-flex align-items-center gap-2 text-decoration-none text-white mb-2">
            <img id="sidebar-avatar-img" src="<?= htmlspecialchars($usuario['img'] ?? '') ?>" alt="" class="profile-avatar-sm" style="object-fit: cover; <?= !empty($usuario['img']) ? '' : 'display: none;' ?>">
            <div id="sidebar-avatar-text" class="profile-avatar-sm" style="<?= !empty($usuario['img']) ? 'display: none;' : '' ?>">
                <?= $iniciales ?>
            </div>
            <div class="flex-grow-1" style="min-width: 0;">
                <div id="sidebar-nombre" class="fw-semibold text-truncate" style="font-size: 0.85rem; color: #fff;">
                    <?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?>
                </div>
                <div id="sidebar-rol" class="text-truncate" style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">
                    <?= htmlspecialchars($usuario['nombreRol'] ?? 'Rol') ?>
                </div>
            </div>
        </a>
        <button class="btn btn-outline-light btn-sm w-100" onclick="window.AUTH.logout()">
            <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
        </button>
    </div>
    <?php endif; ?>
</aside>
