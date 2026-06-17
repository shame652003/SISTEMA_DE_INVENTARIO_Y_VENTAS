<?php use App\Core\UrlCipher; ?>

<div class="container-fluid p-4">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon bg-primary-soft">
                            <i class="bi bi-person-lines-fill text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Gestión de Clientes</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Clientes</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-cliente" id="btn-nuevo">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo Cliente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
            <div class="fw-semibold text-secondary">
                <i class="bi bi-person-lines-fill me-2 text-primary"></i>Lista de Clientes
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle w-100" id="tabla-clientes">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small">Cédula</th>
                            <th class="text-muted small">Nombre</th>
                            <th class="text-muted small">Teléfono</th>
                            <th class="text-muted small">Correo</th>
                            <th class="text-muted small">Tipo de Cliente</th>
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

<div class="modal fade" id="modal-cliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="titulo-modal-cliente">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Nuevo Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-cliente">
                <div class="modal-body">
                    <input type="hidden" name="id" id="cliente-id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Cédula</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person-vcard text-muted"></i></span>
                                <input type="number" class="form-control border-start-0 ps-0" id="cliente-cedula" name="cedula" placeholder="Ej: 12345678" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Teléfono</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-telephone text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="cliente-telefono" name="telefono" placeholder="Ej: 0414-1234567">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Nombre</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="cliente-nombre" name="nombre" placeholder="Primer nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Segundo Nombre</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="cliente-segNombre" name="segNombre" placeholder="Segundo nombre (opcional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Apellido</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="cliente-apellido" name="apellido" placeholder="Primer apellido" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Segundo Apellido</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="cliente-segApellido" name="segApellido" placeholder="Segundo apellido (opcional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Correo</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="cliente-correo" name="correo" placeholder="cliente@ejemplo.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Tipo de Cliente</label>
                            <div class="select2-modern-wrapper">
                                <span class="select2-modern-icon"><i class="bi bi-people text-muted"></i></span>
                                <select class="form-select" id="cliente-equipo" name="idEquipoCliente" required>
                                    <option value="">Seleccione un tipo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Dirección</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-geo-alt text-muted"></i></span>
                                <textarea class="form-control border-start-0 ps-0" id="cliente-direccion" name="direccion" rows="2" placeholder="Dirección (opcional)"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Estado</label>
                            <div class="input-group modern-input">
                                <span class="input-group-text border-end-0 bg-transparent"><i class="bi bi-toggle-on text-muted"></i></span>
                                <select class="form-select border-start-0 ps-0" id="cliente-status" name="status">
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
                        <i class="bi bi-check-lg me-1"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
