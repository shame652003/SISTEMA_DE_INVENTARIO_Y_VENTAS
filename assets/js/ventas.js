/**
 * ventas.js - LÃ³gica CRUD de ventas con modales
 */

$(function () {

    const modalVenta = new bootstrap.Modal('#modal-venta');

    function cargarVentas() {
        Ajax.post(window.ROUTES.ventas_listar)
            .done(function (res) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';
                }
                $('#tabla-ventas tbody').html(html);
            });
    }

    $('#btn-nuevo').on('click', function () {
        Validaciones.limpiarErrores('#form-venta');
        $('#form-venta')[0].reset();
    });

    // Agregar fila de producto
    $('#btn-agregar-producto').on('click', function () {
        const fila = $('.venta-producto-item').first().clone();
        fila.find('input').val('');
        fila.find('select').val('');
        $('#venta-productos').append(fila);
    });

    // Remover fila de producto
    $(document).on('click', '.btn-remover-producto', function () {
        if ($('.venta-producto-item').length > 1) {
            $(this).closest('.venta-producto-item').remove();
        }
    });

    $('#form-venta').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.validarFormulario('#form-venta')) return;

        Ajax.post(window.ROUTES.ventas_guardar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    modalVenta.hide();
                    cargarVentas();
                }
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje });
            });
    });

    cargarVentas();
});
