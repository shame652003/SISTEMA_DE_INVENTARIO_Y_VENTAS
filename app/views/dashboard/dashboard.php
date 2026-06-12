<?php use App\Core\UrlCipher; ?>
<div class="container-fluid p-4">
<?php $r = $resumen ?? []; ?>

<!-- Header del Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm dashboard-header">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon bg-primary-soft">
                        <i class="bi bi-speedometer2 text-primary"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Dashboard</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Resumen</li>
                            </ol>
                        </nav>
                        <div class="mt-1 d-flex align-items-center gap-2">
                            <i class="bi bi-currency-dollar text-success"></i>
                            <span class="fw-semibold text-dark">Taza BCV: 1 USD = 535.00 Bs</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-taza-bcv">
                        <i class="bi bi-plus-lg me-1"></i> Taza BCV
                    </button>
                    <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-margen-dolar">
                        <i class="bi bi-plus-lg me-1"></i> Margen de Ganancia $
                    </button>
                    <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-margen-bolivar">
                        <i class="bi bi-plus-lg me-1"></i> Margen de Ganancia Bs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-box-seam text-primary"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-label">Productos Activos</div>
                    <div class="stat-value"><?= number_format($r['productos_activos'] ?? 0) ?></div>
                    <div class="stat-note text-danger">
                        <i class="bi bi-exclamation-circle me-1"></i>Agotados: <?= number_format($r['productos_agotados'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-warning-soft">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-label">Stock Bajo</div>
                    <div class="stat-value"><?= number_format($r['productos_stock_bajo'] ?? 0) ?></div>
                    <div class="stat-note text-muted">Requiere reposición</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-info-soft">
                    <i class="bi bi-cart-check text-info"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-label">Ventas Hoy</div>
                    <div class="stat-value"><?= number_format($r['ventas_hoy'] ?? 0) ?></div>
                    <div class="stat-note text-muted">USD <?= number_format($r['total_usd_hoy'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-danger-soft">
                    <i class="bi bi-cash-stack text-danger"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-label">Créditos Pendientes</div>
                    <div class="stat-value">$<?= number_format($r['creditos_pendientes_usd'] ?? 0, 2) ?></div>
                    <div class="stat-note text-muted">Por cobrar</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimas Ventas + Resumen -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-graph-up me-2 text-primary"></i>Últimas Ventas
                </div>
                <a href="<?= BASE_URL . UrlCipher::encrypt('/ventas') ?>" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle" id="tabla-ultimas-ventas">
                        <thead class="table-light">
                            <tr>
                                <th class="text-muted small">#</th>
                                <th class="text-muted small">Cliente</th>
                                <th class="text-muted small">Total</th>
                                <th class="text-muted small">Fecha</th>
                                <th class="text-muted small">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    Sin ventas recientes
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-clock-history me-2 text-info"></i>Resumen del Día
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="bi bi-cart-check me-2 text-primary"></i>Ventas hoy</span>
                        <span class="fw-semibold"><?= number_format($r['ventas_hoy'] ?? 0) ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="bi bi-currency-dollar me-2 text-success"></i>Total USD hoy</span>
                        <span class="fw-semibold">$<?= number_format($r['total_usd_hoy'] ?? 0, 2) ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="bi bi-currency-bitcoin me-2 text-warning"></i>Total VES hoy</span>
                        <span class="fw-semibold">Bs.<?= number_format($r['total_ves_hoy'] ?? 0, 2) ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="bi bi-people me-2 text-info"></i>Clientes hoy</span>
                        <span class="fw-semibold"><?= number_format($r['clientes_nuevos_hoy'] ?? 0) ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="bi bi-box-seam me-2 text-danger"></i>Productos agotados</span>
                        <span class="fw-semibold text-danger"><?= number_format($r['productos_agotados'] ?? 0) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-lightning-charge text-warning"></i>
            <span class="fw-semibold text-secondary">Acciones Rápidas</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL . UrlCipher::encrypt('/ventas') ?>" class="btn btn-action w-100 text-decoration-none">
            <div class="action-icon bg-primary-soft">
                <i class="bi bi-cart-plus text-primary"></i>
            </div>
            <div class="action-label">Ventas</div>
            <div class="action-desc">Gestionar ventas</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL . UrlCipher::encrypt('/productos') ?>" class="btn btn-action w-100 text-decoration-none">
            <div class="action-icon bg-success-soft">
                <i class="bi bi-box-seam text-success"></i>
            </div>
            <div class="action-label">Productos</div>
            <div class="action-desc">Gestionar productos</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL . UrlCipher::encrypt('/clientes') ?>" class="btn btn-action w-100 text-decoration-none">
            <div class="action-icon bg-info-soft">
                <i class="bi bi-person-plus text-info"></i>
            </div>
            <div class="action-label">Clientes</div>
            <div class="action-desc">Gestionar clientes</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL . UrlCipher::encrypt('/entradas') ?>" class="btn btn-action w-100 text-decoration-none">
            <div class="action-icon bg-warning-soft">
                <i class="bi bi-box-arrow-in-down text-warning"></i>
            </div>
            <div class="action-label">Entradas</div>
            <div class="action-desc">Ingresar stock</div>
        </a>
    </div>
</div>

<!-- Modal: Taza BCV -->
<div class="modal fade" id="modal-taza-bcv" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-currency-dollar me-2 text-success"></i>Actualizar Taza BCV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-taza-bcv">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Taza Actual</label>
                        <div class="input-group">
                            <span class="input-group-text">1 USD =</span>
                            <input type="number" step="0.01" class="form-control" name="taza" value="535.00" required>
                            <span class="input-group-text">Bs</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Vigencia</label>
                        <input type="date" class="form-control" name="fecha_vigencia" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observación</label>
                        <textarea class="form-control" name="observacion" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Actualizar Taza
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Margen de Ganancia $ -->
<div class="modal fade" id="modal-margen-dolar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-currency-dollar me-2 text-primary"></i>Margen de Ganancia USD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-margen-dolar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Margen de Ganancia (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" class="form-control" name="margen_dolar" value="30" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Porcentaje de ganancia sobre el costo en dólares.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio de Referencia</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="precio_referencia_dolar" value="1.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Guardar Margen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Margen de Ganancia Bs -->
<div class="modal fade" id="modal-margen-bolivar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-currency-bitcoin me-2 text-warning"></i>Margen de Ganancia VES</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-margen-bolivar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Margen de Ganancia (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" class="form-control" name="margen_bolivar" value="30" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Porcentaje de ganancia sobre el costo en bolívares.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio de Referencia</label>
                        <div class="input-group">
                            <span class="input-group-text">Bs</span>
                            <input type="number" step="0.01" class="form-control" name="precio_referencia_bolivar" value="1.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-lg me-1"></i> Guardar Margen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
