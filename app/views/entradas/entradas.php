<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-box-arrow-in-down me-2"></i>Entradas de Productos</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-entrada" id="btn-nuevo">
            <i class="bi bi-plus-lg me-1"></i> Nueva Entrada
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabla-entradas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Proveedor</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Entrada -->
<div class="modal fade" id="modal-entrada" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-entrada">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="entrada-producto" class="form-label">Producto</label>
                        <select class="form-select" id="entrada-producto" name="producto_id" required>
                            <option value="">Seleccione un producto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="entrada-cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="entrada-cantidad" name="cantidad" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="entrada-proveedor" class="form-label">Proveedor</label>
                        <input type="text" class="form-control" id="entrada-proveedor" name="proveedor">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Entrada</button>
                </div>
            </form>
        </div>
    </div>
</div>
