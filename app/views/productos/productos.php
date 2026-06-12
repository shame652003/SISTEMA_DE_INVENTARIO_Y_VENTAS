<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-box-seam me-2"></i>Productos</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-producto" id="btn-nuevo">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Producto
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabla-productos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Producto -->
<div class="modal fade" id="modal-producto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-producto">
                <div class="modal-body">
                    <input type="hidden" name="id" id="producto-id">
                    <div class="mb-3">
                        <label for="producto-codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="producto-codigo" name="codigo" required>
                    </div>
                    <div class="mb-3">
                        <label for="producto-nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="producto-nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="producto-precio" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="producto-precio" name="precio" required>
                    </div>
                    <div class="mb-3">
                        <label for="producto-stock" class="form-label">Stock Inicial</label>
                        <input type="number" class="form-control" id="producto-stock" name="stock" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
