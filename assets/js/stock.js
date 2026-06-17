/**
 * stock.js - Módulo de Stock e Inventario
 * Select2 + filtros + DataTable con precios resaltados
 */

$(function () {

    let tablaStock = null;
    let filtroActual = 'todos';

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
                delay: 300,
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
        const producto = e.params.data;
        const idProducto = producto.id;

        // Hacer AJAX para obtener detalles completos del producto
        Ajax.post(window.ROUTES.stock_producto_info, { id: idProducto })
            .done(function (res) {
                if (res.ok && res.data) {
                    const p = res.data;
                    const stock = parseFloat(p.stock) || 0;
                    const stockMin = parseFloat(p.stock_minimo) || 0;
                    const precioVentaUsd = parseFloat(p.precio_venta_usd) || 0;
                    const precioVentaVes = parseFloat(p.precio_venta_ves) || 0;
                    const precioBcv = parseFloat(p.precio_bcv) || 0;

                    // Imagen
                    if (p.imgproducto) {
                        $('#detalle-imagen img').attr('src', `${window.BASE_URL}/${p.imgproducto}`);
                    } else {
                        $('#detalle-imagen img').attr('src', `${window.BASE_URL}/assets/img/placeholder-product.svg`);
                    }

                    // Información del producto
                    $('#detalle-nombre').text(p.nombre);
                    $('#detalle-codigo').text(p.codigo || 'N/A');
                    $('#detalle-tipo').text(p.tipo_producto || 'N/A');
                    $('#detalle-marca').text(p.marca || 'Sin marca');

                    // Stock con color
                    let stockHtml = '';
                    if (stock === 0) {
                        stockHtml = `<span class="badge bg-danger fs-5">${stock.toFixed(3)}</span>`;
                    } else if (stock <= stockMin) {
                        stockHtml = `<span class="badge bg-warning text-dark fs-5">${stock.toFixed(3)}</span>`;
                    } else {
                        stockHtml = `<span class="badge bg-success fs-5">${stock.toFixed(3)}</span>`;
                    }
                    $('#detalle-stock').html(stockHtml);

                    // Precios
                    const precioVesFormateado = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(precioVentaVes);
                    $('#detalle-precio-bs').text(`Bs. ${precioVesFormateado}`);
                    $('#detalle-precio-bcv').text(`$${precioBcv.toFixed(2)}`);
                    $('#detalle-precio-usd').text(`$${precioVentaUsd.toFixed(2)}`);

                    // Mostrar la card con animación
                    $('#detalle-producto-container').slideDown(300);
                }
            })
            .fail(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener la información del producto.',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
    });

    $('#select-producto').on('select2:clear', function () {
        // Ocultar la card de detalle
        $('#detalle-producto-container').slideUp(300);
    });

    /* ===== Filtros ===== */

    $('[data-filtro]').on('click', function () {
        const filtro = $(this).data('filtro');
        filtroActual = filtro;
        $('[data-filtro]').removeClass('active');
        $(this).addClass('active');
        cargarStock(filtro);
    });

    /* ===== Cargar Stock ===== */

    function cargarStock(filtro = 'todos') {
        Ajax.post(window.ROUTES.stock_listar, { filtro: filtro })
            .done(function (res) {
                const data = res.data || [];
                $('#total-productos').text(data.length + ' producto(s)');

                const rows = data.map(function (p) {
                    const stock = parseFloat(p.stock) || 0;
                    const stockMin = parseFloat(p.stock_minimo) || 0;
                    const precioVentaUsd = parseFloat(p.precio_venta_usd) || 0;
                    const precioVentaVes = parseFloat(p.precio_venta_ves) || 0;

                    // Imagen
                    let imgHtml = '';
                    if (p.imgproducto) {
                        imgHtml = `<img src="${window.BASE_URL}/${p.imgproducto}" class="rounded" style="width:50px;height:50px;object-fit:cover;">`;
                    } else {
                        imgHtml = `<span class="badge bg-secondary">Sin imagen</span>`;
                    }

                    // Stock con color
                    let stockHtml = '';
                    if (stock === 0) {
                        stockHtml = `<span class="badge bg-danger fs-6">${stock.toFixed(3)}</span>`;
                    } else if (stock <= stockMin) {
                        stockHtml = `<span class="badge bg-warning text-dark fs-6">${stock.toFixed(3)}</span>`;
                    } else {
                        stockHtml = `<span class="badge bg-success fs-6">${stock.toFixed(3)}</span>`;
                    }

                    // Precios resaltados
                    const precioVesFormateado = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(precioVentaVes);
                    const precioBsHtml = `<span class="badge bg-primary fs-6">Bs. ${precioVesFormateado}</span>`;
                    const precioBcvHtml = `<span class="badge bg-info text-dark fs-6">$${(p.precio_bcv || 0).toFixed(2)}</span>`;
                    const precioUsdHtml = `<span class="badge bg-success fs-6">$${precioVentaUsd.toFixed(2)}</span>`;

                    return [
                        imgHtml,
                        p.codigo || 'N/A',
                        `<div><strong>${p.nombre}</strong><br><small class="text-muted">${p.marca || ''}</small></div>`,
                        `<span class="badge bg-info">${p.tipo_producto || 'N/A'}</span>`,
                        stockHtml,
                        precioBsHtml,
                        precioBcvHtml,
                        precioUsdHtml
                    ];
                });

                if (tablaStock) {
                    tablaStock.clear().destroy();
                }

                tablaStock = $('#tabla-stock').DataTable({
                    data: rows,
                    columns: [
                        { title: 'Imagen', orderable: false, searchable: false },
                        { title: 'Código' },
                        { title: 'Producto' },
                        { title: 'Tipo' },
                        { title: 'Stock Actual', className: 'text-center' },
                        { title: 'Precio Bs', className: 'text-center' },
                        { title: 'Precio $Bcv', className: 'text-center' },
                        { title: 'Precio $ venta usd', className: 'text-center' }
                    ],
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                    order: [[2, 'asc']],
                    dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
                });
            }).fail(function () {
                if (tablaStock) tablaStock.clear().destroy();
                $('#tabla-stock tbody').html('<tr><td colspan="8" class="text-center text-muted py-4">Error al cargar el stock</td></tr>');
            });
    }

    /* ===== Inicialización ===== */

    initSelect2();
    cargarStock('todos');
});
