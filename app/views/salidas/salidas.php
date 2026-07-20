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
                        <div class="header-icon bg-danger-soft">
                            <i class="bi bi-box-arrow-up text-danger"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Salidas de Productos</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Salidas</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-danger" id="btn-procesar-salida" disabled>
                            <i class="bi bi-check-circle me-1"></i> Procesar Salida
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Card: Nueva Salida -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nueva Salida</h5>
                </div>
                <div class="card-body">
                    <!-- Select2: Buscar Producto -->
                    <div class="mb-4">
                        <label for="select-producto" class="form-label fw-bold">Buscar Repuesto</label>
                        <select class="form-select" id="select-producto" style="width: 100%;">
                            <option value="">Escribe el código o nombre del repuesto...</option>
                        </select>
                    </div>

                    <!-- Info Producto Seleccionado -->
                    <div id="info-producto" class="mb-4 d-none">
                        <div class="alert alert-info border-0 shadow-sm">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <div id="img-producto-salida-container" class="d-none text-center">
                                        <img id="img-producto-salida" src="" alt="Imagen del producto" class="img-thumbnail" style="max-height: 120px; max-width: 100%; object-fit: contain;">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Código</small>
                                            <strong id="info-codigo">-</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Nombre</small>
                                            <strong id="info-nombre">-</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Tipo</small>
                                            <strong id="info-tipo">-</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Stock Actual</small>
                                            <strong id="info-stock" class="text-success">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Salida -->
                    <div id="form-salida-container" class="d-none">
                        <form id="form-salida">
                            <input type="hidden" id="producto-id" value="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="idTipoSalidaA" class="form-label">Tipo de Salida <span class="text-danger">*</span></label>
                                    <select class="form-select" id="idTipoSalidaA" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($tipos as $t): ?>
                                            <option value="<?= $t['idTipoSalidas'] ?>"><?= htmlspecialchars($t['tipoSalida']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-box"></i></span>
                                        <input type="number" class="form-control" id="cantidad" step="1" min="1" placeholder="0" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mt-3">
                                <label for="descripcion" class="form-label">Descripción / Motivo <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="descripcion" rows="2" placeholder="Ej: Producto dañado en almacén..." required></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary" id="btn-limpiar-form">
                                    <i class="bi bi-x-circle me-1"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-primary" id="btn-agregar-item">
                                    <i class="bi bi-plus-lg me-1"></i> Agregar a la Lista
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Productos en esta Salida -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Productos en esta Salida</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tabla-items-pendientes">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Cant.</th>
                                    <th class="text-center">Acc.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted text-center">
                                    <td colspan="5">Sin productos agregados</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3">Total:</td>
                                    <td class="text-end" id="total-cantidad">0</td>
                                    <td class="text-center" id="total-items">0 ítems</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="paginacion-items" class="d-none d-flex justify-content-between align-items-center mt-2">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-pagina-anterior" disabled>
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <small class="text-muted" id="pagina-info"></small>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-pagina-siguiente" disabled>
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Historial de Salidas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Salidas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-salidas" class="table table-striped table-hover align-middle" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Cantidad</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
