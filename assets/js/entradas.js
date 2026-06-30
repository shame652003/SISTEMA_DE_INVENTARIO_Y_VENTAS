/**
 * entradas.js - Módulo de Entrada de Productos
 * Select2 + cálculo de precios en tiempo real + tabla dinámica multi-ítem
 */

$(function () {

    /* ===== Variables globales ===== */
    let tasaBcv = 0;
    let margenUsd = 0;
    let margenVes = 0;
    let itemsPendientes = [];
    let tablaEntradas = null;

    /* ===== Inicialización ===== */

    function initConfigPrecios() {
        Ajax.post(window.ROUTES.entradas_config_precios)
            .done(function (res) {
                if (res.ok) {
                    tasaBcv = parseFloat(res.tasa?.tasa_ves_por_usd || 0);
                    margenUsd = parseFloat(res.margenUsd?.porcentaje || 0);
                    margenVes = parseFloat(res.margenVes?.porcentaje || 0);
                }
            });
    }

    /* ===== Select2 ===== */

    function initSelect2() {
        $('#select-producto').select2({
            placeholder: 'Escribe el código o nombre del repuesto...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: window.BASE_URL + window.ROUTES.entradas_productos_buscar,
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

    /* ===== Cálculo de precios ===== */

    function calcularPrecios(costoUsd) {
        const precioVentaUsd = costoUsd * (1 + margenUsd / 100);
        const precioVentaVes = costoUsd * (1 + margenVes / 100) * tasaBcv;
        return {
            usd: precioVentaUsd.toFixed(2),
            ves: precioVentaVes.toFixed(2)
        };
    }

    function actualizarPreciosCalculados() {
        const costo = parseFloat($('#costo-usd').val()) || 0;
        const precios = calcularPrecios(costo);
        $('#precio-venta-usd').val('$' + precios.usd);
        $('#precio-venta-ves').val('Bs. ' + precios.ves);
    }

    /* ===== Eventos de formulario ===== */

    $('#select-producto').on('select2:select', function (e) {
        const producto = e.params.data;
        $('#producto-id').val(producto.id);
        $('#info-codigo').text(producto.codigo);
        $('#info-nombre').text(producto.text);
        $('#info-tipo').text(producto.tipo_producto);
        $('#info-stock').text(parseFloat(producto.stock).toFixed(3));

        if (producto.precio_costo_usd && parseFloat(producto.precio_costo_usd) > 0) {
            $('#costo-usd').val(producto.precio_costo_usd);
        }

        $('#info-producto').removeClass('d-none').hide().fadeIn(300);
        $('#form-entrada-container').removeClass('d-none').hide().fadeIn(400);
        actualizarPreciosCalculados();
    });

    $('#select-producto').on('select2:clear', function () {
        limpiarFormulario();
    });

    $('#costo-usd, #cantidad').on('input', function () {
        actualizarPreciosCalculados();
    });

    $('#btn-limpiar-form').on('click', function () {
        limpiarFormulario();
    });

    function limpiarFormulario() {
        $('#form-entrada')[0].reset();
        $('#producto-id').val('');
        $('#info-producto').addClass('d-none');
        $('#form-entrada-container').addClass('d-none');
        $('#select-producto').val(null).trigger('change');
    }

    /* ===== Tabla de ítems pendientes ===== */

    $('#btn-agregar-item').on('click', function () {
        const idproducto = $('#producto-id').val();
        const costo = parseFloat($('#costo-usd').val());
        const cantidad = parseFloat($('#cantidad').val());

        if (!idproducto) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Selecciona un producto primero.' });
            return;
        }
        if (!costo || costo <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'El costo debe ser mayor a 0.' });
            return;
        }
        if (!cantidad || cantidad <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'La cantidad debe ser mayor a 0.' });
            return;
        }

        const existingIndex = itemsPendientes.findIndex(item => item.idproducto == idproducto);
        if (existingIndex >= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Producto ya agregado',
                text: 'Este producto ya está en la lista. ¿Deseas actualizar la cantidad?',
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    itemsPendientes[existingIndex].cantidad = cantidad;
                    itemsPendientes[existingIndex].costo = costo;
                    renderTablaItems();
                }
            });
        } else {
            itemsPendientes.push({
                idproducto: idproducto,
                codigo: $('#info-codigo').text(),
                nombre: $('#info-nombre').text(),
                costo: costo,
                cantidad: cantidad
            });
            renderTablaItems();
        }

        $('#form-entrada')[0].reset();
        $('#producto-id').val('');
        $('#info-producto').addClass('d-none');
        $('#form-entrada-container').addClass('d-none');
        $('#select-producto').val(null).trigger('change');
    });

    $(document).on('click', '.btn-eliminar-item', function () {
        const index = $(this).data('index');
        itemsPendientes.splice(index, 1);
        renderTablaItems();
    });

    function renderTablaItems() {
        const $tbody = $('#tabla-items-pendientes tbody');
        $tbody.empty();

        if (itemsPendientes.length === 0) {
            $tbody.html('<tr class="text-muted text-center"><td colspan="5">Sin productos agregados</td></tr>');
            $('#btn-procesar-entrada').prop('disabled', true);
        } else {
            itemsPendientes.forEach(function (item, index) {
                $tbody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td><small><strong>${item.codigo}</strong><br>${item.nombre}</small></td>
                        <td class="text-end">$${item.costo.toFixed(2)}</td>
                        <td class="text-end">${item.cantidad.toFixed(3)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-danger btn-eliminar-item" data-index="${index}" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            $('#btn-procesar-entrada').prop('disabled', false);
        }

        const totalCantidad = itemsPendientes.reduce((sum, item) => sum + item.cantidad, 0);
        $('#total-cantidad').text(totalCantidad.toFixed(3));
        $('#total-items').text(itemsPendientes.length + ' ítem(s)');
    }

    /* ===== Procesar Entrada ===== */

    $('#btn-procesar-entrada').on('click', function () {
        if (itemsPendientes.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'No hay productos en la lista.' });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: '¿Procesar entrada?',
            text: `Se registrarán ${itemsPendientes.length} producto(s) en el inventario.`,
            showCancelButton: true,
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#198754'
        }).then(function (result) {
            if (result.isConfirmed) {
                const data = {
                    descripcion: '',
                    items: JSON.stringify(itemsPendientes)
                };

                Ajax.post(window.ROUTES.entradas_guardar, data)
                    .done(function (res) {
                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: res.mensaje,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            itemsPendientes = [];
                            renderTablaItems();
                            cargarEntradas();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                        }
                    }).fail(function (xhr) {
                        let msg = 'Error al procesar la entrada.';
                        try {
                            const r = JSON.parse(xhr.responseText);
                            if (r.mensaje) msg = r.mensaje;
                        } catch (e) {}
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    });
            }
        });
    });

    /* ===== DataTable Historial ===== */

    function cargarEntradas() {
        Ajax.post(window.ROUTES.entradas_listar)
            .done(function (res) {
                const data = res.data || [];
                const rows = data.map(function (e) {
                    return [
                        e.idEntradaA,
                        e.fecha,
                        e.hora,
                        e.descripcion,
                        `<span class="badge bg-info">${e.items || 0}</span>`,
                        `<span class="badge bg-success">${parseFloat(e.total_cantidad || 0).toFixed(3)}</span>`,
                        `<button class="btn btn-sm btn-outline-primary btn-ver-detalle" data-id="${e.idEntradaA}" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </button>`
                    ];
                });

                if (tablaEntradas) {
                    tablaEntradas.clear().destroy();
                }

                tablaEntradas = $('#tabla-entradas').DataTable({
                    data: rows,
                    columns: [
                        { title: '#' },
                        { title: 'Fecha' },
                        { title: 'Hora' },
                        { title: 'Descripción' },
                        { title: 'Ítems', className: 'text-center' },
                        { title: 'Cantidad Total', className: 'text-end' },
                        { title: 'Acciones', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    responsive: true,
                    language: {
                        url: window.BASE_URL + '/assets/lib/datatables/js/es-ES.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todos']],
                    order: [[0, 'desc']],
                    dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
                });
            }).fail(function () {
                if (tablaEntradas) tablaEntradas.clear().destroy();
                $('#tabla-entradas tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">Error al cargar entradas</td></tr>');
            });
    }

    /* ===== Ver Detalle de Entrada ===== */

    $(document).on('click', '.btn-ver-detalle', function () {
        const id = $(this).data('id');
        Ajax.post(window.ROUTES.entradas_detalle, { id: id })
            .done(function (res) {
                if (res.ok && res.data) {
                    const entrada = res.data;
                    $('#detalle-id').text(entrada.idEntradaA);
                    $('#detalle-fecha').text(entrada.fecha);
                    $('#detalle-hora').text(entrada.hora);
                    $('#detalle-descripcion').text(entrada.descripcion);

                    const $tbody = $('#tabla-detalle-entrada tbody');
                    $tbody.empty();
                    (entrada.detalles || []).forEach(function (d) {
                        const subtotal = (parseFloat(d.costo_unitario_usd || 0) * parseFloat(d.cantidad)).toFixed(2);
                        $tbody.append(`
                            <tr>
                                <td>${d.codigo}</td>
                                <td>${d.producto_nombre}</td>
                                <td class="text-end">$${parseFloat(d.costo_unitario_usd || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(d.cantidad).toFixed(3)}</td>
                                <td class="text-end">$${subtotal}</td>
                            </tr>
                        `);
                    });

                    new bootstrap.Modal('#modal-detalle-entrada').show();
                }
            });
    });

    /* ===== Inicialización ===== */

    initConfigPrecios();
    initSelect2();
    cargarEntradas();
});
