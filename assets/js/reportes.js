/**
 * reportes.js - Lógica de reportes de pagos y generales
 */
$(function () {

    var $tablaHistorial = $('#tabla-historial-pagos');
    var $tablaCreditos = $('#tabla-creditos');
    var $tablaPagosCliente = $('#tabla-pagos-cliente');
    var dtHistorial = null;
    var dtCreditos = null;
    var dtPagosCliente = null;
    var abonoModo = 'abonar';
    var cedulaClienteExpandida = null;
    var descargandoPdf = false;
    var mesSeleccionado = null;

    var modalVenta = new bootstrap.Modal(document.getElementById('modal-venta-detalle'));
    var modalAbono = new bootstrap.Modal(document.getElementById('modal-abonar-credito'));
    var modalAbonoDetalle = new bootstrap.Modal(document.getElementById('modal-abono-detalle'));

    var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    function hoyLocal() {
        var d = new Date();
        var year = d.getFullYear();
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function formatoMoneda(valor, moneda) {
        var num = parseFloat(valor) || 0;
        if (moneda === 'VES') return 'Bs. ' + num.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatoFecha(fechaStr) {
        var d = new Date(fechaStr + 'T00:00:00');
        if (isNaN(d.getTime())) return fechaStr;
        return d.getDate() + ' de ' + meses[d.getMonth()] + ' de ' + d.getFullYear();
    }

    function formatoHora(horaStr) {
        var partes = horaStr.split(':');
        var h = parseInt(partes[0], 10);
        var m = partes[1];
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ampm;
    }

    function formatoFechaHora(dtStr) {
        if (!dtStr) return '—';
        var partes = dtStr.split(' ');
        return formatoFecha(partes[0]) + ', ' + formatoHora(partes[1] || '00:00:00');
    }

    function badgeTipoPago(tipo) {
        var map = {
            'Efectivo': 'success',
            'Transferencia': 'info',
            'Punto': 'primary',
            'Biopago': 'secondary',
            'Credito': 'warning',
            'Zelle': 'dark',
            'Binance USDT': 'warning'
        };
        var color = map[tipo] || 'secondary';
        return '<span class="badge bg-' + color + '">' + tipo + '</span>';
    }

    function badgesMetodosPago(metodosStr) {
        if (!metodosStr) return '<span class="text-muted">—</span>';
        var metodos = metodosStr.split(', ');
        var html = '';
        for (var i = 0; i < metodos.length; i++) {
            html += badgeTipoPago(metodos[i]) + ' ';
        }
        return html;
    }

    function badgeEstadoCredito(creditoPagado, metodosPago) {
        if (!metodosPago || metodosPago.indexOf('Credito') === -1) return '<span class="text-muted">—</span>';
        if (creditoPagado === 1) return '<span class="badge bg-success">Pagado</span>';
        if (creditoPagado === 0) return '<span class="badge bg-danger">Pendiente</span>';
        return '<span class="text-muted">—</span>';
    }

    function llenarModalDetalle(res) {
        if (!res.ok) return;
        var d = res.data;
        var enc = d.encabezado;

        $('#modal-venta-id').text(enc.idVenta);
        $('#modal-venta-fecha').text(formatoFecha(enc.fecha) + ' ' + formatoHora(enc.hora));
        $('#modal-venta-cliente').text(enc.cliente);
        $('#modal-venta-total-usd').text(formatoMoneda(enc.total_usd, 'USD'));
        $('#modal-venta-total-ves').text(formatoMoneda(enc.total_ves, 'VES'));
        $('#modal-venta-total-bcv').text(formatoMoneda(enc.total_bcv, 'USD'));

        var prodTbody = $('#tabla-modal-productos tbody').empty();
        if (d.detalles && d.detalles.length > 0) {
            for (var i = 0; i < d.detalles.length; i++) {
                var dt = d.detalles[i];
                prodTbody.append(
                    '<tr>' +
                    '<td>' + dt.codigo + '</td>' +
                    '<td>' + dt.producto_nombre + '</td>' +
                    '<td class="text-center">' + parseFloat(dt.cantidad).toFixed(0) + '</td>' +
                    '<td class="text-end">' + formatoMoneda(dt.precio_unitario_usd, 'USD') + '</td>' +
                    '<td class="text-end">' + formatoMoneda(dt.precio_unitario_ves, 'VES') + '</td>' +
                    '<td class="text-end">' + formatoMoneda(dt.precio_unitario_bcv, 'USD') + '</td>' +
                    '<td class="text-end">' + formatoMoneda(dt.subtotal_usd, 'USD') + '</td>' +
                    '<td class="text-end">' + formatoMoneda(dt.subtotal_ves, 'VES') + '</td>' +
                    '<td class="text-end">' + formatoMoneda(dt.subtotal_bcv, 'USD') + '</td>' +
                    '</tr>'
                );
            }
        } else {
            prodTbody.append('<tr><td colspan="9" class="text-center text-muted">Sin productos</td></tr>');
        }

        var pagosTbody = $('#tabla-modal-pagos tbody').empty();
        if (d.pagos && d.pagos.length > 0) {
            for (var j = 0; j < d.pagos.length; j++) {
                var pg = d.pagos[j];
                pagosTbody.append(
                    '<tr>' +
                    '<td>' + badgeTipoPago(pg.tipoPago) + '</td>' +
                    '<td>' + pg.moneda + '</td>' +
                    '<td class="text-end">' + formatoMoneda(pg.monto_recibido, pg.moneda) + '</td>' +
                    '<td>' + (pg.referencia || '<span class="text-muted">—</span>') + '</td>' +
                    '</tr>'
                );
            }
        } else {
            pagosTbody.append('<tr><td colspan="4" class="text-center text-muted">Sin pagos</td></tr>');
        }

        $('#btn-pdf-venta').data('idventa', enc.idVenta);
        modalVenta.show();
    }

    var dtLangEs = {
        processing: 'Procesando...',
        search: 'Buscar:',
        lengthMenu: 'Mostrar _MENU_ registros',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
        infoFiltered: '(filtrado de _MAX_ registros totales)',
        infoPostFix: '',
        loadingRecords: 'Cargando...',
        zeroRecords: 'No se encontraron registros',
        emptyTable: 'No hay datos disponibles',
        paginate: {
            first: '<i class="bi bi-chevron-double-left"></i>',
            last: '<i class="bi bi-chevron-double-right"></i>',
            next: '<i class="bi bi-chevron-right"></i>',
            previous: '<i class="bi bi-chevron-left"></i>'
        },
        aria: {
            sortAscending: ': activar para ordenar ascendente',
            sortDescending: ': activar para ordenar descendente'
        }
    };

    var dtConfigBase = {
        language: dtLangEs,
        pageLength: 25,
        responsive: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
        dom: '<"row mb-2 align-items-center"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-2 align-items-center"<"col-sm-5"i><"col-sm-7"p>>',
        ordering: true
    };

    // ============================================================
    // VALIDACION DE FECHAS
    // ============================================================
    function validarFechas() {
        var hoy = hoyLocal();
        $('#fecha-inicio, #fecha-fin').attr('max', hoy);
    }

    $('#fecha-inicio, #fecha-fin').on('change', function () {
        var inicio = $('#fecha-inicio').val();
        var fin = $('#fecha-fin').val();
        var hoy = hoyLocal();

        if (inicio && inicio > hoy) {
            Swal.fire('Fecha no valida', 'La fecha de inicio no puede ser futura.', 'warning');
            $('#fecha-inicio').val(hoy);
            return;
        }
        if (fin && fin > hoy) {
            Swal.fire('Fecha no valida', 'La fecha de fin no puede ser futura.', 'warning');
            $('#fecha-fin').val(hoy);
            return;
        }
        if (inicio && fin && inicio > fin) {
            Swal.fire('Rango invalido', 'La fecha de inicio no puede ser mayor que la fecha de fin.', 'warning');
            $('#fecha-inicio').val('');
            $('#fecha-fin').val('');
        }
    });

    // ============================================================
    // SELECT2 - CLIENTES
    // ============================================================
    $('#select-cliente').select2({
        placeholder: 'Buscar cliente por cedula o nombre...',
        ajax: {
            url: window.BASE_URL + window.ROUTES.reportes_pagos_clientes_buscar,
            type: 'POST',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results || [] };
            },
            cache: true
        },
        minimumInputLength: 1,
        allowClear: true,
        language: {
            inputTooShort: function () { return 'Escriba al menos 1 caracter para buscar'; },
            searching: function () { return 'Buscando...'; },
            noResults: function () { return 'No se encontraron clientes'; }
        }
    });

    // ============================================================
    // SELECT2 - FECHAS DE VENTAS DEL MES ACTUAL
    // ============================================================
    $('#select-mes').select2({
        placeholder: 'Seleccione una fecha...',
        ajax: {
            url: window.BASE_URL + window.ROUTES.reportes_pagos_fechas_ventas,
            type: 'POST',
            dataType: 'json',
            delay: 200,
            data: function () { return {}; },
            processResults: function (data) {
                return { results: data.results || [] };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: false,
        language: {
            noResults: function () { return 'No hay fechas con ventas en el mes actual'; },
            searching: function () { return 'Cargando fechas...'; }
        }
    });

    $('#select-mes').on('select2:open', function () {
        var $input = $('.select2-search__field');
        $input.attr('placeholder', 'Buscar fecha...');
    });

    // Preseleccionar fecha de hoy en el Select2 cuando carguen los datos
    $(document).on('select2:select', '#select-mes', function () {
        $(this).removeClass('is-invalid');
    });

    // ============================================================
    // BOTON TOGGLE - FILTROS AVANZADOS
    // ============================================================
    $('#btn-toggle-filtros').on('click', function () {
        var $panel = $('#panel-filtros-avanzados');
        var $icon = $(this).find('i');
        if ($panel.hasClass('d-none')) {
            $panel.removeClass('d-none').hide().slideDown(300);
            $icon.addClass('bi-sliders2').removeClass('bi-sliders');
            $(this).addClass('active');
        } else {
            $panel.slideUp(300, function () {
                $panel.addClass('d-none');
            });
            $icon.addClass('bi-sliders').removeClass('bi-sliders2');
            $(this).removeClass('active');
        }
    });

    // ============================================================
    // BOTON LIMPIAR FILTROS
    // ============================================================
    function limpiarFiltros() {
        var hoy = hoyLocal();
        $('#select-mes').val(hoy).trigger('change');

        $('#metodo-pago').val('');
        $('#select-cliente').val('').trigger('change');
        $('#fecha-inicio').val('');
        $('#fecha-fin').val('');

        $('#select-mes').removeClass('is-invalid');

        var $panel = $('#panel-filtros-avanzados');
        if (!$panel.hasClass('d-none')) {
            $panel.slideUp(300, function () {
                $panel.addClass('d-none');
            });
            var $icon = $('#btn-toggle-filtros').find('i');
            $icon.addClass('bi-sliders').removeClass('bi-sliders2');
            $('#btn-toggle-filtros').removeClass('active');
        }

        cargarHistorial({
            fecha_inicio: hoy,
            fecha_fin: hoy,
            metodo_pago: '',
            cedula_cliente: ''
        });

        filtroActivo = 'todas';
        $('#filtros-rapidos .btn-filtro').removeClass('active');
        $('#filtros-rapidos .btn-filtro[data-filtro="todas"]').addClass('active');
    }

    $('#btn-limpiar-filtros').on('click', function () {
        limpiarFiltros();
    });

    // ============================================================
    // HISTORIAL DE PAGOS
    // ============================================================
    function cargarHistorial(data) {
        Ajax.post(window.ROUTES.reportes_pagos_generar, data)
            .done(function (res) {
                if (!res.ok) return;

                var rows = res.data || [];

                if (dtHistorial) {
                    $.fn.dataTable.ext.search.pop();
                    dtHistorial.destroy();
                    dtHistorial = null;
                }

                $tablaHistorial.find('tbody').empty();

                if (rows.length === 0) {
                    $tablaHistorial.find('tbody').html('<tr><td colspan="8" class="text-center text-muted py-4">No se encontraron pagos en el rango seleccionado</td></tr>');
                    $('#contador-pagos').hide();
                    $('#btn-pdf-historial').hide();
                    return;
                }

                for (var i = 0; i < rows.length; i++) {
                    var p = rows[i];
                    var esAbono = p.tipo_fila === 'abono';

                    if (esAbono) {
                        var monedaAbono = p.monedas || 'USD';
                        var esAbonoVes = monedaAbono === 'VES';
                        var totalVesAbono = esAbonoVes ? parseFloat(p.credito_monto_ves || p.monto_recibido) : 0;
                        var totalVesAbonoCol = esAbonoVes
                            ? 'Bs. ' + totalVesAbono.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                            : '<span class="text-muted">—</span>';

                        var row = '<tr class="fila-abono-historial fila-abono-click" data-idabono="' + p.idVenta + '" style="cursor:pointer;">' +
                            '<td><span class="badge bg-info">Abono a credito</span></td>' +
                            '<td data-sort="' + p.fecha + ' ' + p.hora + '">' + formatoFecha(p.fecha) + ' <small class="text-muted">' + formatoHora(p.hora) + '</small></td>' +
                            '<td>' + p.cliente + ' <small class="text-muted">(' + p.cedula_cliente + ')</small></td>' +
                            '<td>' + badgeTipoPago(p.metodos_pago) + '</td>' +
                            '<td class="text-end fw-bold" data-sort="' + parseFloat(p.total_bcv) + '">' + formatoMoneda(p.total_bcv, 'USD') + (esAbonoVes ? '' : ' <small class="text-muted">USD</small>') + '</td>' +
                            '<td class="text-end fw-bold" data-sort="' + totalVesAbono.toFixed(2) + '">' + totalVesAbonoCol + '</td>' +
                            '<td>' + (p.referencia || '<span class="text-muted">—</span>') + '</td>' +
                            '<td><small class="text-muted">' + p.vendedor + '</small></td>' +
                            '</tr>';
                        $tablaHistorial.find('tbody').append(row);
                        continue;
                    }

                    var creditoVes = parseFloat(p.credito_monto_ves) || 0;
                    var monedasStr = p.monedas || '';
                    var esVes = monedasStr.indexOf('VES') !== -1;
                    var totalVes = parseFloat(p.total_ves) || 0;

                    var totalBcvCol;
                    if (!esVes) {
                        totalBcvCol = formatoMoneda(p.total_usd, 'USD') + ' <small class="text-muted">USD</small>';
                    } else {
                        totalBcvCol = formatoMoneda(p.total_bcv, 'USD');
                    }

                    var totalVesCol;
                    if (!esVes) {
                        totalVesCol = '<span class="text-muted">—</span>';
                    } else if (creditoVes > 0) {
                        var pagadoVes = (totalVes - creditoVes).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        var creditoVesFmt = creditoVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        var txtEstado = p.credito_pagado === 0 ? 'pendiente' : 'pagado';
                        totalVesCol = 'Bs. ' + pagadoVes + ' pagado<br>'
                            + '<small class="text-muted">+ Bs. ' + creditoVesFmt + ' credito ' + txtEstado + '</small><br>'
                            + '<small>= Bs. ' + totalVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</small>';
                    } else {
                        totalVesCol = 'Bs. ' + totalVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }

                    var claseAdicional = '';
                    if (p.credito_pagado === 0) claseAdicional = ' fila-credito-pendiente';
                    else if (p.credito_pagado === 1) claseAdicional = ' fila-credito-pagado';

                    var row = '<tr class="fila-pago' + claseAdicional + '" data-idventa="' + p.idVenta + '" style="cursor:pointer;">' +
                        '<td><span class="fw-bold">#' + p.idVenta + '</span></td>' +
                        '<td data-sort="' + p.fecha + ' ' + p.hora + '">' + formatoFecha(p.fecha) + ' <small class="text-muted">' + formatoHora(p.hora) + '</small></td>' +
                        '<td>' + p.cliente + ' <small class="text-muted">(' + p.cedula_cliente + ')</small></td>' +
                        '<td>' + badgesMetodosPago(p.metodos_pago) + '</td>' +
                        '<td class="text-end fw-bold" data-sort="' + parseFloat(p.total_bcv) + '">' + totalBcvCol + '</td>' +
                        '<td class="text-end fw-bold" data-sort="' + totalVes.toFixed(2) + '">' + totalVesCol + '</td>' +
                        '<td>' + badgeEstadoCredito(p.credito_pagado, p.metodos_pago) + '</td>' +
                        '<td>' + p.vendedor + '</td>' +
                        '</tr>';
                    $tablaHistorial.find('tbody').append(row);
                }

                dtHistorial = $tablaHistorial.DataTable($.extend({}, dtConfigBase, {
                    order: [[1, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: [3, 6] }
                    ]
                }));

                $('#contador-num').text(rows.length);
                $('#contador-pagos').show();
                $('#btn-pdf-historial').show();

                aplicarFiltroRapido();
            });
    }

    // ============================================================
    // FILTROS RAPIDOS
    // ============================================================
    var filtroActivo = 'todas';

    function aplicarFiltroRapido() {
        if (!dtHistorial) return;
        $.fn.dataTable.ext.search.pop();
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (!dtHistorial) return true;
            var row = dtHistorial.row(dataIndex).node();
            if (!row) return true;

            if (filtroActivo === 'todas') return true;
            if (filtroActivo === 'abonos') return $(row).hasClass('fila-abono-historial');
            if (filtroActivo === 'creditos') return $(row).hasClass('fila-credito-pendiente') || $(row).hasClass('fila-credito-pagado');
            if (filtroActivo === 'finalizadas') return !$(row).hasClass('fila-credito-pendiente');
            return true;
        });
        if (dtHistorial) dtHistorial.draw();
    }

    $('#filtros-rapidos').on('click', '.btn-filtro', function () {
        filtroActivo = $(this).data('filtro');
        $('#filtros-rapidos .btn-filtro').removeClass('active');
        $(this).addClass('active');
        aplicarFiltroRapido();
    });

    $('#form-reporte-pagos').on('submit', function (e) {
        e.preventDefault();

        var fechaVal = $('#select-mes').val() || '';

        var inicio = fechaVal;
        var fin = fechaVal;

        var inicioCustom = $('#fecha-inicio').val();
        var finCustom = $('#fecha-fin').val();
        var panelAbierto = !$('#panel-filtros-avanzados').hasClass('d-none');

        if (panelAbierto && ((inicioCustom && !finCustom) || (!inicioCustom && finCustom))) {
            Swal.fire('Fechas requeridas', 'Debe seleccionar ambas fechas en los filtros avanzados.', 'warning');
            return;
        }

        var usarCustom = panelAbierto && inicioCustom && finCustom;

        if (usarCustom) {
            inicio = inicioCustom;
            fin = finCustom;
        }

        var hoy = hoyLocal();
        if (inicio && (inicio > hoy || (fin && fin > hoy))) {
            Swal.fire('Fecha no valida', 'No se pueden consultar fechas futuras.', 'warning');
            return;
        }
        if (inicio && fin && inicio > fin) {
            Swal.fire('Rango invalido', 'La fecha de inicio no puede ser mayor que la fecha de fin.', 'warning');
            return;
        }

        mesSeleccionado = fechaVal || null;

        cargarHistorial({
            fecha_inicio: inicio,
            fecha_fin: fin,
            metodo_pago: $('#metodo-pago').val(),
            cedula_cliente: $('#select-cliente').val()
        });
    });

    $tablaHistorial.on('click', 'tr.fila-abono-click', function () {
        var idAbono = $(this).data('idabono');
        if (!idAbono) return;

        Ajax.post(window.ROUTES.reportes_pagos_abono_detalle, { id: idAbono })
            .done(function (res) {
                if (!res.ok) return;
                var d = res.data;
                var a = d.abono;
                var esAbonoUsd = a.moneda === 'USD';

                $('#modal-abono-id').text(a.idPago);
                $('#modal-abono-cliente').text(a.cliente + ' (C.I. ' + a.cedula_cliente + ')');
                $('#modal-abono-fecha').text(formatoFecha(a.fecha) + ' ' + formatoHora(a.hora));
                $('#modal-abono-monto').text(esAbonoUsd ? formatoMoneda(a.monto_recibido, 'USD') + ' USD' : formatoMoneda(a.monto_bcv, 'USD'));
                $('#modal-abono-ref').text(a.referencia || '—');

                var tbody = $('#tabla-modal-abono-aplicados tbody').empty();
                var $thead = $('#tabla-modal-abono-aplicados thead');
                if (esAbonoUsd) {
                    $thead.find('th:last').text('Monto Aplicado USD');
                } else {
                    $thead.find('th:last').text('Monto Aplicado VES');
                }

                var tasaBcv = parseFloat(d.tasa_bcv) || 0;
                if (d.aplicados && d.aplicados.length > 0) {
                    for (var i = 0; i < d.aplicados.length; i++) {
                        var ap = d.aplicados[i];
                        var montoVes = tasaBcv > 0 ? parseFloat(ap.monto_aplicado_bcv) * tasaBcv : 0;
                        var colFinal = esAbonoUsd
                            ? '<td class="text-end">' + formatoMoneda(ap.monto_aplicado_usd, 'USD') + '</td>'
                            : '<td class="text-end">' + formatoMoneda(montoVes, 'VES') + '</td>';
                        tbody.append(
                            '<tr>' +
                            '<td><span class="fw-bold">Venta #' + ap.idVenta + '</span> ' +
                            '<small class="text-muted">' + ap.cliente_venta + '</small></td>' +
                            '<td>' + formatoFecha(ap.fecha_venta) + '</td>' +
                            '<td class="text-end">' + formatoMoneda(ap.monto_aplicado_bcv, 'USD') + '</td>' +
                            colFinal +
                            '</tr>'
                        );
                    }
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center text-muted">Sin detalle</td></tr>');
                }

                modalAbonoDetalle.show();
            });
    });

    $tablaHistorial.on('click', 'tr.fila-pago', function () {
        var idVenta = $(this).data('idventa');
        if (!idVenta) return;
        Ajax.post(window.ROUTES.reportes_pagos_venta_detalle, { id: idVenta }).done(llenarModalDetalle);
    });

    // ============================================================
    // CREDITOS PENDIENTES
    // ============================================================
    function cargarCreditos() {
        Ajax.post(window.ROUTES.reportes_pagos_creditos)
            .done(function (res) {
                if (!res.ok) return;

                var rows = res.data || [];

                if (dtCreditos) {
                    dtCreditos.destroy();
                    dtCreditos = null;
                }

                $tablaCreditos.find('tbody').empty();

                if (rows.length === 0) {
                    $tablaCreditos.find('tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">No hay clientes con créditos pendientes</td></tr>');
                    $('#btn-pdf-creditos').hide();
                    return;
                }

                for (var i = 0; i < rows.length; i++) {
                    var c = rows[i];
                    var row = '<tr class="fila-credito" data-cedula="' + c.cedula + '" data-nombre="' + c.cliente + '" style="cursor:pointer;">' +
                        '<td><span class="fw-bold">' + c.cedula + '</span></td>' +
                        '<td>' + c.cliente + '</td>' +
                        '<td class="text-end text-danger fw-bold">' + formatoMoneda(c.saldo_deudor_usd, 'USD') + (c.moneda_credito === 'USD' ? ' <small class="text-muted">USD</small>' : '') + '</td>' +
                        '<td class="text-end text-danger fw-bold">' + formatoMoneda(c.saldo_deudor_bcv, 'USD') + '</td>' +
                        '<td>' + formatoFechaHora(c.ultima_actualizacion) + '</td>' +
                        '<td class="text-center">' +
                            '<button class="btn btn-sm btn-success btn-abonar-credito me-1" ' +
                                'data-cedula="' + c.cedula + '" ' +
                                'data-nombre="' + c.cliente + '" title="Abonar">' +
                                '<i class="bi bi-cash"></i>' +
                            '</button>' +
                            '<button class="btn btn-sm btn-primary btn-pagar-todo" ' +
                                'data-cedula="' + c.cedula + '" ' +
                                'data-nombre="' + c.cliente + '" title="Pagar completo">' +
                                '<i class="bi bi-cash-stack"></i>' +
                            '</button>' +
                        '</td>' +
                        '</tr>';
                    $tablaCreditos.find('tbody').append(row);
                }

                dtCreditos = $tablaCreditos.DataTable($.extend({}, dtConfigBase, {
                    order: [[3, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: [5] }
                    ]
                }));
                $('#btn-pdf-creditos').show();
            });
    }

    $('#btn-cargar-creditos, #tab-creditos').on('click', function () {
        if ($('#panel-creditos').hasClass('active') && dtCreditos) return;
        cargarCreditos();
    });

    // Click en fila de crédito -> ver historial (excepto si click fue en botón Pagar)
    $tablaCreditos.on('click', 'tr.fila-credito', function (e) {
        if ($(e.target).closest('.btn-abonar-credito, .btn-pagar-todo').length) return;

        var cedula = $(this).data('cedula');
        var nombre = $(this).data('nombre');
        if (!cedula) return;

        $('#nombre-cliente-credito').text(nombre + ' (C.I. ' + cedula + ')');
        $('#card-pagos-cliente').removeClass('d-none');
        $('#credito-saldo-actual').hide();
        cedulaClienteExpandida = cedula;

        // Cargar saldo actual de crédito
        Ajax.post(window.ROUTES.reportes_pagos_saldo_credito, { cedula: cedula })
            .done(function (saldoRes) {
                if (saldoRes.ok && saldoRes.data) {
                    var s = saldoRes.data;
                    $('#credito-badge-usd').text('USD: ' + formatoMoneda(s.saldo_deudor_usd, 'USD'));
                    $('#credito-badge-bcv').text('BCV: ' + formatoMoneda(s.saldo_deudor_bcv, 'USD'));
                    $('#credito-saldo-actual').show();
                }
            });

        Ajax.post(window.ROUTES.reportes_pagos_cliente_detalle, { cedula: cedula })
            .done(function (res) {
                if (!res.ok) return;
                renderPagosCliente(res.data || []);
            });
    });

    function renderPagosCliente(rows) {
        if (dtPagosCliente) {
            dtPagosCliente.destroy();
            dtPagosCliente = null;
        }

        $tablaPagosCliente.find('tbody').empty();

        if (rows.length === 0) {
            $tablaPagosCliente.find('tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">Sin pagos registrados para este cliente</td></tr>');
            return;
        }

        var filaHtml = '';

        for (var i = 0; i < rows.length; i++) {
            var p = rows[i];
            var esAgrupado = !!p.metodos_pago;

            if (esAgrupado) {
                var tieneCredito = p.metodos_pago.indexOf('Credito') !== -1;
                var badgeCredito = tieneCredito ? ' <span class="badge bg-warning text-dark ms-1" title="Esta venta incluye crédito"><i class="bi bi-exclamation-triangle"></i> Crédito</span>' : '';
                filaHtml += '<tr class="fila-pago" data-idventa="' + p.idVenta + '" style="cursor:pointer;">' +
                    '<td><span class="fw-bold">#' + p.idVenta + '</span>' + badgeCredito + '</td>' +
                    '<td>' + formatoFecha(p.fecha) + ' <small class="text-muted">' + formatoHora(p.hora) + '</small></td>' +
                    '<td>' + badgesMetodosPago(p.metodos_pago) + '</td>' +
                    '<td class="text-end fw-bold">' + formatoMoneda(p.total_bcv, 'USD') + '</td>' +
                    '<td>' + badgeEstadoCredito(p.credito_pagado, p.metodos_pago) + '</td>' +
                    '<td><span class="text-muted">—</span></td>' +
                    '<td>' + p.vendedor + '</td>' +
                    '</tr>';
            } else {
                filaHtml += '<tr class="fila-abono" style="cursor:default;">' +
                    '<td><span class="badge bg-info">Abono a crédito</span> <small class="text-muted">' + formatoFecha(p.fecha) + '</small></td>' +
                    '<td>' + formatoFecha(p.fecha) + ' <small class="text-muted">' + formatoHora(p.hora) + '</small></td>' +
                    '<td>' + badgeTipoPago(p.tipoPago) + '</td>' +
                    '<td class="text-end fw-bold">' + formatoMoneda(p.monto_bcv, 'USD') + '</td>' +
                    '<td><span class="text-muted">—</span></td>' +
                    '<td>' + (p.referencia || '<span class="text-muted">—</span>') + '</td>' +
                    '<td>' + p.vendedor + '</td>' +
                    '</tr>';
            }
        }

        $tablaPagosCliente.find('tbody').append(filaHtml);

        dtPagosCliente = $tablaPagosCliente.DataTable($.extend({}, dtConfigBase, {
            order: [[1, 'desc'], [0, 'desc']]
        }));

        $('html, body').animate({ scrollTop: $('#card-pagos-cliente').offset().top - 80 }, 400);
    }

    $tablaPagosCliente.on('click', 'tr.fila-pago', function () {
        var idVenta = $(this).data('idventa');
        if (!idVenta) return;
        Ajax.post(window.ROUTES.reportes_pagos_venta_detalle, { id: idVenta }).done(llenarModalDetalle);
    });

    $('#btn-cerrar-cliente-detalle').on('click', function () {
        $('#card-pagos-cliente').addClass('d-none');
        if (dtPagosCliente) {
            dtPagosCliente.destroy();
            dtPagosCliente = null;
        }
        $('#credito-saldo-actual').hide();
        cedulaClienteExpandida = null;
        $tablaPagosCliente.find('tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">Sin pagos registrados</td></tr>');
        $('#nombre-cliente-credito').text('');
    });

    // ============================================================
    // MODAL ABONAR / PAGAR COMPLETO
    // ============================================================
    function abrirModalAbono($btn, modo) {
        abonoModo = modo;

        var cedula = $btn.data('cedula');
        var nombre = $btn.data('nombre');
        var vendedor = window.AUTH && window.AUTH.usuario ? window.AUTH.usuario.nombre + ' ' + (window.AUTH.usuario.apellido || '') : '—';

        $('#abono-cedula').val(cedula);
        $('#abono-cliente-nombre').text(nombre + ' (C.I. ' + cedula + ')');
        $('#abono-vendedor').text(vendedor);
        $('#abono-monto').val('');
        $('#abono-monto-ves').val('');
        $('#abono-monto-bcv').val('');
        $('#abono-referencia').val('');
        $('#abono-restante-usd').text('$0.00');
        $('#abono-restante-bcv').text('$0.00');
        $('#abono-restante-ves').text('Bs. 0,00');
        $('#abono-moneda-usd').prop('checked', true);
        mostrarColumnasMoneda('USD');

        if (modo === 'pagarTodo') {
            $('#abono-modal-header').removeClass('bg-success').addClass('bg-primary');
            $('#abono-modal-titulo').html('<i class="bi bi-cash-stack me-2"></i>Pagar Crédito Completo');
            $('#abono-monto-container').hide();
            $('#abono-btn-text').text('Confirmar Pago Total');
            $('#btn-confirmar-abono').removeClass('btn-success').addClass('btn-primary');
        } else {
            $('#abono-modal-header').removeClass('bg-primary').addClass('bg-success');
            $('#abono-modal-titulo').html('<i class="bi bi-cash-coin me-2"></i>Abonar a Crédito');
            $('#abono-monto-container').show();
            $('#abono-btn-text').text('Confirmar Abono');
            $('#btn-confirmar-abono').removeClass('btn-primary').addClass('btn-success');
        }

        Ajax.post(window.ROUTES.reportes_pagos_saldo_credito, { cedula: cedula })
            .done(function (res) {
                if (!res.ok) {
                    Swal.fire('Error', res.mensaje || 'No se pudo obtener el saldo.', 'error');
                    return;
                }

                var d = res.data;
                var tasa = parseFloat(d.tasa_bcv) || 0;
                var deudaUsd = parseFloat(d.saldo_deudor_usd) || 0;
                var deudaBcv = parseFloat(d.saldo_deudor_bcv) || 0;
                var deudaVes = deudaBcv * tasa;

                $('#abono-tasa-bcv').val(tasa);
                $('#abono-deuda-usd').text(formatoMoneda(deudaUsd, 'USD'));
                $('#abono-deuda-bcv').text(formatoMoneda(deudaBcv, 'USD'));
                $('#abono-deuda-ves').text(formatoMoneda(deudaVes, 'VES'));

                if (modo === 'pagarTodo') {
                    var monedaActual = $('input[name="abono-moneda"]:checked').val();
                    if (monedaActual === 'USD') {
                        $('#abono-monto').val(deudaUsd.toFixed(2));
                    } else {
                        $('#abono-monto-ves').val(Math.round(deudaVes * 100) / 100);
                        $('#abono-monto-bcv').val(deudaBcv.toFixed(2));
                    }
                    actualizarRestante();
                }

                actualizarMaxAbono();
            });

        // Cargar créditos pendientes por venta
        Ajax.post(window.ROUTES.reportes_pagos_creditos_detalle, { cedula: cedula })
            .done(function (res) {
                var $lista = $('#abono-creditos-lista').empty();
                if (!res.ok || !res.data || res.data.length === 0) {
                    $lista.html('<small class="text-muted">Sin creditos detallados</small>');
                    return;
                }
                var html = '<div class="list-group list-group-flush small">';
                var monedasUnicas = {};
                for (var i = 0; i < res.data.length; i++) {
                    var c = res.data[i];
                    monedasUnicas[c.moneda_credito] = true;
                    html += '<div class="list-group-item py-1 px-2 d-flex justify-content-between align-items-center">' +
                        '<span><strong>Venta #' + c.idVenta + '</strong> ' +
                        '<small class="text-muted">' + formatoFecha(c.fecha_venta) + '</small> ' +
                        '<span class="badge bg-info">' + c.moneda_credito + '</span></span>' +
                        '<span class="text-danger">' + formatoMoneda(c.saldo_pendiente_bcv, 'USD') + '</span>' +
                        '</div>';
                }
                html += '</div>';
                $lista.html(html);

                // Bloquear moneda si todos los creditos son de la misma
                var $usdBtn = $('#abono-moneda-usd');
                var $vesBtn = $('#abono-moneda-ves');
                $usdBtn.prop('disabled', false);
                $vesBtn.prop('disabled', false);
                $('label[for="abono-moneda-usd"], label[for="abono-moneda-ves"]').removeClass('opacity-50');

                if (monedasUnicas['USD'] && !monedasUnicas['VES']) {
                    $usdBtn.prop('checked', true);
                    $vesBtn.prop('disabled', true);
                    $('label[for="abono-moneda-ves"]').addClass('opacity-50');
                    mostrarColumnasMoneda('USD');
                    filtrarMetodosPago();
                } else if (monedasUnicas['VES'] && !monedasUnicas['USD']) {
                    $vesBtn.prop('checked', true);
                    $usdBtn.prop('disabled', true);
                    $('label[for="abono-moneda-usd"]').addClass('opacity-50');
                    mostrarColumnasMoneda('VES');
                    filtrarMetodosPago();
                }
            });

        $('#abono-metodo-group').empty();
        Ajax.post(window.ROUTES.reportes_pagos_tipos)
            .done(function (res) {
                if (!res.ok) return;
                var tipos = res.data || [];
                var colores = {
                    'Efectivo': 'success',
                    'Transferencia': 'info',
                    'Punto': 'primary',
                    'Biopago': 'dark',
                    'Zelle': 'secondary',
                    'Binance USDT': 'warning'
                };
                for (var i = 0; i < tipos.length; i++) {
                    if (tipos[i].tipoPago === 'Credito') continue;
                    var color = colores[tipos[i].tipoPago] || 'outline-secondary';
                    var $input = $('<input type="radio" class="btn-check" name="abono-metodo" ' +
                        'id="abono-m-' + tipos[i].idtipo_de_pagos + '" ' +
                        'value="' + tipos[i].idtipo_de_pagos + '" autocomplete="off">');
                    var $label = $('<label class="btn btn-outline-' + color + '" ' +
                        'for="abono-m-' + tipos[i].idtipo_de_pagos + '">' +
                        tipos[i].tipoPago + '</label>');
                    $('#abono-metodo-group').append($input).append($label);
                }
                filtrarMetodosPago();
            });

        modalAbono.show();
    }

    $tablaCreditos.on('click', '.btn-abonar-credito', function (e) {
        e.preventDefault();
        e.stopPropagation();
        abrirModalAbono($(this), 'abonar');
    });

    $tablaCreditos.on('click', '.btn-pagar-todo', function (e) {
        e.preventDefault();
        e.stopPropagation();
        abrirModalAbono($(this), 'pagarTodo');
    });

    function filtrarMetodosPago() {
        var moneda = $('input[name="abono-moneda"]:checked').val();
        if (moneda === 'USD') {
            $('#abono-metodo-group input').each(function () {
                var tipo = $('label[for="' + this.id + '"]').text();
                if (tipo === 'Efectivo' || tipo === 'Zelle' || tipo === 'Binance USDT') {
                    $(this).prop('disabled', false);
                    $('label[for="' + this.id + '"]').show();
                } else {
                    $(this).prop('checked', false);
                    $(this).prop('disabled', true);
                    $('label[for="' + this.id + '"]').hide();
                }
            });
        } else {
            $('#abono-metodo-group input').each(function () {
                var tipo = $('label[for="' + this.id + '"]').text();
                if (tipo === 'Zelle' || tipo === 'Binance USDT') {
                    $(this).prop('checked', false);
                    $(this).prop('disabled', true);
                    $('label[for="' + this.id + '"]').hide();
                } else {
                    $(this).prop('disabled', false);
                    $('label[for="' + this.id + '"]').show();
                }
            });
        }
    }

    function mostrarColumnasMoneda(moneda) {
        if (moneda === 'USD') {
            $('#abono-monto-usd-col').show();
            $('#abono-monto-ves-col').hide();
            $('#abono-monto-bcv-col').hide();
        } else {
            $('#abono-monto-usd-col').hide();
            $('#abono-monto-ves-col').show();
            $('#abono-monto-bcv-col').show();
        }
    }

    // Toggle moneda en modal de abono
    $('input[name="abono-moneda"]').on('change', function () {
        var moneda = $(this).val();
        mostrarColumnasMoneda(moneda);

        if (abonoModo === 'pagarTodo') {
            var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
            var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
            var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;
            if (moneda === 'USD') {
                $('#abono-monto').val(deudaUsd.toFixed(2));
            } else {
                var deudaVes = deudaBcv * tasa;
                $('#abono-monto-ves').val(Math.round(deudaVes * 100) / 100);
                $('#abono-monto-bcv').val(deudaBcv.toFixed(2));
            }
            actualizarRestante();
        } else {
            $('#abono-monto').val('');
            $('#abono-monto-ves').val('');
            $('#abono-monto-bcv').val('');
        }

        actualizarMaxAbono();
        filtrarMetodosPago();
    });

    function actualizarMaxAbono() {
        var moneda = $('input[name="abono-moneda"]:checked').val();
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
        var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;
        var deudaVesVal = deudaBcv * tasa;

        if (moneda === 'USD') {
            $('#abono-monto').attr('max', deudaUsd.toFixed(2));
            $('#abono-max-hint').text('Máximo disponible: ' + formatoMoneda(deudaUsd, 'USD'));
        } else {
            $('#abono-monto-ves').attr('max', Math.round(deudaVesVal * 100) / 100);
            $('#abono-monto-bcv').attr('max', deudaBcv.toFixed(2));
            $('#abono-max-hint-ves').text('Máximo: ' + formatoMoneda(deudaVesVal, 'VES'));
            $('#abono-max-hint-bcv').text('Máximo: ' + formatoMoneda(deudaBcv, 'USD'));
        }
    }

    function actualizarRestante() {
        var moneda = $('input[name="abono-moneda"]:checked').val();
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
        var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;

        var montoBcv;
        if (moneda === 'USD') {
            montoBcv = parseFloat($('#abono-monto').val()) || 0;
        } else {
            montoBcv = parseFloat($('#abono-monto-bcv').val()) || 0;
        }

        var restanteUsd = Math.max(0, deudaUsd - montoBcv);
        var restanteBcv = Math.max(0, deudaBcv - montoBcv);
        var restanteVes = restanteBcv * tasa;

        $('#abono-restante-usd').text(formatoMoneda(restanteUsd, 'USD'));
        $('#abono-restante-bcv').text(formatoMoneda(restanteBcv, 'USD'));
        $('#abono-restante-ves').text(formatoMoneda(restanteVes, 'VES'));
    }

    function validarMontoAbono($input, max, moneda) {
        var monto = parseFloat($input.val()) || 0;
        if (monto > max && max > 0) {
            $input.addClass('is-invalid');
            var $hint = $input.closest('.col-md-6').find('small');
            $hint.removeClass('text-muted').addClass('text-danger fw-bold')
                .text('¡Monto excedido! Máximo: ' + formatoMoneda(max, moneda));
            return false;
        } else {
            $input.removeClass('is-invalid');
            return true;
        }
    }

    // USD input
    $('#abono-monto').on('input', function () {
        var max = parseFloat($(this).attr('max')) || 0;
        if (validarMontoAbono($(this), max, 'USD')) {
            $('#abono-max-hint').removeClass('text-danger fw-bold').addClass('text-muted')
                .text('Máximo disponible: ' + formatoMoneda(max, 'USD'));
            actualizarRestante();
        }
    });

    // VES input → sync BCV
    $('#abono-monto-ves').on('input', function () {
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var ves = parseFloat($(this).val()) || 0;
        var bcv = tasa > 0 ? parseFloat((ves / tasa).toFixed(2)) : 0;
        $('#abono-monto-bcv').val(bcv);

        var max = parseFloat($(this).attr('max')) || 0;
        if (validarMontoAbono($(this), max, 'VES')) {
            $('#abono-max-hint-ves').removeClass('text-danger fw-bold').addClass('text-muted')
                .text('Máximo: ' + formatoMoneda(max, 'VES'));
            actualizarRestante();
        }
    });

    // BCV input → sync VES
    $('#abono-monto-bcv').on('input', function () {
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var bcv = parseFloat($(this).val()) || 0;
        var ves = parseFloat((bcv * tasa).toFixed(2));
        $('#abono-monto-ves').val(ves);

        var max = parseFloat($(this).attr('max')) || 0;
        if (validarMontoAbono($(this), max, 'USD')) {
            $('#abono-max-hint-bcv').removeClass('text-danger fw-bold').addClass('text-muted')
                .text('Máximo: ' + formatoMoneda(max, 'USD'));
            actualizarRestante();
        }
    });

    // Confirmar abono
    $('#btn-confirmar-abono').on('click', function () {
        var cedula = $('#abono-cedula').val();
        var idTipoPago = parseInt($('input[name="abono-metodo"]:checked').val()) || 0;
        var moneda = $('input[name="abono-moneda"]:checked').val();
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
        var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;

        var monto, montoBcv;
        if (moneda === 'USD') {
            monto = parseFloat($('#abono-monto').val()) || 0;
            montoBcv = monto;
        } else {
            monto = parseFloat($('#abono-monto-ves').val()) || 0;
            montoBcv = parseFloat($('#abono-monto-bcv').val()) || 0;
        }

        var referencia = $('#abono-referencia').val().trim();

        if (!idTipoPago) {
            Swal.fire('Método requerido', 'Seleccione un método de pago.', 'warning');
            return;
        }

        if (monto <= 0) {
            Swal.fire('Monto inválido', 'Ingrese un monto mayor a cero.', 'warning');
            return;
        }

        if (moneda === 'USD') {
            if (monto > deudaUsd + 0.01) {
                Swal.fire('Monto excedido', 'El monto en USD no puede superar la deuda (' + formatoMoneda(deudaUsd, 'USD') + ').', 'warning');
                return;
            }
        } else {
            var deudaVes = deudaBcv * tasa;
            if (monto > deudaVes + 1) {
                Swal.fire('Monto excedido', 'El monto en VES no puede superar la deuda equivalente (' + formatoMoneda(deudaVes, 'VES') + ').', 'warning');
                return;
            }
        }

        Swal.fire({
            title: abonoModo === 'pagarTodo' ? '¿Confirmar pago total?' : '¿Confirmar abono?',
            html: 'Se registrará un pago de <b>' + formatoMoneda(monto, moneda) + '</b><br>' +
                  'Método: <b>' + $('label[for="abono-m-' + idTipoPago + '"]').text() + '</b>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: abonoModo === 'pagarTodo' ? 'Sí, registrar pago total' : 'Sí, registrar abono',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: abonoModo === 'pagarTodo' ? '#0d6efd' : '#198754'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Ajax.post(window.ROUTES.reportes_pagos_abonar_credito, {
                cedula_cliente: cedula,
                idtipo_de_pagos: idTipoPago,
                moneda: moneda,
                monto_recibido: monto,
                monto_bcv: parseFloat(montoBcv.toFixed(2)),
                referencia: referencia || ''
            }).done(function (res) {
                if (res.ok) {
                    modalAbono.hide();
                    Swal.fire({
                        title: abonoModo === 'pagarTodo' ? 'Crédito pagado' : 'Abono registrado',
                        text: res.mensaje,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    cargarCreditos();

                    // Refrescar historial principal con filtros actuales
                    var refFecha = mesSeleccionado || hoy;

                    cargarHistorial({
                        fecha_inicio: refFecha,
                        fecha_fin: refFecha,
                        metodo_pago: $('#metodo-pago').val(),
                        cedula_cliente: $('#select-cliente').val()
                    });

                    // Refrescar detalle del cliente si está expandido
                    if (cedulaClienteExpandida && !$('#card-pagos-cliente').hasClass('d-none')) {
                        Ajax.post(window.ROUTES.reportes_pagos_cliente_detalle, { cedula: cedulaClienteExpandida })
                            .done(function (detRes) {
                                if (!detRes.ok) return;
                                renderPagosCliente(detRes.data || []);
                            });

                        Ajax.post(window.ROUTES.reportes_pagos_saldo_credito, { cedula: cedulaClienteExpandida })
                            .done(function (saldoRes) {
                                if (saldoRes.ok && saldoRes.data) {
                                    var s = saldoRes.data;
                                    $('#credito-badge-usd').text('USD: ' + formatoMoneda(s.saldo_deudor_usd, 'USD'));
                                    $('#credito-badge-bcv').text('BCV: ' + formatoMoneda(s.saldo_deudor_bcv, 'USD'));
                                    if (s.saldo_deudor_bcv > 0) {
                                        $('#credito-saldo-actual').show();
                                    } else {
                                        $('#credito-saldo-actual').hide();
                                    }
                                }
                            });
                    }
                } else {
                    Swal.fire('Error', res.mensaje, 'error');
                }
            });
        });
    });

    // Limpiar modal de abono al cerrar
    document.getElementById('modal-abonar-credito').addEventListener('hidden.bs.modal', function () {
        $('#abono-monto').val('');
        $('#abono-monto-ves').val('');
        $('#abono-monto-bcv').val('');
        $('#abono-referencia').val('');
        $('input[name="abono-metodo"]').prop('checked', false);
        abonoModo = 'abonar';
    });

    // ============================================================
    // AUTO-CARGA: ventas del dia actual
    // ============================================================
    validarFechas();
    var hoy = hoyLocal();
    mesSeleccionado = hoy;

    cargarHistorial({
        fecha_inicio: hoy,
        fecha_fin: hoy,
        metodo_pago: '',
        cedula_cliente: ''
    });

    $('#select-mes').on('change', function () {
        $(this).removeClass('is-invalid');
    });

    // ============================================================
    // REPORTES GENERALES
    // ============================================================
    $('#form-reporte-general').on('submit', function (e) {
        e.preventDefault();

        Ajax.post(window.ROUTES.reportes_generales_generar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    $('#resultado-reporte-general').html('<p class="text-success">Reporte generado correctamente.</p>');
                }
            });
    });

    // ============================================================
    // DESCARGAS PDF
    // ============================================================
    function descargarPdf(url, data, $btn) {
        if (descargandoPdf) return;
        descargandoPdf = true;

        Swal.fire({
            title: '¿Descargar PDF?',
            text: 'Se generará un archivo PDF con la información actual.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, descargar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ea580c'
        }).then(function (result) {
            if (!result.isConfirmed) {
                descargandoPdf = false;
                return;
            }

            if ($btn) $btn.prop('disabled', true);

            Swal.fire({
                title: 'Generando PDF',
                html: 'Por favor espere mientras se genera el archivo...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.BASE_URL + url, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                Swal.close();
                descargandoPdf = false;
                if ($btn) $btn.prop('disabled', false);

                if (xhr.status === 200) {
                    var blob = xhr.response;
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    var filename = 'documento.pdf';
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        filename = disposition.split('filename=')[1].replace(/"/g, '').trim();
                    }
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(link.href);
                } else {
                    Swal.fire('Error', 'No se pudo generar el PDF.', 'error');
                }
            };
            xhr.onerror = function () {
                Swal.close();
                descargandoPdf = false;
                if ($btn) $btn.prop('disabled', false);
                Swal.fire('Error', 'No se pudo generar el PDF.', 'error');
            };
            xhr.send($.param(data));
        });
    }

    $('#btn-pdf-historial').on('click', function () {
        var fechaRef = $('#select-mes').val() || hoyLocal();
        var inicio = fechaRef;
        var fin = fechaRef;

        var inicioCustom = $('#fecha-inicio').val();
        var finCustom = $('#fecha-fin').val();
        var usarCustom = !$('#panel-filtros-avanzados').hasClass('d-none') && inicioCustom && finCustom;
        if (usarCustom) {
            inicio = inicioCustom;
            fin = finCustom;
        }

        var data = {
            fecha_inicio: inicio,
            fecha_fin: fin,
            metodo_pago: $('#metodo-pago').val(),
            cedula_cliente: $('#select-cliente').val(),
            filtro: filtroActivo
        };
        descargarPdf(window.ROUTES.reportes_pagos_pdf_historial, data, $(this));
    });

    $('#btn-pdf-creditos').on('click', function () {
        descargarPdf(window.ROUTES.reportes_pagos_pdf_creditos, {}, $(this));
    });

    $('#btn-pdf-venta').on('click', function () {
        var idVenta = $(this).data('idventa');
        if (!idVenta) return;
        descargarPdf(window.ROUTES.reportes_pagos_pdf_venta, { id: idVenta }, $(this));
    });
});
