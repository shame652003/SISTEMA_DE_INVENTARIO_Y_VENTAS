<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-cart-check me-2"></i>Ventas</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-venta" id="btn-nuevo">
            <i class="bi bi-plus-lg me-1"></i> Nueva Venta
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabla-ventas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Método de Pago</th>
                            <th>Fecha</th>
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

<!-- Modal Venta -->
<div class="modal fade" id="modal-venta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-venta">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="venta-cliente" class="form-label">Cliente</label>
                            <select class="form-select" id="venta-cliente" name="cliente_id" required>
                                <option value="">Seleccione un cliente</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="venta-metodo-pago" class="form-label">Método de Pago</label>
                            <select class="form-select" id="venta-metodo-pago" name="metodo_pago">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>

                    <h6>Productos</h6>
                    <div id="venta-productos">
                        <div class="row mb-2 venta-producto-item">
                            <div class="col-md-5">
                                <select class="form-select" name="producto_id[]" required>
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="0.01" class="form-control" name="precio[]" placeholder="Precio" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm btn-remover-producto">&times;</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-agregar-producto">
                        <i class="bi bi-plus-lg me-1"></i> Agregar Producto
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>
