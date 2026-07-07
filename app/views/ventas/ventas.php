<?php
use App\Core\UrlCipher;
$tiposPago = $tiposPago ?? [];
?>
<div class="container-fluid p-4">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon bg-primary-soft">
                            <i class="bi bi-cart-check text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Gestión de Ventas</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Ventas</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-info fs-6" id="tasa-bcv-display">Tasa BCV: Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Cliente</h5>
                </div>
                <div class="card-body">
                    <select class="form-select" id="select-cliente" style="width: 100%;">
                        <option value="">Buscar cliente por cédula o nombre...</option>
                    </select>
                    <div class="mt-2 text-end">
                        <button class="btn btn-sm btn-outline-success" id="btn-nuevo-cliente" type="button">
                            <i class="bi bi-person-plus me-1"></i> Nuevo Cliente
                        </button>
                    </div>
                    <div id="info-cliente" class="d-none mt-3">
                        <div class="alert alert-info border-0 shadow-sm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Nombre</small>
                                    <strong id="info-cliente-nombre">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Cédula</small>
                                    <strong id="info-cliente-cedula">-</strong>
                                </div>
                                <div class="col-md-3" id="info-cliente-telefono-wrap">
                                    <small class="text-muted d-block">Teléfono</small>
                                    <strong id="info-cliente-telefono">-</strong>
                                </div>
                                <div class="col-md-6" id="info-cliente-email-wrap">
                                    <small class="text-muted d-block">Email</small>
                                    <strong id="info-cliente-email">-</strong>
                                </div>
                                <div class="col-md-6" id="info-cliente-direccion-wrap">
                                    <small class="text-muted d-block">Dirección</small>
                                    <strong id="info-cliente-direccion">-</strong>
                                </div>
                            </div>
                            <div id="info-cliente-credito" class="mt-2 d-none">
                                <span class="badge bg-warning text-dark me-1">USD: $<span id="info-cliente-saldo-usd">0.00</span></span>
                                <span class="badge bg-secondary">BCV: $<span id="info-cliente-saldo-bcv">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Producto</h5>
                </div>
                <div class="card-body">
                    <select class="form-select" id="select-producto" style="width: 100%;">
                        <option value="">Buscar producto por código o nombre...</option>
                    </select>

                    <div id="info-producto" class="d-none mt-3">
                        <div class="alert alert-secondary border-0 shadow-sm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Código</small>
                                    <strong id="info-producto-codigo">-</strong>
                                </div>
                                <div class="col-md-8">
                                    <small class="text-muted d-block">Nombre</small>
                                    <strong id="info-producto-nombre">-</strong>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted d-block">Tipo</small>
                                    <strong id="info-producto-tipo">-</strong>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted d-block">Stock</small>
                                    <strong id="info-producto-stock">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Precio USD</small>
                                    <strong id="info-producto-precio-usd">-</strong>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted d-block">Precio VES</small>
                                    <strong id="info-producto-precio-ves">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Precio $BCV</small>
                                    <strong id="info-producto-precio-bcv">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="form-carrito-container" class="d-none mt-3">
                        <form id="form-carrito">
                            <input type="hidden" id="producto-id" value="">
                            <input type="hidden" id="producto-costo-usd" value="0">
                            <input type="hidden" id="producto-precio-usd" value="0">
                            <input type="hidden" id="producto-precio-ves" value="0">

                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cantidad" step="1" min="1" placeholder="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Subtotal USD</label>
                                    <input type="text" class="form-control bg-light" id="preview-subtotal-usd" readonly value="$0.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Subtotal VES</label>
                                    <input type="text" class="form-control bg-light" id="preview-subtotal-ves" readonly value="Bs. 0.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Subtotal $BCV</label>
                                    <input type="text" class="form-control bg-light" id="preview-subtotal-bcv" readonly value="$0.00">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary" id="btn-limpiar-form">
                                    <i class="bi bi-x-circle me-1"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-primary" id="btn-agregar-carrito">
                                    <i class="bi bi-cart-plus me-1"></i> Agregar al Carrito
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Carrito de Compra</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="tabla-carrito">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th class="text-center" style="width:80px">Cant</th>
                                    <th class="text-end">P.U. USD</th>
                                    <th class="text-end">P.U. VES</th>
                                    <th class="text-end">P.U. $BCV</th>
                                    <th class="text-end">Sub. USD</th>
                                    <th class="text-end">Sub. VES</th>
                                    <th class="text-end">Sub. $BCV</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted text-center">
                                    <td colspan="10">Sin productos en el carrito</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="6" class="text-end">Totales:</td>
                                    <td class="text-end" id="total-usd">$0.00</td>
                                    <td class="text-end" id="total-ves">Bs. 0.00</td>
                                    <td class="text-end" id="total-bcv">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Métodos de Pago</h5>
                        <div class="btn-group btn-group-sm" role="group" id="moneda-global-group">
                            <input type="radio" class="btn-check" name="moneda-global" id="moneda-usd" value="USD" checked>
                            <label class="btn btn-outline-success" for="moneda-usd">$ USD</label>
                            <input type="radio" class="btn-check" name="moneda-global" id="moneda-ves" value="VES">
                            <label class="btn btn-outline-primary" for="moneda-ves">Bs. VES</label>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" id="btn-agregar-pago" disabled>
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
                <div class="card-body">
                    <div id="pagos-container">
                        <p class="text-muted text-center mb-0" id="pagos-vacio">Agregue productos al carrito y luego configure los pagos.</p>
                    </div>
                    <div id="resumen-pagos" class="mt-3 pt-3 border-top d-none">
                        <div class="d-flex justify-content-between mb-1">
                            <span id="resumen-label-total">Total Venta USD:</span>
                            <strong id="resumen-total">$0.00</strong>
                        </div>
                        <div id="resumen-total-bcv-container" class="d-flex justify-content-between mb-1 d-none">
                            <span>Equivalente $BCV:</span>
                            <strong id="resumen-total-bcv">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-success">
                            <span>Total Pagado:</span>
                            <strong id="resumen-pagado">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between text-danger fw-bold">
                            <span>Pendiente:</span>
                            <strong id="resumen-pendiente">$0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-outline-danger" id="btn-cancelar-compra">
                    <i class="bi bi-x-circle me-1"></i> Cancelar Compra
                </button>
                <button class="btn btn-success btn-lg" id="btn-procesar-venta" disabled>
                    <i class="bi bi-check-circle me-1"></i> Procesar Venta
                </button>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modal-nuevo-cliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Registro Rápido de Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="form-nuevo-cliente">
                    <div class="mb-3">
                        <label for="nc-cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="nc-cedula" required min="1" placeholder="Ej: 12345678">
                    </div>
                    <div class="mb-3">
                        <label for="nc-nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nc-nombre" required placeholder="Nombre">
                    </div>
                    <div class="mb-3">
                        <label for="nc-apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nc-apellido" required placeholder="Apellido">
                    </div>
                    <div class="mb-3">
                        <label for="nc-tipo-cliente" class="form-label">Tipo de Cliente <span class="text-danger">*</span></label>
                        <select class="form-select" id="nc-tipo-cliente" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-guardar-nuevo-cliente">
                    <i class="bi bi-check-lg me-1"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<script id="tiposPagoData" type="application/json"><?= json_encode($tiposPago, JSON_UNESCAPED_UNICODE) ?></script>
