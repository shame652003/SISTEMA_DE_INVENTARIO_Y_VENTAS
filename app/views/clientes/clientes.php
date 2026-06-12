<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Clientes</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-cliente" id="btn-nuevo">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Cliente
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabla-clientes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Dirección</th>
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

<!-- Modal Cliente -->
<div class="modal fade" id="modal-cliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-cliente">
                <div class="modal-body">
                    <input type="hidden" name="id" id="cliente-id">
                    <div class="mb-3">
                        <label for="cliente-nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="cliente-nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="cliente-telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="cliente-telefono" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="cliente-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="cliente-email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="cliente-direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="cliente-direccion" name="direccion" rows="2"></textarea>
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
