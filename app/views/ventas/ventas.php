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
                            <i class="bi bi-person-lines-fill text-primary"></i>
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
