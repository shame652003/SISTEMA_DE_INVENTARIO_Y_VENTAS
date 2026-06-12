/**
 * clientes.js - LÃ³gica CRUD de clientes con modales
 */

$(function () {

    const modalCliente = new bootstrap.Modal('#modal-cliente');

    function cargarClientes() {
        Ajax.post(window.ROUTES.clientes_listar)
            .done(function (res) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';
                }
                $('#tabla-clientes tbody').html(html);
            });
    }

    $('#btn-nuevo').on('click', function () {
        Validaciones.limpiarErrores('#form-cliente');
        $('#form-cliente')[0].reset();
        $('#cliente-id').val('');
    });

    $('#form-cliente').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.validarFormulario('#form-cliente')) return;

        Ajax.post(window.ROUTES.clientes_guardar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    modalCliente.hide();
                    cargarClientes();
                }
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje });
            });
    });

    cargarClientes();
});
