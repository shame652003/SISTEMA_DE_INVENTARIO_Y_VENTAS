<?php use App\Core\UrlCipher; ?>
<div class="container-fluid p-4">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm dashboard-header">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon bg-success-soft">
                            <i class="bi bi-box-seam text-success"></i>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de búsqueda y filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="buscar-stock" placeholder="Buscar por código o nombre...">
                    </div>
                </div>
                <div class="col-md-6">
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

    <!-- Tabla de Stock -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
            <div class="fw-semibold text-secondary">
                <i class="bi bi-box-seam me-2 text-success"></i>Inventario Actual
            </div>
            <div id="contador-stock" class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                <i class="bi bi-box me-1"></i><span id="total-productos">0</span> productos
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle w-100" id="tabla-stock">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted small">Código</th>
                            <th class="text-muted small">Producto</th>
                            <th class="text-muted small">Tipo</th>
                            <th class="text-muted small text-center">Stock Actual</th>
                            <th class="text-muted small text-center">Precio $Bcv</th>
                            <th class="text-muted small text-center">Precio En Bs</th>
                            <th class="text-muted small text-center">Precio $</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
