/**
 * stock.js - Módulo de Stock con búsqueda rápida y filtros
 */

$(function () {

    let tablaStock = null;
    let filtroActual = 'todos';
    let busquedaDebounce = null;

    // ═══════════════════════════════════════════════════════
    // CARGAR DATOS
    // ═══════════════════════════════════════════════════════

    function cargarStock(filtro = 'todos') {
        filtroActual = filtro;
        Ajax.post(window.ROUTES.stock_listar, { filtro: filtro })
            .done(function (res) {
                const data = res.data || [];
                const rows = data.map(function (p) {
                    const stock = parseFloat(p.stock) || 0;
                    const stockMin = parseFloat(p.stock_minimo) || 0;
                    
                    let estadoBadge = '';
                    if (stock === 0) {
                        estadoBadge = '<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Agotado</span>';
                    } else if (stock <= stockMin) {
                        estadoBadge = '<span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">Stock Bajo</span>';
                    } else {
                        estadoBadge = '<span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Disponible</span>';
                    }

                    return {
                        codigo: p.codigo || 'N/A',
                        nombre: `<div class="fw-semibold">${p.nombre || 'Sin nombre'}</div><small class="text-muted">${p.marca || ''}</small>`,
                        tipo_producto: `<span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">${p.tipo_producto || 'N/A'}</span>`,
                        stock: `<div class="text-center fw-bold ${stock === 0 ? 'text-danger' : (stock <= stockMin ? 'text-warning' : 'text-success')}">${stock.toFixed(2)}</div>`,
                        stock_minimo: `<div class="text-center text-muted">${stockMin.toFixed(2)}</div>`,
                        unidadMedida: `<div class="text-center">${p.unidadMedida || 'Und'}</div>`,
                        estado: `<div class="text-center">${estadoBadge}</div>`
                    };
                });

                $('#total-productos').text(data.length);

                if (tablaStock) {
                    tablaStock.clear().destroy();
                }

                tablaStock = $('#tabla-stock').DataTable({
                    data: rows,
                    columns: [
                        { data: 'codigo' },
                        { data: 'nombre' },
                        { data: 'tipo_producto' },
                        { data: 'stock', orderable: true },
                        { data: 'stock_minimo' },
                        { data: 'unidadMedida' },
                        { data: 'estado', orderable: false }
                    ],
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
                    order: [[1, 'asc']],
                    dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
                });
            }).fail(function () {
                if (tablaStock) tablaStock.clear().destroy();
                $('#tabla-stock tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">Error al cargar el stock</td></tr>');
            });
    }

    // ═══════════════════════════════════════════════════════
    // BÚSQUEDA RÁPIDA
    // ═══════════════════════════════════════════════════════

    $('#buscar-stock').on('input', function () {
        const termino = $(this).val().trim();
        
        if (busquedaDebounce) clearTimeout(busquedaDebounce);
        
        busquedaDebounce = setTimeout(function () {
            if (tablaStock) {
                tablaStock.search(termino).draw();
            }
        }, 300);
    });

    // ═══════════════════════════════════════════════════════
    // FILTROS
    // ═══════════════════════════════════════════════════════

    $('[data-filtro]').on('click', function () {
        const filtro = $(this).data('filtro');
        
        $('[data-filtro]').removeClass('active');
        $(this).addClass('active');
        
        cargarStock(filtro);
    });

    // ═══════════════════════════════════════════════════════
    // INICIALIZAR
    // ═══════════════════════════════════════════════════════

    cargarStock('todos');

});
