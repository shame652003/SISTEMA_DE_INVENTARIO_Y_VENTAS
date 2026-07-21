/**
 * stock.js - Módulo de Stock e Inventario
 * Select2 + DataTable server-side + filtros sin destrucción
 */

$(function () {

    var tablaStock = null;
    var filtroActual = 'todos';
    var fmtVes = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    /* ===== Select2 ===== */

    function initSelect2() {
        $('#select-producto').select2({
            placeholder: 'Escribe el código o nombre del repuesto...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: window.BASE_URL + window.ROUTES.stock_productos_buscar,
                type: 'POST',
                dataType: 'json',
                delay: 200,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                cache: true
            }
        });
    }

    /* ===== Eventos Select2 ===== */

    $('#select-producto').on('select2:select', function (e) {
        var p = e.params.data;
        if (!p || !p.id) return;

        var stock = parseFloat(p.stock) || 0;
        var stockMin = parseFloat(p.stock_minimo) || 0;
        var precioVentaUsd = parseFloat(p.precio_venta_usd) || 0;
        var precioVentaVes = parseFloat(p.precio_venta_ves) || 0;
        var precioBcv = parseFloat(p.precio_bcv) || 0;

        if (p.imgproducto) {
            $('#detalle-imagen img').attr('src', window.BASE_URL + '/' + p.imgproducto);
            $('#detalle-imagen-icon').addClass('d-none');
            $('#detalle-imagen img').removeClass('d-none');
        } else {
            $('#detalle-imagen img').addClass('d-none').attr('src', '');
            $('#detalle-imagen-icon').removeClass('d-none');
        }

        $('#detalle-nombre').text(p.nombre);
        $('#detalle-codigo').text(p.codigo || 'N/A');
        $('#detalle-tipo').text(p.tipo_producto || 'N/A');
        $('#detalle-marca').text(p.marca || 'Sin marca');

        var stockHtml = '';
        if (stock === 0) {
            stockHtml = '<span class="badge bg-danger fs-5">' + Math.round(stock) + '</span>';
        } else if (stock <= stockMin) {
            stockHtml = '<span class="badge bg-warning text-dark fs-5">' + Math.round(stock) + '</span>';
        } else {
            stockHtml = '<span class="badge bg-success fs-5">' + Math.round(stock) + '</span>';
        }
        $('#detalle-stock').html(stockHtml);

        $('#detalle-precio-bs').text('Bs. ' + fmtVes.format(precioVentaVes));
        $('#detalle-precio-bcv').text('$' + precioBcv.toFixed(2));
        $('#detalle-precio-usd').text('$' + precioVentaUsd.toFixed(2));

        $('#detalle-producto-container').slideDown(300);
    });

    $('#select-producto').on('select2:clear', function () {
        $('#detalle-producto-container').slideUp(300);
    });

    /* ===== DataTable server-side ===== */

    function initDataTable() {
        tablaStock = $('#tabla-stock').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.BASE_URL + window.ROUTES.stock_listar,
                type: 'POST',
                data: function (d) {
                    d.filtro = filtroActual;
                }
            },
            columns: [
                {
                    data: 'imgproducto',
                    render: function (data) {
                        if (data) {
                            return '<img src="' + window.BASE_URL + '/' + data + '" class="rounded" style="width:50px;height:50px;object-fit:contain;">';
                        }
                        return '<span class="badge bg-secondary">Sin imagen</span>';
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'codigo' },
                {
                    data: 'nombre',
                    render: function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return '<div><strong>' + data + '</strong><br><small class="text-muted">' + (row.marca || '') + '</small></div>';
                        }
                        return data;
                    }
                },
                {
                    data: 'tipo_producto',
                    render: function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            return '<span class="badge bg-info">' + (data || 'N/A') + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'stock',
                    render: function (data, type, row) {
                        var s = parseFloat(data) || 0;
                        if (type === 'display' || type === 'filter') {
                            var min = parseFloat(row.stock_minimo) || 0;
                            var cls = 'bg-success';
                            if (s === 0) cls = 'bg-danger';
                            else if (s <= min) cls = 'bg-warning text-dark';
                            return '<span class="badge ' + cls + ' fs-6">' + Math.round(s) + '</span>';
                        }
                        return s;
                    },
                    className: 'text-center'
                },
                {
                    data: 'precio_venta_ves',
                    render: function (data, type) {
                        var val = parseFloat(data) || 0;
                        if (type === 'display' || type === 'filter') {
                            return '<span class="badge bg-primary fs-6">Bs. ' + fmtVes.format(val) + '</span>';
                        }
                        return val;
                    },
                    className: 'text-center'
                },
                {
                    data: 'precio_bcv',
                    render: function (data, type) {
                        var val = parseFloat(data) || 0;
                        if (type === 'display' || type === 'filter') {
                            return '<span class="badge bg-info text-dark fs-6">$' + val.toFixed(2) + '</span>';
                        }
                        return val;
                    },
                    className: 'text-center'
                },
                {
                    data: 'precio_venta_usd',
                    render: function (data, type) {
                        var val = parseFloat(data) || 0;
                        if (type === 'display' || type === 'filter') {
                            return '<span class="badge bg-success fs-6">$' + val.toFixed(2) + '</span>';
                        }
                        return val;
                    },
                    className: 'text-center'
                }
            ],
            language: {
                url: window.BASE_URL + '/assets/lib/datatables/js/es-ES.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            order: [[2, 'asc']],
            dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
        });

        $('#tabla-stock').on('xhr.dt', function (e, settings, json) {
            $('#total-productos').text(json.recordsFiltered + ' producto(s)');
        });
    }

    /* ===== Filtros ===== */

    $('[data-filtro]').on('click', function () {
        filtroActual = $(this).data('filtro');
        $('[data-filtro]').removeClass('active');
        $(this).addClass('active');
        tablaStock.ajax.reload(null, false);
    });

    /* ===== Inicialización ===== */

    initSelect2();
    initDataTable();
});
