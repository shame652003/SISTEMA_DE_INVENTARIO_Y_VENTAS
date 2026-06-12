/**
 * bitacora.js - Lógica del módulo de bitácora
 */

$(function () {

    function cargarBitacora() {
        Ajax.post(window.ROUTES.bitacora_listar)
            .done(function (res) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>';
                }
                $('#tabla-bitacora tbody').html(html);
            });
    }

    cargarBitacora();
});
