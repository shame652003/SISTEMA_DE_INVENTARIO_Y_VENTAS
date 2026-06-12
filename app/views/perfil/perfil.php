<div class="container-fluid p-4">
    <h2 class="mb-4"><i class="bi bi-person-circle me-2"></i>Mi Perfil</h2>

    <div class="card">
        <div class="card-body">
            <form id="form-perfil">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="perfil-nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="perfil-nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="perfil-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="perfil-email" name="email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="perfil-password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="perfil-password" name="password">
                        <small class="text-muted">Dejar vacío para no cambiar</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Actualizar Perfil
                </button>
            </form>
        </div>
    </div>
</div>
