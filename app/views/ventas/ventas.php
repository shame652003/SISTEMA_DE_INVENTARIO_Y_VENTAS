<?php
use App\Core\UrlCipher;
$tiposPago = $tiposPago ?? [];
?>
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="bi bi-cart-check fs-2"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold">Nueva Venta</h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0 small">
                                        <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-white text-decoration-none">Dashboard</a></li>
                                        <li class="breadcrumb-item text-white-50 active">Ventas</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cliente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-person-circle me-2"></i>Cliente
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-9">
                            <label for="select-cliente" class="form-label fw-semibold">Buscar Cliente</label>
                            <select class="form-select form-select-lg" id="select-cliente" style="width: 100%;">
                                <option value="">Escribe la cédula o nombre del cliente...</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modal-nuevo-cliente">
                                <i class="bi bi-person-plus me-2"></i>Registrar
                            </button>
                        </div>
                    </div>

                    <!-- Info Cliente -->
                    <div id="info-cliente" class="mt-3 d-none">
                        <div class="alert alert-info border-0 rounded-3 shadow-sm">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Cédula</small>
                                    <strong id="info-cedula">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Nombre</small>
                                    <strong id="info-nombre">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Teléfono</small>
                                    <strong id="info-telefono">-</strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Crédito Pendiente</small>
                                    <strong id="info-credito" class="text-danger">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-box-seam me-2"></i>Productos
                    </h5>
                    
                    <!-- Select2 Producto -->
                    <div class="mb-3">
                        <label for="select-producto" class="form-label fw-semibold">Buscar Repuesto</label>
                        <select class="form-select form-select-lg" id="select-producto" style="width: 100%;">
                            <option value="">Escribe el código o nombre del repuesto...</option>
                        </select>
                    </div>

                    <!-- Info Producto Seleccionado -->
                    <div id="info-producto" class="mb-3 d-none">
                        <div class="alert alert-light border rounded-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Stock Disponible</small>
                                    <span id="info-stock" class="badge rounded-pill fs-6">-</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Precio Bs</small>
                                    <strong id="info-precio-bs" class="text-primary">-</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Precio $Bcv</small>
                                    <strong id="info-precio-bcv" class="text-info">-</strong>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Cantidad</label>
                                    <div class="input-group input-group-lg">
                                        <button class="btn btn-outline-secondary" type="button" id="btn-cant-menos">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" id="cantidad" value="1" min="1" step="0.001">
                                        <button class="btn btn-outline-secondary" type="button" id="btn-cant-mas">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                        <button class="btn btn-primary" type="button" id="btn-agregar-producto">
                                            <i class="bi bi-cart-plus me-2"></i>Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Productos -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tabla-productos">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th class="text-center">Precio Bs</th>
                                    <th class="text-center">Precio $Bcv</th>
                                    <th class="text-center">Precio $</th>
                                    <th class="text-end">Cant.</th>
                                    <th class="text-end">Sub $</th>
                                    <th class="text-end">Sub Bs</th>
                                    <th class="text-center">Acc.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted text-center">
                                    <td colspan="10">Sin productos agregados</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="7" class="text-end">Total:</td>
                                    <td class="text-end" id="total-usd">$0.00</td>
                                    <td class="text-end" id="total-bs">Bs. 0.00</td>
                                    <td class="text-center" id="total-items">0 ítems</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="bi bi-credit-card me-2"></i>Pagos
                    </h5>

                    <!-- Total a pagar -->
                    <div class="alert alert-primary rounded-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 fw-bold">Total a pagar:</h4>
                            <h4 class="mb-0 fw-bold" id="total-pagar">$0.00</h4>
                        </div>
                    </div>

                    <!-- Métodos de pago (toggles) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Selecciona métodos de pago:</label>
                        <div class="d-flex flex-wrap gap-2" id="metodos-pago">
                            <?php foreach ($tiposPago as $tp): ?>
                                <button type="button" class="btn btn-outline-secondary rounded-pill metodo-pago-btn" 
                                        data-id="<?= $tp['idtipo_de_pagos'] ?>" 
                                        data-nombre="<?= htmlspecialchars($tp['tipoPago']) ?>">
                                    <?= htmlspecialchars($tp['tipoPago']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Detalle de pagos activos -->
                    <div id="detalle-pagos" class="d-none">
                        <div class="border rounded-3 p-3 bg-light">
                            <div id="pagos-container"></div>
                            
                            <div class="mt-3 pt-3 border-top">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold">✅ Pagado:</span>
                                            <span class="fw-bold text-success" id="total-pagado">$0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold">⚠️ Pendiente:</span>
                                            <span class="fw-bold text-danger" id="pendiente">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-3">
                <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill" id="btn-limpiar">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-lg rounded-pill" id="btn-procesar-venta" disabled>
                    <i class="bi bi-check-circle me-2"></i>Procesar Venta <span id="btn-total">$0.00</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nuevo Cliente -->
<div class="modal fade" id="modal-nuevo-cliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-nuevo-cliente">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cliente-cedula" class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg" id="cliente-cedula" name="cedula" required>
                    </div>
                    <div class="mb-3">
                        <label for="cliente-nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="cliente-nombre" name="nombre" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="cliente-apellido" class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="cliente-apellido" name="apellido" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>
