<?php
use App\Core\UrlCipher;
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
                            <h4 class="mb-1 fw-bold text-dark">Stock e Inventario</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item"><a href="<?= BASE_URL . UrlCipher::encrypt('/dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Stock</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary fs-6" id="total-productos">0 productos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-search me-2"></i>Búsqueda y Filtros</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="select-producto" class="form-label fw-bold">Buscar Repuesto</label>
                            <select class="form-select" id="select-producto" style="width: 100%;">
                                <option value="">Escribe el código o nombre del repuesto...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Filtros</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary active" data-filtro="todos">
                                    <i class="bi bi-list-ul me-1"></i> Todos
                                </button>
                                <button type="button" class="btn btn-outline-warning" data-filtro="bajo">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Stock Bajo
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-filtro="agotado">
                                    <i class="bi bi-x-circle me-1"></i> Agotados
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Detalle del Producto Seleccionado -->
    <div class="row" id="detalle-producto-container" style="display: none;">
        <div class="col-12">
            <div class="card shadow mb-4 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <!-- Imagen centrada -->
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div id="detalle-imagen" class="rounded-circle bg-white p-2 d-inline-block shadow-lg" style="width: 150px; height: 150px; overflow: hidden;">
                                <img src="" alt="Producto" class="img-fluid w-100 h-100" style="object-fit: cover;">
                            </div>
                        </div>
                        <!-- Información del producto -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h3 class="fw-bold mb-1" id="detalle-nombre">-</h3>
                                <p class="mb-0 opacity-75"><span id="detalle-codigo">-</span> | <span id="detalle-tipo">-</span> | <span id="detalle-marca">-</span></p>
                            </div>
                            <div class="row g-3">
                                <!-- Stock -->
                                <div class="col-md-3">
                                    <div class="bg-white bg-opacity-25 rounded-3 p-3 text-center">
                                        <small class="d-block opacity-75 mb-1">Stock Actual</small>
                                        <div class="fs-4 fw-bold" id="detalle-stock">-</div>
                                    </div>
                                </div>
                                <!-- Precio Bs -->
                                <div class="col-md-3">
                                    <div class="bg-white bg-opacity-25 rounded-3 p-3 text-center">
                                        <small class="d-block opacity-75 mb-1">Precio Bs</small>
                                        <div class="fs-5 fw-bold" id="detalle-precio-bs">-</div>
                                    </div>
                                </div>
                                <!-- Precio $Bcv -->
                                <div class="col-md-3">
                                    <div class="bg-white bg-opacity-25 rounded-3 p-3 text-center">
                                        <small class="d-block opacity-75 mb-1">Precio $Bcv</small>
                                        <div class="fs-5 fw-bold" id="detalle-precio-bcv">-</div>
                                    </div>
                                </div>
                                <!-- Precio $ venta usd -->
                                <div class="col-md-3">
                                    <div class="bg-white bg-opacity-25 rounded-3 p-3 text-center">
                                        <small class="d-block opacity-75 mb-1">Precio $ venta usd</small>
                                        <div class="fs-5 fw-bold" id="detalle-precio-usd">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-table me-2"></i>Inventario de Productos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-stock" class="table table-striped table-hover align-middle" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th>Imagen</th>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Stock Actual</th>
                                    <th class="text-center">Precio Bs</th>
                                    <th class="text-center">Precio $Bcv</th>
                                    <th class="text-center">Precio $ venta usd</th>
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
