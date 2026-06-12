/**
 * entradas.js - LÃ³gica de entradas de productos
 */

$(function () {

    const modalEntrada = new bootstrap.Modal('#modal-entrada');

    function cargarEntradas() {
        Ajax.post(window.ROUTES.entradas_listar)
            .done(function (res) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>';
                }
                $('#tabla-entradas tbody').html(html);
            });
    }

    $('#btn-nuevo').on('click', function () {
        Validaciones.limpiarErrores('#form-entrada');
        $('#form-entrada')[0].reset();
    });

    $('#form-entrada').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.validarFormulario('#form-entrada')) return;

        Ajax.post(window.ROUTES.entradas_guardar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    modalEntrada.hide();
                    cargarEntradas();
                }
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje });
            });
    });

    cargarEntradas();
});
