/**
 * salidas.js - LÃ³gica de salidas de productos
 */

$(function () {

    const modalSalida = new bootstrap.Modal('#modal-salida');

    function cargarSalidas() {
        Ajax.post(window.ROUTES.salidas_listar)
            .done(function (res) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>';
                }
                $('#tabla-salidas tbody').html(html);
            });
    }

    $('#btn-nuevo').on('click', function () {
        Validaciones.limpiarErrores('#form-salida');
        $('#form-salida')[0].reset();
    });

    $('#form-salida').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.validarFormulario('#form-salida')) return;

        Ajax.post(window.ROUTES.salidas_guardar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    modalSalida.hide();
                    cargarSalidas();
                }
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje });
            });
    });

    cargarSalidas();
});
