<?php
use App\Core\UrlCipher;
$tasa = $tasa ?? null;
$margenUsd = $margenUsd ?? null;
$margenVes = $margenVes ?? null;
?>
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon bg-primary-soft">
                            <i class="bi bi-box-arrow-in-down text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Entradas de Productos</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Entradas</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" id="btn-procesar-entrada" disabled>
                            <i class="bi bi-check-circle me-1"></i> Procesar Entrada
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Card: Nueva Entrada -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nueva Entrada</h5>
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
                                    <div id="img-producto-entrada-container" class="d-none text-center">
                                        <img id="img-producto-entrada" src="" alt="Imagen del producto" class="img-thumbnail" style="max-height: 120px; max-width: 100%; object-fit: contain;">
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
                                            <strong id="info-stock">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Entrada -->
                    <div id="form-entrada-container" class="d-none">
                        <form id="form-entrada">
                            <input type="hidden" id="producto-id" value="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="costo-usd" class="form-label">Costo USD <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                        <input type="number" class="form-control" id="costo-usd" step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-box"></i></span>
                                        <input type="number" class="form-control" id="cantidad" step="1" min="0" placeholder="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkbox-solo-precio">
                                        <label class="form-check-label" for="checkbox-solo-precio">
                                            Solo actualizar precio de costo (sin mover stock)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Precios Calculados -->
                            <div class="row g-3 mt-3">
                                <div class="col-md-4">
                                    <label class="form-label">Precio Venta USD (calculado)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success text-white"><i class="bi bi-tag"></i></span>
                                        <input type="text" class="form-control bg-light" id="precio-venta-usd" readonly value="$0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Precio Venta VES (calculado)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-warning text-dark"><i class="bi bi-tag"></i></span>
                                        <input type="text" class="form-control bg-light" id="precio-venta-ves" readonly value="Bs. 0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Precio Venta BCV (calculado)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info text-white"><i class="bi bi-tag"></i></span>
                                        <input type="text" class="form-control bg-light" id="precio-venta-bcv" readonly value="$0.00">
                                    </div>
                                </div>
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

        <!-- Card: Productos en esta Entrada -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Productos en esta Entrada</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tabla-items-pendientes">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th class="text-end">Costo</th>
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

    <!-- Card: Historial de Entradas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Entradas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-entradas" class="table table-striped table-hover align-middle" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Ítems</th>
                                    <th class="text-end">Cantidad Total</th>
                                    <th class="text-center">Acciones</th>
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

<!-- Modal: Detalle de Entrada -->
<div class="modal fade" id="modal-detalle-entrada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Detalle de Entrada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <div class="row">
                        <div class="col-md-4"><strong>Entrada #:</strong> <span id="detalle-id">-</span></div>
                        <div class="col-md-4"><strong>Fecha:</strong> <span id="detalle-fecha">-</span></div>
                        <div class="col-md-4"><strong>Hora:</strong> <span id="detalle-hora">-</span></div>
                    </div>
                    <div class="mt-2"><strong>Descripción:</strong> <span id="detalle-descripcion">-</span></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="tabla-detalle-entrada">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th class="text-end">Precio Costo</th>
                                <th class="text-end">P. Venta USD</th>
                                <th class="text-end">P. Venta VES</th>
                                <th class="text-end">P. Venta BCV</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Subtotal USD</th>
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
