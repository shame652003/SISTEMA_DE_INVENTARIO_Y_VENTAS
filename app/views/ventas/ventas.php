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
            <div class="card shadow">
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
                        <div class="alert alert-light border shadow-sm">
                            <div class="text-center mb-3">
                                <div class="display-6 text-primary mb-1">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-0" id="info-cliente-nombre">-</h5>
                                <small class="text-muted">C.I. <span id="info-cliente-cedula">-</span></small>
                            </div>

                            <hr class="my-2">

                            <div class="row g-2 mb-2">
                                <div class="col-4" id="info-cliente-telefono-wrap">
                                    <div class="border rounded-3 p-2 text-center bg-white">
                                        <small class="text-muted d-block"><i class="bi bi-telephone me-1"></i>Teléfono</small>
                                        <strong class="text-dark" id="info-cliente-telefono">-</strong>
                                    </div>
                                </div>
                                <div class="col-4" id="info-cliente-email-wrap">
                                    <div class="border rounded-3 p-2 text-center bg-white">
                                        <small class="text-muted d-block"><i class="bi bi-envelope me-1"></i>Email</small>
                                        <strong class="text-dark" id="info-cliente-email">-</strong>
                                    </div>
                                </div>
                                <div class="col-4" id="info-cliente-direccion-wrap">
                                    <div class="border rounded-3 p-2 text-center bg-white">
                                        <small class="text-muted d-block"><i class="bi bi-geo-alt me-1"></i>Dirección</small>
                                        <strong class="text-dark" id="info-cliente-direccion">-</strong>
                                    </div>
                                </div>
                            </div>

                            <div id="info-cliente-credito" class="text-center d-none">
                                <span class="badge bg-warning text-dark fs-6">Deuda USD: $<span id="info-cliente-saldo-usd">0.00</span></span>
                                <span class="badge bg-secondary fs-6">Deuda BCV: $<span id="info-cliente-saldo-bcv">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Producto</h5>
                </div>
                <div class="card-body">
                    <select class="form-select" id="select-producto" style="width: 100%;">
                        <option value="">Buscar producto por código o nombre...</option>
                    </select>

                    <div id="info-producto" class="d-none mt-3">
                        <div class="alert alert-light border shadow-sm">
                            <div class="text-center mb-3">
                                <div id="img-producto-venta-container" class="d-none mb-2">
                                    <img id="img-producto-venta" src="" alt="Producto" class="rounded shadow-sm" style="max-height: 130px; max-width: 100%; object-fit: contain;">
                                </div>
                                <h5 class="fw-bold text-dark mb-0">
                                    <span id="info-producto-codigo">-</span>
                                    <span class="text-muted mx-2">—</span>
                                    <span id="info-producto-nombre">-</span>
                                </h5>
                            </div>

                            <hr class="my-2">

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="border rounded-3 p-2 text-center bg-white">
                                        <small class="text-muted d-block">Tipo</small>
                                        <strong class="text-dark" id="info-producto-tipo">-</strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded-3 p-2 text-center bg-white">
                                        <small class="text-muted d-block">Stock</small>
                                        <strong id="info-producto-stock">-</strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded-3 p-2 text-center bg-white">
                                        <small class="text-muted d-block">Marca</small>
                                        <strong class="text-dark" id="info-producto-marca">-</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="rounded-3 p-3 text-center bg-success bg-opacity-10 border border-success border-opacity-25">
                                        <small class="text-success fw-semibold d-block">PRECIO USD</small>
                                        <strong class="text-success fs-5" id="info-producto-precio-usd">-</strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded-3 p-3 text-center bg-warning bg-opacity-10 border border-warning border-opacity-25">
                                        <small class="text-warning fw-semibold d-block">PRECIO VES</small>
                                        <strong class="text-dark fs-5" id="info-producto-precio-ves">-Bs.</strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded-3 p-3 text-center bg-info bg-opacity-10 border border-info border-opacity-25">
                                        <small class="text-info fw-semibold d-block">PRECIO $BCV</small>
                                        <strong class="text-info fs-5" id="info-producto-precio-bcv">-</strong>
                                    </div>
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
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Carrito de Compra</h5>
                    <span class="badge bg-secondary" id="carrito-contador">0</span>
                </div>
                <div class="card-body">
                    <div id="carrito-container">
                        <p class="text-muted text-center mb-0 py-3" id="carrito-vacio">Sin productos en el carrito</p>
                    </div>
                </div>
                <div class="card-footer bg-light d-none" id="carrito-footer">
                    <div class="row text-center fw-bold">
                        <div class="col-4">
                            <small class="text-muted d-block">TOTAL USD</small>
                            <span class="text-success fs-5" id="total-usd">$0.00</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">TOTAL VES</small>
                            <span class="text-dark fs-5" id="total-ves">Bs. 0.00</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">TOTAL $BCV</small>
                            <span class="text-info fs-5" id="total-bcv">$0.00</span>
                        </div>
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
    <br>
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
