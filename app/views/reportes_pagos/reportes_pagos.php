<div class="container-fluid p-4">
    <h2 class="mb-4"><i class="bi bi-cash-stack me-2"></i>Reportes de Pagos</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form id="form-reporte-pagos" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="fecha-inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha-inicio" name="fecha_inicio" required>
                </div>
                <div class="col-md-3">
                    <label for="fecha-fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha-fin" name="fecha_fin" required>
                </div>
                <div class="col-md-3">
                    <label for="metodo-pago" class="form-label">Método de Pago</label>
                    <select class="form-select" id="metodo-pago" name="metodo_pago">
                        <option value="">Todos</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body" id="resultado-reporte-pagos">
            <p class="text-muted text-center">Seleccione un rango de fechas y genere el reporte</p>
        </div>
    </div>
</div>
