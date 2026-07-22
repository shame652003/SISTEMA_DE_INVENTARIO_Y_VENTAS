<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="header-icon bg-primary-soft">
                        <i class="bi bi-cash-stack text-primary"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Reportes de Pagos</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="<?= BASE_URL . \App\Core\UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                <li class="breadcrumb-item active">Reportes de Pagos</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="reportesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-historial" data-bs-toggle="tab" data-bs-target="#panel-historial" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> Historial de Pagos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-creditos" data-bs-toggle="tab" data-bs-target="#panel-creditos" type="button" role="tab">
                <i class="bi bi-exclamation-triangle me-1"></i> Créditos Pendientes
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reportesTabsContent">
        <div class="tab-pane fade show active" id="panel-historial" role="tabpanel">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-bold">Filtros de Busqueda</h6>
                </div>
                <div class="card-body">
                    <form id="form-reporte-pagos" novalidate>
                        <div class="row g-2 align-items-end">
                            <div class="col">
                                <label for="select-mes" class="form-label small fw-semibold text-muted">Fecha de consulta</label>
                                <select class="form-select" id="select-mes" name="mes" style="width: 100%;">
                                    <option value="">Todas las fechas</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" title="Generar Reporte">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="btn-limpiar-filtros" title="Limpiar filtros">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="btn-toggle-filtros" title="Filtros avanzados">
                                        <i class="bi bi-sliders"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-none mt-3" id="panel-filtros-avanzados">
                            <div class="bg-light rounded-3 p-3 border">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3 col-sm-6">
                                        <label for="metodo-pago" class="form-label small text-muted"><i class="bi bi-credit-card me-1"></i>Metodo de Pago</label>
                                        <select class="form-select" id="metodo-pago" name="metodo_pago">
                                            <option value="">Todos</option>
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="Transferencia">Transferencia</option>
                                            <option value="Punto">Punto</option>
                                            <option value="Biopago">Biopago</option>
                                            <option value="Credito">Credito</option>
                                            <option value="Zelle">Zelle</option>
                                            <option value="Binance USDT">Binance USDT</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="select-cliente" class="form-label small text-muted"><i class="bi bi-person me-1"></i>Cliente</label>
                                        <select class="form-select" id="select-cliente" name="cedula_cliente" style="width: 100%;">
                                            <option value="">Todos los clientes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="fecha-inicio" class="form-label small text-muted"><i class="bi bi-calendar3 me-1"></i>Fecha Inicio</label>
                                        <input type="date" class="form-control" id="fecha-inicio" name="fecha_inicio">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="fecha-fin" class="form-label small text-muted"><i class="bi bi-calendar3 me-1"></i>Fecha Fin</label>
                                        <input type="date" class="form-control" id="fecha-fin" name="fecha_fin">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex gap-2 mb-3 flex-wrap" id="filtros-rapidos">
                <button type="button" class="btn btn-sm btn-filtro active" data-filtro="todas">
                    <i class="bi bi-grid-fill me-1"></i> Todas las ventas
                </button>
                <button type="button" class="btn btn-sm btn-filtro btn-finalizadas" data-filtro="finalizadas">
                    <i class="bi bi-check-circle-fill me-1"></i> Solo Finalizadas
                </button>
                <button type="button" class="btn btn-sm btn-filtro btn-creditos" data-filtro="creditos">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Solo Créditos
                </button>
                <button type="button" class="btn btn-sm btn-filtro btn-abonos" data-filtro="abonos">
                    <i class="bi bi-arrow-repeat me-1"></i> Solo Abonos
                </button>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-list-ul text-primary"></i>
                        <h5 class="mb-0 fw-bold">Detalle de Pagos</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small" id="contador-pagos" style="display:none;"><i class="bi bi-circle-fill text-primary me-1" style="font-size:0.4rem;vertical-align:middle;"></i><span id="contador-num">0</span> ventas encontradas</span>
                        <button class="btn btn-sm btn-outline-danger" id="btn-pdf-historial" title="Descargar PDF del historial" style="display:none;">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0" id="tabla-historial-pagos" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th># Venta</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Métodos de Pago</th>
                                    <th class="text-end">Total BCV</th>
                                    <th class="text-end">Total VES</th>
                                    <th>Estado Crédito</th>
                                    <th>Vendedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox fs-4 mb-2 d-block"></i>Seleccione una fecha y genere el reporte</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="panel-creditos" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-people text-primary"></i>
                        <h5 class="mb-0 fw-bold">Clientes con Créditos Pendientes</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-warning" id="btn-cargar-creditos" title="Cargar Créditos">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="btn-pdf-creditos" title="Descargar PDF de créditos" style="display:none;">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0" id="tabla-creditos" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Saldo Deudor USD</th>
                                    <th class="text-end">Saldo Deudor BCV</th>
                                    <th>Última Actualización</th>
                                    <th class="text-center" style="width:100px">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4"><i class="bi bi-people fs-4 mb-2 d-block"></i>Presione cargar para ver créditos pendientes</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-0 shadow-sm d-none" id="card-pagos-cliente">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-primary"></i>
                            <h5 class="mb-0 fw-bold">Historial de Pagos — <span id="nombre-cliente-credito" class="fw-bold"></span></h5>
                        </div>
                        <div class="mt-1" id="credito-saldo-actual" style="display:none;">
                            <span class="badge bg-warning text-dark me-2 fs-6" id="credito-badge-usd">USD: $0.00</span>
                            <span class="badge bg-secondary fs-6" id="credito-badge-bcv">BCV: $0.00</span>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="btn-cerrar-cliente-detalle" title="Cerrar detalle">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0" id="tabla-pagos-cliente" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th># Venta</th>
                                    <th>Fecha</th>
                                    <th>Métodos</th>
                                    <th class="text-end">Total BCV</th>
                                    <th>Estado Crédito</th>
                                    <th>Referencia</th>
                                    <th>Vendedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4"><i class="bi bi-inbox fs-4 mb-2 d-block"></i>Sin pagos registrados</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-abonar-credito" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" id="abono-modal-header">
                <h5 class="modal-title" id="abono-modal-titulo"><i class="bi bi-cash-coin me-2"></i>Abonar a Crédito</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="abono-cedula" value="">
                <input type="hidden" id="abono-tasa-bcv" value="0">

                <div class="alert alert-info border-0 shadow-sm mb-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted d-block">Cliente</small>
                            <strong id="abono-cliente-nombre">-</strong>
                        </div>
                        <div class="col-12 mt-2">
                            <small class="text-muted d-block">Créditos pendientes por venta (FIFO)</small>
                            <div id="abono-creditos-lista" class="mt-1"></div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Deuda Total USD</small>
                            <strong class="text-danger" id="abono-deuda-usd">$0.00</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Deuda Total BCV</small>
                            <strong class="text-danger" id="abono-deuda-bcv">$0.00</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Equiv. en VES</small>
                            <strong class="text-warning" id="abono-deuda-ves">Bs. 0,00</strong>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Moneda <span class="text-danger">*</span></label>
                    <div class="btn-group w-100" role="group" id="abono-moneda-group">
                        <input type="radio" class="btn-check" name="abono-moneda" id="abono-moneda-usd" value="USD" checked>
                        <label class="btn btn-outline-success" for="abono-moneda-usd">$ USD</label>
                        <input type="radio" class="btn-check" name="abono-moneda" id="abono-moneda-ves" value="VES">
                        <label class="btn btn-outline-primary" for="abono-moneda-ves">Bs. VES</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                    <div class="btn-group flex-wrap w-100" role="group" id="abono-metodo-group">
                    </div>
                </div>

                <div class="mb-3" id="abono-monto-container">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6" id="abono-monto-ves-col" style="display:none;">
                            <label class="form-label">Monto VES <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Bs.</span>
                                <input type="number" class="form-control" id="abono-monto-ves" step="0.01" min="0.01" placeholder="0.00">
                            </div>
                            <small class="text-muted" id="abono-max-hint-ves">Máximo: Bs. 0,00</small>
                        </div>
                        <div class="col-md-6" id="abono-monto-bcv-col" style="display:none;">
                            <label class="form-label">≈ BCV</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="abono-monto-bcv" step="0.01" min="0.01" placeholder="0.00">
                            </div>
                            <small class="text-muted" id="abono-max-hint-bcv">Máximo: $0.00</small>
                        </div>
                        <div class="col-md-6" id="abono-monto-usd-col">
                            <label class="form-label">Monto USD <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="abono-monto" step="0.01" min="0.01" required placeholder="0.00">
                            </div>
                            <small class="text-muted" id="abono-max-hint">Máximo disponible: $0.00</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="abono-referencia" class="form-label">Referencia</label>
                    <input type="text" class="form-control" id="abono-referencia" placeholder="N° de referencia (opcional)">
                </div>

                <div class="alert alert-warning border-0 shadow-sm mb-0">
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted d-block">Vendedor</small>
                            <strong id="abono-vendedor">-</strong>
                        </div>
                        <div class="col-12 mt-2">
                            <small class="text-muted d-block">Restante después del abono</small>
                            <div class="d-flex gap-3">
                                <span><strong class="text-danger" id="abono-restante-usd">$0.00</strong> USD</span>
                                <span><strong class="text-danger" id="abono-restante-bcv">$0.00</strong> BCV</span>
                                <span><strong class="text-warning" id="abono-restante-ves">Bs. 0,00</strong> VES</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-abono">
                    <i class="bi bi-check-lg me-1"></i> <span id="abono-btn-text">Confirmar Abono</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-venta-detalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Detalle de Venta #<span id="modal-venta-id">-</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4" id="modal-venta-info">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Fecha</small>
                        <strong id="modal-venta-fecha">-</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Cliente</small>
                        <strong id="modal-venta-cliente">-</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Total USD</small>
                        <strong id="modal-venta-total-usd">-</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Total VES</small>
                        <strong id="modal-venta-total-ves">-</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Total BCV</small>
                        <strong id="modal-venta-total-bcv">-</strong>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Productos</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered align-middle mb-0" id="tabla-modal-productos">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">P.U. USD</th>
                                <th class="text-end">P.U. VES</th>
                                <th class="text-end">P.U. BCV</th>
                                <th class="text-end">Subtotal USD</th>
                                <th class="text-end">Subtotal VES</th>
                                <th class="text-end">Subtotal BCV</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-cash-stack me-2"></i>Pagos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" id="tabla-modal-pagos">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Moneda</th>
                                <th class="text-end">Monto</th>
                                <th>Referencia</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-pdf me-auto" id="btn-pdf-venta" title="Descargar PDF de esta venta">
                    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-abono-detalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Detalle de Abono #<span id="modal-abono-id">-</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Cliente</small>
                        <strong id="modal-abono-cliente">-</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Fecha</small>
                        <strong id="modal-abono-fecha">-</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Monto</small>
                        <strong id="modal-abono-monto">-</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Referencia</small>
                        <strong id="modal-abono-ref">-</strong>
                    </div>
                </div>
                <h6 class="fw-bold mb-3">Aplicado a las siguientes ventas (FIFO)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" id="tabla-modal-abono-aplicados">
                        <thead class="table-light">
                            <tr>
                                <th>Venta</th>
                                <th>Fecha Venta</th>
                                <th class="text-end">Monto Aplicado BCV</th>
                                <th class="text-end">Monto Aplicado VES</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
