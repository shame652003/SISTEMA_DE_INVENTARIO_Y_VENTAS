/**
 * dashboard.js - Lógica del panel principal
 */

$(function () {

    // Cargar estadísticas
    function cargarEstadisticas() {
        Ajax.get('/dashboard')
            .done(function (res) {
                // Actualizar counters del dashboard
            });
    }

    cargarEstadisticas();

    // ─── Submit de Taza BCV ───
    $('#form-taza-bcv').on('submit', function (e) {
        e.preventDefault();
        Swal.fire({
            icon: 'success',
            title: 'Taza actualizada',
            text: 'La taza BCV se ha actualizado correctamente.',
            timer: 1500,
            showConfirmButton: false
        });
        bootstrap.Modal.getInstance(document.getElementById('modal-taza-bcv')).hide();
    });

    // ─── Submit de Margen Dólar ───
    $('#form-margen-dolar').on('submit', function (e) {
        e.preventDefault();
        Swal.fire({
            icon: 'success',
            title: 'Margen guardado',
            text: 'El margen de ganancia en USD se ha actualizado.',
            timer: 1500,
            showConfirmButton: false
        });
        bootstrap.Modal.getInstance(document.getElementById('modal-margen-dolar')).hide();
    });

    // ─── Submit de Margen Bolívar ───
    $('#form-margen-bolivar').on('submit', function (e) {
        e.preventDefault();
        Swal.fire({
            icon: 'success',
            title: 'Margen guardado',
            text: 'El margen de ganancia en VES se ha actualizado.',
            timer: 1500,
            showConfirmButton: false
        });
        bootstrap.Modal.getInstance(document.getElementById('modal-margen-bolivar')).hide();
    });

});