/**
 * reportes.js - Lógica de reportes de pagos y generales
 */

$(function () {

    // Reportes de Pagos
    $('#form-reporte-pagos').on('submit', function (e) {
        e.preventDefault();

        Ajax.post(window.ROUTES.reportes_pagos_generar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    $('#resultado-reporte-pagos').html('<p class="text-success">Reporte generado correctamente.</p>');
                }
            });
    });

    // Reportes Generales
    $('#form-reporte-general').on('submit', function (e) {
        e.preventDefault();

        Ajax.post(window.ROUTES.reportes_generales_generar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    $('#resultado-reporte-general').html('<p class="text-success">Reporte generado correctamente.</p>');
                }
            });
    });
});
