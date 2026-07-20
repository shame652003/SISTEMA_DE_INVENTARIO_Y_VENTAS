<?php use App\Core\UrlCipher; ?>
<div class="container-fluid p-4">
<?php $r = $resumen ?? []; 
$tasa = $tasaBcv['tasa_ves_por_usd'] ?? '--';
$fechaTasa = $tasaBcv['fecha_tasa'] ?? '';
?>

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
                            <span class="fw-semibold text-dark" id="bcv-rate-text">
                                Tasa BCV: 1 USD = <?= htmlspecialchars($tasa) ?> Bs
                            </span>
                            <?php
                            $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                            $fechaEs = '';
                            if ($fechaTasa) {
                                $d = new DateTime($fechaTasa);
                                $fechaEs = $d->format('j') . ' de ' . $meses[(int)$d->format('n')] . ' de ' . $d->format('Y');
                            }
                            ?>
                            <?php if ($fechaTasa): ?>
                            <small class="text-muted" id="bcv-rate-date">(<?= htmlspecialchars($fechaEs) ?>)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-taza-bcv">
                        <i class="bi bi-currency-dollar me-1"></i> Tasa BCV
                    </button>
                    <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-margen-dolar">
                        <i class="bi bi-cash me-1"></i> Margen USD
                    </button>
                    <button class="btn btn-header-action" data-bs-toggle="modal" data-bs-target="#modal-margen-bolivar">
                        <i class="bi bi-cash-coin me-1"></i> Margen VES
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-box-seam text-primary"></i>
                </div>
                <div class="ms-3 flex-grow-1">
                    <div class="stat-label">Productos Activos</div>
                    <div class="stat-value" id="stat-productos-activos"><?= number_format($r['productos_activos'] ?? 0) ?></div>
                    <div class="stat-note text-danger" id="stat-agotados">
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
                    <div class="stat-value" id="stat-stock-bajo"><?= number_format($r['productos_stock_bajo'] ?? 0) ?></div>
                    <div class="stat-note text-muted">Requiere reposicion</div>
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
                    <div class="stat-value" id="stat-ventas-hoy"><?= number_format($r['ventas_hoy'] ?? 0) ?></div>
                    <div class="stat-note text-muted">
                        <span id="stat-total-bcv-hoy">BCV $<?= number_format($r['total_bcv_hoy'] ?? 0, 2) ?></span>
                        <span class="mx-1">|</span>
                        <span id="stat-total-ves-hoy">VES Bs.<?= number_format($r['total_ves_hoy'] ?? 0, 2) ?></span>
                    </div>
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
                    <div class="stat-label">Creditos Pendientes</div>
                    <div class="stat-value" id="stat-creditos">Bs.<?= number_format($r['creditos_pendientes_bcv'] ?? 0, 2) ?></div>
                    <div class="stat-note text-muted">Por cobrar</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graficas -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-person-heart me-2 text-danger"></i>Clientes Mas Frecuentes
                </div>
            </div>
            <div class="card-body">
                <div id="chart-clientes-frecuentes" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-clock-history me-2 text-warning"></i>Creditos Mas Antiguos
                </div>
            </div>
            <div class="card-body">
                <div id="chart-creditos-antiguos" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-trophy me-2 text-warning"></i>Productos Mas Vendidos
                </div>
            </div>
            <div class="card-body">
                <div id="chart-productos-mas-vendidos" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-pie-chart me-2 text-info"></i>Tipos de Pago Mas Usados
                </div>
            </div>
            <div class="card-body">
                <div id="chart-pagos-por-tipo" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-bar-chart me-2 text-success"></i>Ventas por Tipo de Producto
                </div>
            </div>
            <div class="card-body">
                <div id="chart-ventas-por-tipo-producto" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Ultimas Ventas -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <div class="fw-semibold text-secondary">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Ultimas Ventas
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
                                <th class="text-muted small">Vendedor</th>
                                <th class="text-muted small">Total BCV</th>
                                <th class="text-muted small">Total VES</th>
                                <th class="text-muted small">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4" id="ventas-vacio">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    Cargando ventas...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acciones Rapidas -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-lightning-charge text-warning"></i>
            <span class="fw-semibold text-secondary">Acciones Rapidas</span>
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

<!-- Modal: Tasa BCV -->
<div class="modal fade" id="modal-taza-bcv" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-currency-dollar me-2 text-success"></i>Actualizar Tasa BCV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-taza-bcv">
                <div class="modal-body">
                    <button type="button" class="btn btn-outline-info btn-sm w-100 mb-3" id="btn-consultar-api">
                        <i class="bi bi-cloud-download me-1"></i> Consultar tasa desde API (ve.dolarapi.com)
                    </button>
                    <div class="mb-3">
                        <label class="form-label">Tasa (1 USD = ? Bs)</label>
                        <div class="input-group">
                            <span class="input-group-text">1 USD =</span>
                            <input type="number" step="0.0001" class="form-control" name="tasa" id="input-tasa-bcv" value="<?= htmlspecialchars($tasa) ?>" required>
                            <span class="input-group-text">Bs</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Vigencia</label>
                        <input type="date" class="form-control" name="fecha_vigencia" id="input-fecha-bcv" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observacion</label>
                        <textarea class="form-control" name="observacion" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Actualizar Tasa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Margen de Ganancia USD -->
<div class="modal fade" id="modal-margen-dolar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-cash me-2 text-primary"></i>Margen de Ganancia USD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-margen-dolar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Margen de Ganancia (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="porcentaje" id="input-margen-usd" value="<?= htmlspecialchars($margenUsd['porcentaje'] ?? '50') ?>" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Porcentaje de ganancia sobre el costo en dolares. Actual: <?= htmlspecialchars($margenUsd['porcentaje'] ?? '--') ?>%</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observacion</label>
                        <textarea class="form-control" name="observacion" rows="2"></textarea>
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

<!-- Modal: Margen de Ganancia VES -->
<div class="modal fade" id="modal-margen-bolivar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2 text-warning"></i>Margen de Ganancia VES</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-margen-bolivar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Margen de Ganancia (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="porcentaje" id="input-margen-ves" value="<?= htmlspecialchars($margenVes['porcentaje'] ?? '75') ?>" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Porcentaje de ganancia sobre el costo en bolivares. Actual: <?= htmlspecialchars($margenVes['porcentaje'] ?? '--') ?>%</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observacion</label>
                        <textarea class="form-control" name="observacion" rows="2"></textarea>
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
