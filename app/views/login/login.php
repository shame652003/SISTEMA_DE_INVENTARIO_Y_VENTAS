<?php
use App\Core\UrlCipher;
$modoRecuperacion = $modoRecuperacion ?? false;
?>

<div class="auth-container">
    <div class="auth-card card">
        <div class="card-body">
            <!-- Brand / Logo -->
            <div class="text-center mb-4">
                <div class="auth-brand">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h2 class="auth-title">Sistema de Ventas e Inventarios</h2>
                <p class="auth-subtitle"><?= $modoRecuperacion ? 'Recuperar Contraseña' : 'Inicia sesión en tu cuenta' ?></p>
            </div>

            <?php if ($modoRecuperacion): ?>
            <!-- Formulario Recuperar Contraseña -->
            <form id="form-recuperar">
                <div class="mb-3">
                    <label for="recuperar-email" class="form-label fw-semibold text-secondary">Correo Electrónico</label>
                    <div class="input-group auth-input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="recuperar-email" name="email" placeholder="usuario@ejemplo.com" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-auth w-100 mt-2">
                    <i class="bi bi-envelope me-2"></i> Enviar Instrucciones
                </button>

                <div class="text-center mt-3">
                    <a href="<?= BASE_URL . UrlCipher::encrypt('/login') ?>" class="auth-link">
                        <i class="bi bi-arrow-left me-1"></i> Volver al inicio de sesión
                    </a>
                </div>
            </form>

            <?php else: ?>
            <!-- Formulario Inicio de Sesión -->
            <form id="form-login">
                <!-- Cédula -->
                <div class="mb-3">
                    <label for="cedula" class="form-label fw-semibold text-secondary">Cédula</label>
                    <div class="input-group auth-input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <input type="number" class="form-control" id="cedula" name="cedula" placeholder="Ej: 12345678" required autofocus>
                    </div>
                </div>

                <!-- Contraseña con toggle -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label fw-semibold text-secondary">Contraseña</label>
                        <a href="<?= BASE_URL . UrlCipher::encrypt('/recuperar') ?>" class="auth-link" style="font-size: 0.85rem;">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="input-group auth-input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="btn-toggle-password" id="btn-toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" id="icon-password"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-auth w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Ingresar
                </button>
            </form>

            <div class="auth-footer">
                <i class="bi bi-shield-check me-1"></i> Conexión segura y cifrada
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
