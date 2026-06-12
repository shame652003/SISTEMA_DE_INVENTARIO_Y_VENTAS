<div class="container-fluid p-4">
    <h2 class="mb-4"><i class="bi bi-graph-up me-2"></i>Reportes Generales</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form id="form-reporte-general" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="tipo-reporte" class="form-label">Tipo de Reporte</label>
                    <select class="form-select" id="tipo-reporte" name="tipo" required>
                        <option value="">Seleccione</option>
                        <option value="ventas">Ventas</option>
                        <option value="entradas">Entradas</option>
                        <option value="salidas">Salidas</option>
                        <option value="inventario">Inventario Actual</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha-inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha-inicio" name="fecha_inicio">
                </div>
                <div class="col-md-3">
                    <label for="fecha-fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha-fin" name="fecha_fin">
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
        <div class="card-body" id="resultado-reporte-general">
            <p class="text-muted text-center">Seleccione el tipo de reporte y genere los resultados</p>
        </div>
    </div>
</div>
