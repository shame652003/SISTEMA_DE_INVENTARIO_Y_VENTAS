<?php
use App\Core\UrlCipher;
$tipos = $tipos ?? [];
?>
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon bg-primary-soft">
                            <i class="bi bi-box-seam text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Gestión de Productos</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Productos</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-lista-productos" id="btn-ver-productos">
                            <i class="bi bi-plus-lg me-1"></i> Ver Productos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Card Imagen -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3"><i class="bi bi-image me-2"></i>Imagen del Producto</h5>
                    <div id="preview-container" class="mb-3">
                        <img id="img-preview" src="<?= BASE_URL ?>/assets/img/placeholder-product.svg" alt="Vista previa" class="img-fluid rounded border" style="max-height: 250px; object-fit: cover;">
                    </div>
                    <div class="mb-3">
                        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Formulario -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-gear-wide-connected me-2"></i>Datos del Repuesto</h5>

                    <form id="form-producto" enctype="multipart/form-data">
                        <input type="hidden" id="producto-id" name="id" value="">

                        <div class="row">
                            <!-- Tipo Producto -->
                            <div class="col-md-6 mb-3">
                                <label for="idTipoA" class="form-label">Tipo de Repuesto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" id="idTipoA" name="idTipoA" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($tipos as $t): ?>
                                            <option value="<?= $t['idTipoA'] ?>"><?= htmlspecialchars($t['tipo']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-tipo-producto" title="Nuevo tipo">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Código -->
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Ej: REP-001" required autocomplete="off">
                                </div>
                            </div>

                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del Repuesto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Filtro de aceite" required autocomplete="off">
                                </div>
                            </div>

                            <!-- Marca -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="acMarca">
                                    <label class="form-check-label" for="acMarca">
                                        ¿Incluir marca?
                                    </label>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-award"></i></span>
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Ej: Yamaha, Honda" disabled autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="reset" class="btn btn-danger" id="btn-cancelar">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" id="btn-guardar">
                                <i class="bi bi-save me-1"></i> <span id="btn-guardar-texto">Registrar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lista de Productos -->
<div class="modal fade" id="modal-lista-productos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-list-ul me-2"></i>Productos Registrados</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabla-productos" class="table table-striped table-hover align-middle" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>Imagen</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Marca</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Tipo Producto -->
<div class="modal fade" id="modal-tipo-producto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-tags me-2"></i>Nuevo Tipo de Repuesto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-tipo-producto">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tipo-nombre" class="form-label">Nombre del Tipo</label>
                        <input type="text" class="form-control" id="tipo-nombre" name="tipo" required autocomplete="off" placeholder="Ej: Filtros, Frenos, Motor">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Tipo</button>
                </div>
            </form>
        </div>
    </div>
</div>
