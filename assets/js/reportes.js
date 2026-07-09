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

    var modalVenta = new bootstrap.Modal(document.getElementById('modal-venta-detalle'));
    var modalAbono = new bootstrap.Modal(document.getElementById('modal-abonar-credito'));

    var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

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

        modalVenta.show();
    }

    // ============================================================
    // VALIDACION DE FECHAS
    // ============================================================
    function validarFechas() {
        var hoy = new Date().toISOString().split('T')[0];
        $('#fecha-inicio, #fecha-fin').attr('max', hoy);
    }

    $('#fecha-inicio, #fecha-fin').on('change', function () {
        var inicio = $('#fecha-inicio').val();
        var fin = $('#fecha-fin').val();
        var hoy = new Date().toISOString().split('T')[0];

        if (inicio && inicio > hoy) {
            Swal.fire('Fecha no válida', 'La fecha de inicio no puede ser futura.', 'warning');
            $('#fecha-inicio').val(hoy);
            return;
        }
        if (fin && fin > hoy) {
            Swal.fire('Fecha no válida', 'La fecha de fin no puede ser futura.', 'warning');
            $('#fecha-fin').val(hoy);
            return;
        }
        if (inicio && fin && inicio > fin) {
            Swal.fire('Rango inválido', 'La fecha de inicio no puede ser mayor que la fecha de fin.', 'warning');
            $('#fecha-inicio').val('');
            $('#fecha-fin').val('');
        }
    });

    // ============================================================
    // SELECT2 - CLIENTES
    // ============================================================
    $('#select-cliente').select2({
        placeholder: 'Buscar cliente por cédula o nombre...',
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
            inputTooShort: function () { return 'Escriba al menos 1 carácter para buscar'; },
            searching: function () { return 'Buscando...'; },
            noResults: function () { return 'No se encontraron clientes'; }
        }
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
                    dtHistorial.destroy();
                    dtHistorial = null;
                }

                $tablaHistorial.find('tbody').empty();

                if (rows.length === 0) {
                    $tablaHistorial.find('tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">No se encontraron pagos en el rango seleccionado</td></tr>');
                    $('#contador-pagos').hide();
                    return;
                }

                for (var i = 0; i < rows.length; i++) {
                    var p = rows[i];
                    var row = '<tr class="fila-pago" data-idventa="' + p.idVenta + '" style="cursor:pointer;">' +
                        '<td><span class="fw-bold">#' + p.idVenta + '</span></td>' +
                        '<td>' + formatoFecha(p.fecha) + ' <small class="text-muted">' + formatoHora(p.hora) + '</small></td>' +
                        '<td>' + p.cliente + ' <small class="text-muted">(' + p.cedula_cliente + ')</small></td>' +
                        '<td>' + badgesMetodosPago(p.metodos_pago) + '</td>' +
                        '<td class="text-end fw-bold">' + formatoMoneda(p.total_bcv, 'USD') + '</td>' +
                        '<td>' + badgeEstadoCredito(p.credito_pagado, p.metodos_pago) + '</td>' +
                        '<td>' + p.vendedor + '</td>' +
                        '</tr>';
                    $tablaHistorial.find('tbody').append(row);
                }

                dtHistorial = $tablaHistorial.DataTable({
                    order: [[1, 'desc'], [0, 'desc']],
                    pageLength: 25,
                    responsive: true,
                    columnDefs: [
                        { orderable: false, targets: [3, 5] }
                    ]
                });

                $('#contador-pagos').text(rows.length + ' ventas encontradas').show();
            });
    }

    $('#form-reporte-pagos').on('submit', function (e) {
        e.preventDefault();

        var inicio = $('#fecha-inicio').val();
        var fin = $('#fecha-fin').val();
        var hoy = new Date().toISOString().split('T')[0];

        if (!inicio || !fin) {
            Swal.fire('Fechas requeridas', 'Debe seleccionar ambas fechas.', 'warning');
            return;
        }
        if (inicio > hoy || fin > hoy) {
            Swal.fire('Fecha no válida', 'No se pueden consultar fechas futuras.', 'warning');
            return;
        }
        if (inicio > fin) {
            Swal.fire('Rango inválido', 'La fecha de inicio no puede ser mayor que la fecha de fin.', 'warning');
            return;
        }

        cargarHistorial($(this).serialize());
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
                    return;
                }

                for (var i = 0; i < rows.length; i++) {
                    var c = rows[i];
                    var row = '<tr class="fila-credito" data-cedula="' + c.cedula + '" data-nombre="' + c.cliente + '" style="cursor:pointer;">' +
                        '<td><span class="fw-bold">' + c.cedula + '</span></td>' +
                        '<td>' + c.cliente + '</td>' +
                        '<td class="text-end text-danger fw-bold">' + formatoMoneda(c.saldo_deudor_usd, 'USD') + '</td>' +
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

                dtCreditos = $tablaCreditos.DataTable({
                    order: [[3, 'desc']],
                    pageLength: 25,
                    responsive: true,
                    columnDefs: [
                        { orderable: false, targets: [5] }
                    ]
                });
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

        dtPagosCliente = $tablaPagosCliente.DataTable({
            order: [[1, 'desc'], [0, 'desc']],
            pageLength: 25,
            responsive: true
        });

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
        $('#abono-referencia').val('');
        $('#abono-restante-usd').text('$0.00');
        $('#abono-restante-bcv').text('$0.00');
        $('#abono-restante-ves').text('Bs. 0,00');
        $('#abono-moneda-usd').prop('checked', true);
        $('#abono-simbolo').text('$');

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
                        $('#abono-monto').val(Math.round(deudaVes * 100) / 100);
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
                    $lista.html('<small class="text-muted">Sin créditos detallados</small>');
                    return;
                }
                var html = '<div class="list-group list-group-flush small">';
                for (var i = 0; i < res.data.length; i++) {
                    var c = res.data[i];
                    html += '<div class="list-group-item py-1 px-2 d-flex justify-content-between align-items-center">' +
                        '<span><strong>Venta #' + c.idVenta + '</strong> ' +
                        '<small class="text-muted">' + formatoFecha(c.fecha_venta) + '</small></span>' +
                        '<span class="text-danger">' + formatoMoneda(c.saldo_pendiente_bcv, 'USD') + '</span>' +
                        '</div>';
                }
                html += '</div>';
                $lista.html(html);
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

    // Toggle moneda en modal de abono
    $('input[name="abono-moneda"]').on('change', function () {
        var moneda = $(this).val();
        $('#abono-simbolo').text(moneda === 'USD' ? '$' : 'Bs.');

        if (abonoModo === 'pagarTodo') {
            var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
            var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
            var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;
            if (moneda === 'USD') {
                $('#abono-monto').val(deudaUsd.toFixed(2));
            } else {
                $('#abono-monto').val(Math.round(deudaBcv * tasa * 100) / 100);
            }
            actualizarRestante();
        } else {
            $('#abono-monto').val('');
        }

        actualizarMaxAbono();
        filtrarMetodosPago();
    });

    function actualizarMaxAbono() {
        var moneda = $('input[name="abono-moneda"]:checked').val();
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
        var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;

        if (moneda === 'USD') {
            $('#abono-monto').attr('max', deudaUsd.toFixed(2));
            $('#abono-max-hint').text('Máximo disponible: ' + formatoMoneda(deudaUsd, 'USD'));
        } else {
            var deudaVes = deudaBcv * tasa;
            $('#abono-monto').attr('max', Math.round(deudaVes * 100) / 100);
            $('#abono-max-hint').text('Máximo disponible: ' + formatoMoneda(deudaVes, 'VES'));
        }
    }

    function actualizarRestante() {
        var monto = parseFloat($('#abono-monto').val()) || 0;
        var moneda = $('input[name="abono-moneda"]:checked').val();
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
        var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;

        var montoBcv;
        if (moneda === 'USD') {
            montoBcv = monto;
        } else {
            montoBcv = tasa > 0 ? monto / tasa : 0;
        }

        var restanteUsd = Math.max(0, deudaUsd - montoBcv);
        var restanteBcv = Math.max(0, deudaBcv - montoBcv);
        var restanteVes = restanteBcv * tasa;

        $('#abono-restante-usd').text(formatoMoneda(restanteUsd, 'USD'));
        $('#abono-restante-bcv').text(formatoMoneda(restanteBcv, 'USD'));
        $('#abono-restante-ves').text(formatoMoneda(restanteVes, 'VES'));
    }

    // Cálculo en tiempo real del restante con validación visual
    $('#abono-monto').on('input', function () {
        var monto = parseFloat($(this).val()) || 0;
        var max = parseFloat($(this).attr('max')) || 0;
        var moneda = $('input[name="abono-moneda"]:checked').val();

        if (monto > max && max > 0) {
            $(this).addClass('is-invalid');
            $('#abono-max-hint').removeClass('text-muted').addClass('text-danger fw-bold')
                .text('¡Monto excedido! Máximo: ' + formatoMoneda(max, moneda));
        } else {
            $(this).removeClass('is-invalid');
            $('#abono-max-hint').removeClass('text-danger fw-bold').addClass('text-muted')
                .text('Máximo disponible: ' + formatoMoneda(max, moneda));
            actualizarRestante();
        }
    });

    // Confirmar abono
    $('#btn-confirmar-abono').on('click', function () {
        var cedula = $('#abono-cedula').val();
        var idTipoPago = parseInt($('input[name="abono-metodo"]:checked').val()) || 0;
        var moneda = $('input[name="abono-moneda"]:checked').val();
        var monto = parseFloat($('#abono-monto').val()) || 0;
        var referencia = $('#abono-referencia').val().trim();
        var tasa = parseFloat($('#abono-tasa-bcv').val()) || 0;
        var deudaUsd = parseFloat($('#abono-deuda-usd').text().replace(/[^0-9.,-]/g, '')) || 0;
        var deudaBcv = parseFloat($('#abono-deuda-bcv').text().replace(/[^0-9.,-]/g, '')) || 0;

        if (!idTipoPago) {
            Swal.fire('Método requerido', 'Seleccione un método de pago.', 'warning');
            return;
        }

        if (monto <= 0) {
            Swal.fire('Monto inválido', 'Ingrese un monto mayor a cero.', 'warning');
            return;
        }

        var montoBcv;
        if (moneda === 'USD') {
            montoBcv = monto;
            if (monto > deudaUsd + 0.01) {
                Swal.fire('Monto excedido', 'El monto en USD no puede superar la deuda (' + formatoMoneda(deudaUsd, 'USD') + ').', 'warning');
                return;
            }
        } else {
            montoBcv = tasa > 0 ? monto / tasa : 0;
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
                    cargarHistorial({
                        fecha_inicio: $('#fecha-inicio').val(),
                        fecha_fin: $('#fecha-fin').val(),
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
        $('#abono-referencia').val('');
        $('input[name="abono-metodo"]').prop('checked', false);
        abonoModo = 'abonar';
    });

    // ============================================================
    // AUTO-CARGA: ventas del día actual
    // ============================================================
    validarFechas();
    var hoy = new Date().toISOString().split('T')[0];
    $('#fecha-inicio').val(hoy);
    $('#fecha-fin').val(hoy);

    cargarHistorial({
        fecha_inicio: hoy,
        fecha_fin: hoy,
        metodo_pago: '',
        cedula_cliente: ''
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
});
