/**
 * usuarios.js - LÃ³gica CRUD de usuarios con modales
 */

$(function () {

    const modalUsuario = new bootstrap.Modal('#modal-usuario');

    function cargarUsuarios() {
        Ajax.post(window.ROUTES.usuarios_listar)
            .done(function (res) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';
                }
                // TODO: Renderizar filas con res.data
                $('#tabla-usuarios tbody').html(html);
            });
    }

    $('#btn-nuevo').on('click', function () {
        Validaciones.limpiarErrores('#form-usuario');
        $('#form-usuario')[0].reset();
        $('#usuario-id').val('');
        $('#usuario-password').attr('required', true);
    });

    $('#form-usuario').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.validarFormulario('#form-usuario')) return;

        Ajax.post(window.ROUTES.usuarios_guardar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    modalUsuario.hide();
                    cargarUsuarios();
                }
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje });
            });
    });

    cargarUsuarios();
});
