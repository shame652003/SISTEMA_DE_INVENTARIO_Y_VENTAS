<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people me-2"></i>Usuarios</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-usuario" id="btn-nuevo">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Usuario
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabla-usuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
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

<!-- Modal Usuario -->
<div class="modal fade" id="modal-usuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-usuario">
                <div class="modal-body">
                    <input type="hidden" name="id" id="usuario-id">
                    <div class="mb-3">
                        <label for="usuario-nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="usuario-nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="usuario-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="usuario-email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="usuario-password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="usuario-password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="usuario-rol" class="form-label">Rol</label>
                        <select class="form-select" id="usuario-rol" name="rol">
                            <option value="admin">Administrador</option>
                            <option value="vendedor">Vendedor</option>
                        </select>
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
