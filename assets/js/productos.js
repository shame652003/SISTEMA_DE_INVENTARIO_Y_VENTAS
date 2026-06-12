/**
 * productos.js - LÃ³gica CRUD de productos con modales
 */

$(function () {

    const modalProducto = new bootstrap.Modal('#modal-producto');

    function cargarProductos() {
        Ajax.post(window.ROUTES.productos_listar)
            .done(function (res) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';
                }
                $('#tabla-productos tbody').html(html);
            });
    }

    $('#btn-nuevo').on('click', function () {
        Validaciones.limpiarErrores('#form-producto');
        $('#form-producto')[0].reset();
        $('#producto-id').val('');
    });

    $('#form-producto').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.validarFormulario('#form-producto')) return;

        Ajax.post(window.ROUTES.productos_guardar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    modalProducto.hide();
                    cargarProductos();
                }
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje });
            });
    });

    cargarProductos();
});
