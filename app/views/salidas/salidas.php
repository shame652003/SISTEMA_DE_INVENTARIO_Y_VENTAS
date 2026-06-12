<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-box-arrow-up me-2"></i>Salidas de Productos</h2>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-salida" id="btn-nuevo">
            <i class="bi bi-plus-lg me-1"></i> Nueva Salida
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabla-salidas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Motivo</th>
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

<!-- Modal Salida -->
<div class="modal fade" id="modal-salida" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Salida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-salida">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="salida-producto" class="form-label">Producto</label>
                        <select class="form-select" id="salida-producto" name="producto_id" required>
                            <option value="">Seleccione un producto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="salida-cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="salida-cantidad" name="cantidad" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="salida-motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" id="salida-motivo" name="motivo" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Registrar Salida</button>
                </div>
            </form>
        </div>
    </div>
</div>
