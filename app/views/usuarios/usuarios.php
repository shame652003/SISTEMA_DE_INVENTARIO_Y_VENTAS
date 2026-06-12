<?php use App\Core\UrlCipher; ?>
<div class="container-fluid p-4">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon bg-primary-soft">
                            <i class="bi bi-people text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Gestión de Usuarios</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-usuario" id="btn-nuevo-usuario">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo Usuario
                        </button>
                        <button class="btn btn-header-action btn-outline-orange" data-bs-toggle="modal" data-bs-target="#modal-rol" id="btn-nuevo-rol">
                            <i class="bi bi-shield-plus me-1"></i> Nuevo Rol
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
            <div class="fw-semibold text-secondary">
                <i class="bi bi-people me-2 text-primary"></i>Lista de Usuarios
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle w-100" id="tabla-usuarios">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small"></th>
                            <th class="text-muted small">Cédula</th>
                            <th class="text-muted small">Nombre</th>
                            <th class="text-muted small">Correo</th>
                            <th class="text-muted small">Teléfono</th>
                            <th class="text-muted small">Rol</th>
                            <th class="text-muted small">Estado</th>
                            <th class="text-muted small text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla de Roles -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
            <div class="fw-semibold text-secondary">
                <i class="bi bi-shield-lock me-2 text-warning"></i>Roles del Sistema
            </div>
            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-rol">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Rol
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle w-100" id="tabla-roles">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small">ID</th>
                            <th class="text-muted small">Nombre del Rol</th>
                            <th class="text-muted small">Estado</th>
                            <th class="text-muted small text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Usuario -->
<div class="modal fade" id="modal-usuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="titulo-modal-usuario">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-usuario" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="usuario-id">

                    <!-- Preview de Imagen -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img id="preview-imagen" src="" alt="Foto de perfil" class="rounded-circle border shadow-sm" style="width: 100px; height: 100px; object-fit: cover; display: none;">
                            <div id="avatar-default" class="profile-avatar" style="width: 100px; height: 100px; font-size: 2rem;">
                                <i class="bi bi-person"></i>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0;" onclick="document.getElementById('usuario-imagen').click()">
                                <i class="bi bi-camera"></i>
                            </button>
                        </div>
                        <input type="file" class="d-none" id="usuario-imagen" name="imagen" accept="image/*">
                        <div class="form-text mt-2">Haz clic en la cámara para subir una foto</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Cédula</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person-vcard text-muted"></i></span>
                                <input type="number" class="form-control border-start-0 ps-0" id="usuario-cedula" name="cedula" placeholder="Ej: 12345678" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Teléfono</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-telephone text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="usuario-telefono" name="telefono" placeholder="Ej: 0414-1234567" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Nombre</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="usuario-nombre" name="nombre" placeholder="Primer nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Segundo Nombre</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="usuario-segNombre" name="segNombre" placeholder="Segundo nombre (opcional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Apellido</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="usuario-apellido" name="apellido" placeholder="Primer apellido" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Segundo Apellido</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="usuario-segApellido" name="segApellido" placeholder="Segundo apellido (opcional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Correo</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="usuario-correo" name="correo" placeholder="usuario@ejemplo.com" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Rol</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-shield text-muted"></i></span>
                                <select class="form-select border-start-0 ps-0" id="usuario-rol" name="idRol" required>
                                    <option value="">Seleccione un rol</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Contraseña</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-lock text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0 bg-light" id="usuario-clave" value="123456" readonly>
                            </div>
                            <div class="form-text">Contraseña predeterminada. El usuario debe cambiarla al iniciar sesión.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Estado</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-toggle-on text-muted"></i></span>
                                <select class="form-select border-start-0 ps-0" id="usuario-status" name="status">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Rol -->
<div class="modal fade" id="modal-rol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="titulo-modal-rol">
                    <i class="bi bi-shield-plus me-2 text-warning"></i>Nuevo Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-rol">
                <div class="modal-body">
                    <input type="hidden" name="idRol" id="rol-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre del Rol</label>
                        <div class="input-group modern-input">
                            <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-shield text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="rol-nombre" name="nombreRol" placeholder="Ej: Administrador, Vendedor" required>
                        </div>
                        <div class="form-text">El nombre debe ser único en el sistema.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i> Guardar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
