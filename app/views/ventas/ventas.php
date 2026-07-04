<?php
use App\Core\UrlCipher;
$tiposPago = $tiposPago ?? [];
?>
<div class="container-fluid p-4">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
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

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body text-center py-5">
                    <i class="bi bi-cone-striped fs-1 text-muted mb-3 d-block"></i>
                    <h4 class="text-muted">Modulo en reconstruccion</h4>
                    <p class="text-muted">El modulo de ventas esta siendo reimplementado.</p>
                </div>
            </div>
        </div>
    </div>

</div>
