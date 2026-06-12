/**
 * perfil.js - Lógica de edición de perfil
 */

$(function () {

    $('#form-perfil').on('submit', function (e) {
        e.preventDefault();

        if (!Validaciones.requerido('#perfil-nombre', 'El nombre')) return;
        if (!Validaciones.email('#perfil-email')) return;

        Ajax.post(window.ROUTES.perfil_actualizar, $(this).serialize())
            .done(function (res) {
                Swal.fire({ icon: 'success', title: '�xito', text: res.mensaje });
            });
    });
});
