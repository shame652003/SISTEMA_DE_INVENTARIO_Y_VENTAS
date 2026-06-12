<?php use App\Core\UrlCipher; ?>
<div class="container-fluid p-4">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon bg-primary-soft">
                            <i class="bi bi-person-circle text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Mi Perfil</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Perfil</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card de Información del Perfil -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <!-- Avatar -->
                    <div class="position-relative d-inline-block mb-3">
                        <img id="perfil-preview-img" src="" alt="Foto de perfil" class="rounded-circle border shadow-sm" style="width: 140px; height: 140px; object-fit: cover; display: none;">
                        <div id="perfil-avatar-default" class="profile-avatar" style="width: 140px; height: 140px; font-size: 3rem;">
                            <i class="bi bi-person"></i>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 40px; height: 40px; padding: 0;" onclick="document.getElementById('perfil-imagen').click()">
                            <i class="bi bi-camera fs-5"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0; display: none;" id="btn-eliminar-foto" title="Eliminar foto">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </div>
                    <h5 class="fw-bold mb-1" id="perfil-nombre-completo">Cargando...</h5>
                    <p class="text-muted mb-2" id="perfil-rol">Cargando...</p>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">Activo</span>

                    <hr class="my-4">

                    <div class="text-start">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon bg-primary-soft" style="width: 40px; height: 40px; font-size: 1rem;">
                                <i class="bi bi-envelope text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Correo</div>
                                <div class="fw-semibold" id="perfil-info-correo">-</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon bg-success-soft" style="width: 40px; height: 40px; font-size: 1rem;">
                                <i class="bi bi-telephone text-success"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Teléfono</div>
                                <div class="fw-semibold" id="perfil-info-telefono">-</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-warning-soft" style="width: 40px; height: 40px; font-size: 1rem;">
                                <i class="bi bi-person-vcard text-warning"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Cédula</div>
                                <div class="fw-semibold" id="perfil-info-cedula">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Edición -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="fw-semibold text-secondary">
                        <i class="bi bi-pencil me-2 text-primary"></i>Editar Información
                    </div>
                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-cambiar-clave">
                        <i class="bi bi-shield-lock me-1"></i> Cambiar Contraseña
                    </button>
                </div>
                <div class="card-body">
                    <form id="form-perfil" enctype="multipart/form-data">
                        <input type="file" class="d-none" id="perfil-imagen" name="imagen" accept="image/*">
                        <input type="hidden" id="perfil-eliminar-img" name="eliminar_img" value="0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Nombre</label>
                                <div class="input-group modern-input">
                                    <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="perfil-nombre" name="nombre" placeholder="Primer nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Segundo Nombre</label>
                                <div class="input-group modern-input">
                                    <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="perfil-segNombre" name="segNombre" placeholder="Segundo nombre (opcional)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Apellido</label>
                                <div class="input-group modern-input">
                                    <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="perfil-apellido" name="apellido" placeholder="Primer apellido" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Segundo Apellido</label>
                                <div class="input-group modern-input">
                                    <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="perfil-segApellido" name="segApellido" placeholder="Segundo apellido (opcional)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Correo</label>
                                <div class="input-group modern-input">
                                    <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="perfil-correo" name="correo" placeholder="usuario@ejemplo.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">Teléfono</label>
                                <div class="input-group modern-input">
                                    <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-telephone text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="perfil-telefono" name="telefono" placeholder="Ej: 0414-1234567" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cambiar Contraseña -->
<div class="modal fade" id="modal-cambiar-clave" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-shield-lock me-2 text-warning"></i>Cambiar Contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-cambiar-clave">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Contraseña Actual</label>
                        <div class="input-group modern-input">
                            <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="clave-actual" name="clave_actual" placeholder="Ingresa tu contraseña actual" required>
                            <button type="button" class="btn btn-outline-secondary border-start-0 bg-transparent btn-toggle-clave-modal" data-target="clave-actual" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nueva Contraseña</label>
                        <div class="input-group modern-input">
                            <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="clave-nueva" name="clave_nueva" placeholder="Ingresa la nueva contraseña" required>
                            <button type="button" class="btn btn-outline-secondary border-start-0 bg-transparent btn-toggle-clave-modal" data-target="clave-nueva" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Mínimo 6 caracteres.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Confirmar Nueva Contraseña</label>
                        <div class="input-group modern-input">
                            <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-key-fill text-muted"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="clave-confirmar" name="clave_confirmar" placeholder="Confirma la nueva contraseña" required>
                            <button type="button" class="btn btn-outline-secondary border-start-0 bg-transparent btn-toggle-clave-modal" data-target="clave-confirmar" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i> Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
