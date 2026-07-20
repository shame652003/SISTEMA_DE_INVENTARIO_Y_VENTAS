/**
 * dashboard.js - Logica del panel principal
 */
$(function () {

    var charts = {};

    var MESES_ES = [
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
    ];

    function formatearFechaEs(fechaStr) {
        if (!fechaStr) return '';
        var partes = fechaStr.split('-');
        if (partes.length !== 3) return fechaStr;
        var dia = parseInt(partes[2], 10);
        var mes = MESES_ES[parseInt(partes[1], 10) - 1];
        var anio = partes[0];
        return dia + ' de ' + mes + ' de ' + anio;
    }

    function initCharts() {
        cargarClientesMasFrecuentes();
        cargarCreditosAntiguos();
        cargarProductosMasVendidos();
        cargarPagosPorTipo();
        cargarVentasPorTipoProducto();
    }

    function opcionesComunes(categoria, tipo) {
        var cfg = {
            chart: {
                type: tipo || 'bar',
                height: 350,
                fontFamily: 'inherit',
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 3 },
            xaxis: {
                type: categoria || 'category',
                labels: { style: { fontSize: '12px' } }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val;
                    }
                }
            },
            tooltip: { theme: 'light' },
            noData: { text: 'Sin datos disponibles', align: 'center', verticalAlign: 'middle', style: { fontSize: '14px', color: '#888' } }
        };
        return cfg;
    }

    function renderChart(selector, opts) {
        if (charts[selector]) {
            charts[selector].destroy();
        }
        var el = document.querySelector(selector);
        if (!el) return;
        charts[selector] = new ApexCharts(el, opts);
        charts[selector].render();
    }

    // ─── Clientes Mas Frecuentes (barra horizontal: cantidad de ventas) ───
    function cargarClientesMasFrecuentes() {
        Ajax.post(window.ROUTES.dashboard_graficas, { tipo: 'clientes_mas_frecuentes' })
            .done(function (res) {
                if (!res.ok) return;
                var data = res.data || [];
                var nombres = [];
                var cantidades = [];
                data.slice(0, 10).forEach(function (d) {
                    var nombre = d.cliente || '';
                    nombres.push(nombre.length > 22 ? nombre.substring(0, 22) + '...' : nombre);
                    cantidades.push(parseInt(d.cantidad_ventas) || 0);
                });

                var opts = opcionesComunes('category', 'bar');
                opts.series = [{ name: 'Cantidad de Compras', data: cantidades }];
                opts.colors = ['#dc3545'];
                opts.plotOptions = { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } };
                opts.xaxis = { categories: nombres.reverse(), labels: { style: { fontSize: '12px' } } };
                opts.dataLabels = {
                    enabled: true,
                    formatter: function (v) { return v + ' compras'; },
                    style: { fontSize: '10px' }
                };
                opts.legend = { show: false };
                opts.tooltip = { y: { formatter: function (v) { return v + ' compras'; } } };
                cantidades.reverse();

                renderChart('#chart-clientes-frecuentes', opts);
            });
    }

    // ─── Creditos Mas Antiguos (barra horizontal: saldo pendiente BCV) ───
    function cargarCreditosAntiguos() {
        Ajax.post(window.ROUTES.dashboard_graficas, { tipo: 'creditos_antiguos' })
            .done(function (res) {
                if (!res.ok) return;
                var data = res.data || [];
                var labels = [];
                var saldos = [];
                data.slice(0, 10).forEach(function (d) {
                    var nombre = d.cliente || '';
                    var fechaEs = formatearFechaEs(d.fecha_creacion ? d.fecha_creacion.substring(0, 10) : d.fecha_venta);
                    labels.push(nombre.length > 18 ? nombre.substring(0, 18) + '...' : nombre);
                    saldos.push({
                        x: nombre.length > 18 ? nombre.substring(0, 18) + '...' : nombre,
                        y: parseFloat(d.saldo_pendiente_bcv) || 0,
                        cliente: d.cliente,
                        fecha: fechaEs,
                        saldo: parseFloat(d.saldo_pendiente_bcv) || 0
                    });
                });

                var opts = opcionesComunes('category', 'bar');
                opts.series = [{ name: 'Saldo Pendiente (Bs.)', data: saldos }];
                opts.colors = ['#fd7e14'];
                opts.plotOptions = { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } };
                opts.xaxis = { categories: labels.reverse(), labels: { style: { fontSize: '12px' } } };
                opts.dataLabels = {
                    enabled: true,
                    formatter: function (v) { return 'Bs.' + v.toFixed(0); },
                    style: { fontSize: '10px' }
                };
                opts.legend = { show: false };
                opts.tooltip = {
                    y: {
                        formatter: function (v, opt) {
                            var pt = opt.w.config.series[0].data[opt.dataPointIndex];
                            return 'Bs.' + pt.saldo.toFixed(2) + ' | Desde: ' + pt.fecha;
                        }
                    }
                };
                saldos.reverse();

                renderChart('#chart-creditos-antiguos', opts);
            });
    }

    // ─── Productos Mas Vendidos (barra horizontal: cantidad de unidades) ───
    function cargarProductosMasVendidos() {
        Ajax.post(window.ROUTES.dashboard_graficas, { tipo: 'productos_mas_vendidos' })
            .done(function (res) {
                if (!res.ok) return;
                var data = res.data || [];
                var nombres = [];
                var cantidades = [];
                data.slice(0, 10).forEach(function (d) {
                    nombres.push(d.nombre.length > 20 ? d.nombre.substring(0, 20) + '...' : d.nombre);
                    cantidades.push(parseFloat(d.unidades_vendidas) || 0);
                });

                var opts = opcionesComunes('category', 'bar');
                opts.series = [{ name: 'Unidades Vendidas', data: cantidades }];
                opts.colors = ['#20c997'];
                opts.plotOptions = { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } };
                opts.xaxis = { categories: nombres.reverse(), labels: { style: { fontSize: '12px' } } };
                opts.dataLabels = {
                    enabled: true,
                    formatter: function (v) { return v + ' unds.'; },
                    style: { fontSize: '10px' }
                };
                opts.legend = { show: false };
                opts.tooltip = { y: { formatter: function (v) { return v + ' unidades vendidas'; } } };
                cantidades.reverse();

                renderChart('#chart-productos-mas-vendidos', opts);
            });
    }

    // ─── Tipos de Pago Mas Usados (dona: cantidad de pagos = frecuencia) ───
    function cargarPagosPorTipo() {
        Ajax.post(window.ROUTES.dashboard_graficas, { tipo: 'pagos_por_tipo' })
            .done(function (res) {
                if (!res.ok) return;
                var data = res.data || [];
                var labels = [];
                var series = [];
                data.forEach(function (d) {
                    labels.push(d.tipoPago);
                    series.push(parseInt(d.cantidad_pagos) || 0);
                });

                var opts = opcionesComunes(null, 'donut');
                opts.series = series;
                opts.labels = labels;
                opts.colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'];
                opts.plotOptions = {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Pagos',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                                    }
                                }
                            }
                        }
                    }
                };
                opts.legend = { position: 'bottom', fontSize: '12px' };
                opts.tooltip = {
                    y: {
                        formatter: function (v) {
                            var total = series.reduce(function (a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((v / total) * 100).toFixed(1) : 0;
                            return v + ' pagos (' + pct + '%)';
                        }
                    }
                };
                opts.dataLabels = {
                    enabled: true,
                    formatter: function (val, opts) {
                        return opts.w.config.series[opts.seriesIndex];
                    },
                    style: { fontSize: '12px' }
                };

                renderChart('#chart-pagos-por-tipo', opts);
            });
    }

    // ─── Ventas por Tipo de Producto (barra: BCV + unidades) ───
    function cargarVentasPorTipoProducto() {
        Ajax.post(window.ROUTES.dashboard_graficas, { tipo: 'ventas_por_tipo_producto' })
            .done(function (res) {
                if (!res.ok) return;
                var data = res.data || [];
                var categorias = [];
                var bcv = [];
                var unidades = [];
                data.forEach(function (d) {
                    categorias.push(d.tipo_producto);
                    bcv.push(parseFloat(d.venta_total_bcv) || 0);
                    unidades.push(parseFloat(d.unidades_vendidas) || 0);
                });

                var opts = opcionesComunes('category', 'bar');
                opts.series = [
                    { name: 'Total BCV ($)', data: bcv },
                    { name: 'Unidades', data: unidades }
                ];
                opts.colors = ['#0d6efd', '#adb5bd'];
                opts.plotOptions = { bar: { borderRadius: 4, columnWidth: '50%', dataLabels: { position: 'top' } } };
                opts.xaxis = { categories: categorias, labels: { style: { fontSize: '12px' } } };
                opts.stroke = { show: true, width: 1, colors: ['transparent'] };
                opts.legend = { position: 'bottom' };
                opts.tooltip = {
                    shared: true,
                    intersect: false,
                    y: [
                        { formatter: function (v) { return '$' + v.toFixed(2) + ' BCV'; } },
                        { formatter: function (v) { return v + ' unds.'; } }
                    ]
                };

                renderChart('#chart-ventas-por-tipo-producto', opts);
            });
    }

    // ─── Cargar ultimas ventas (BCV + VES) ───
    function cargarUltimasVentas() {
        Ajax.post(window.ROUTES.dashboard_ultimas_ventas)
            .done(function (res) {
                if (!res.ok) return;
                var data = res.data || [];
                var tbody = $('#tabla-ultimas-ventas tbody');
                tbody.empty();

                if (data.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Sin ventas recientes</td></tr>');
                    return;
                }

                data.forEach(function (v) {
                    var fechaEs = formatearFechaEs(v.fecha);
                    var row = '<tr>' +
                        '<td class="fw-semibold">#' + v.idVenta + '</td>' +
                        '<td>' + escapeHtml(v.cliente) + '</td>' +
                        '<td>' + escapeHtml(v.vendedor) + '</td>' +
                        '<td class="text-primary">$' + parseFloat(v.total_bcv).toLocaleString('en-US', { minimumFractionDigits: 2 }) + '</td>' +
                        '<td class="text-warning">Bs.' + parseFloat(v.total_ves).toLocaleString('en-US', { minimumFractionDigits: 2 }) + '</td>' +
                        '<td class="text-muted small">' + fechaEs + ' ' + v.hora.substring(0, 5) + '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
            })
            .fail(function () {
                $('#tabla-ultimas-ventas tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">Error al cargar ventas</td></tr>');
            });
    }

    function escapeHtml(str) {
        return $('<span>').text(str || '').html();
    }

    // ─── Submit: Tasa BCV ───
    $('#form-taza-bcv').on('submit', function (e) {
        e.preventDefault();
        var tasa = $(this).find('input[name="tasa"]').val();
        var fecha = $(this).find('input[name="fecha_vigencia"]').val();
        var observacion = $(this).find('textarea[name="observacion"]').val();

        if (!tasa || parseFloat(tasa) <= 0) {
            Swal.fire({ icon: 'warning', title: 'Dato invalido', text: 'Ingrese una tasa mayor a 0.' });
            return;
        }

        Ajax.post(window.ROUTES.dashboard_bcv_actualizar, {
            tasa: tasa,
            fecha: fecha,
            observacion: observacion
        }).done(function (res) {
            if (res.ok) {
                var t = res.tasa;
                $('#bcv-rate-text').text('Tasa BCV: 1 USD = ' + parseFloat(t.tasa_ves_por_usd).toLocaleString('en-US', { minimumFractionDigits: 4 }) + ' Bs');
                $('#bcv-rate-date').text('(' + formatearFechaEs(t.fecha_tasa) + ')');
                $('#input-tasa-bcv').val(t.tasa_ves_por_usd);
                $('#form-taza-bcv textarea[name="observacion"]').val('');
                Swal.fire({ icon: 'success', title: 'Tasa actualizada', text: res.mensaje, timer: 2000, showConfirmButton: false });
                bootstrap.Modal.getInstance(document.getElementById('modal-taza-bcv')).hide();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
            }
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al actualizar la tasa BCV.' });
        });
    });

    // ─── Consultar BCV desde API ───
    $('#btn-consultar-api').on('click', function () {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Consultando...');

        Ajax.post(window.ROUTES.dashboard_bcv_consultar_api)
            .done(function (res) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-download me-1"></i> Consultar tasa desde API (ve.dolarapi.com)');

                if (res.ok) {
                    $('#input-tasa-bcv').val(res.tasa);
                    $('#input-fecha-bcv').val(res.fecha);
                    Swal.fire({
                        icon: 'success',
                        title: 'Tasa obtenida',
                        text: 'Tasa: ' + res.tasa + ' Bs/USD (Fecha: ' + formatearFechaEs(res.fecha) + ')',
                        timer: 2500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'warning', title: 'API no disponible', text: res.mensaje });
                }
            })
            .fail(function () {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-download me-1"></i> Consultar tasa desde API (ve.dolarapi.com)');
                Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo consultar la API. Verifique su conexion a internet.' });
            });
    });

    // ─── Submit: Margen USD ───
    $('#form-margen-dolar').on('submit', function (e) {
        e.preventDefault();
        var porcentaje = $(this).find('input[name="porcentaje"]').val();
        var observacion = $(this).find('textarea[name="observacion"]').val();

        if (!porcentaje || parseFloat(porcentaje) < 0) {
            Swal.fire({ icon: 'warning', title: 'Dato invalido', text: 'Ingrese un porcentaje valido.' });
            return;
        }

        Ajax.post(window.ROUTES.dashboard_margen_actualizar, {
            tipo: 'USD',
            porcentaje: porcentaje,
            observacion: observacion
        }).done(function (res) {
            if (res.ok) {
                var m = res.margen;
                $('#input-margen-usd').val(m.porcentaje);
                $('#form-margen-dolar .form-text').text('Porcentaje de ganancia sobre el costo en dolares. Actual: ' + parseFloat(m.porcentaje) + '%');
                $('#form-margen-dolar textarea[name="observacion"]').val('');
                Swal.fire({ icon: 'success', title: 'Margen actualizado', text: res.mensaje, timer: 2000, showConfirmButton: false });
                bootstrap.Modal.getInstance(document.getElementById('modal-margen-dolar')).hide();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
            }
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al actualizar el margen.' });
        });
    });

    // ─── Submit: Margen VES ───
    $('#form-margen-bolivar').on('submit', function (e) {
        e.preventDefault();
        var porcentaje = $(this).find('input[name="porcentaje"]').val();
        var observacion = $(this).find('textarea[name="observacion"]').val();

        if (!porcentaje || parseFloat(porcentaje) < 0) {
            Swal.fire({ icon: 'warning', title: 'Dato invalido', text: 'Ingrese un porcentaje valido.' });
            return;
        }

        Ajax.post(window.ROUTES.dashboard_margen_actualizar, {
            tipo: 'VES',
            porcentaje: porcentaje,
            observacion: observacion
        }).done(function (res) {
            if (res.ok) {
                var m = res.margen;
                $('#input-margen-ves').val(m.porcentaje);
                $('#form-margen-bolivar .form-text').text('Porcentaje de ganancia sobre el costo en bolivares. Actual: ' + parseFloat(m.porcentaje) + '%');
                $('#form-margen-bolivar textarea[name="observacion"]').val('');
                Swal.fire({ icon: 'success', title: 'Margen actualizado', text: res.mensaje, timer: 2000, showConfirmButton: false });
                bootstrap.Modal.getInstance(document.getElementById('modal-margen-bolivar')).hide();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
            }
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al actualizar el margen.' });
        });
    });

    // ─── Resize listener para redibujar graficas ───
    var resizeTimeout;
    $(window).on('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            Object.values(charts).forEach(function (c) {
                if (c && c.render) c.render();
            });
        }, 250);
    });

    // ─── Iniciar ───
    cargarUltimasVentas();
    initCharts();
});
