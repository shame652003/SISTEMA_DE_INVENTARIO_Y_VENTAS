/**
 * salidas.js - Módulo de Salidas de Productos
 * Select2 + validación de stock + tabla dinámica multi-ítem + DataTable historial
 */

$(function () {

    let tablaSalidas = null;
    let itemsPendientes = [];
    let stockActual = 0;

    /* ===== Select2 ===== */

    function initSelect2() {
        $('#select-producto').select2({
            placeholder: 'Escribe el código o nombre del repuesto...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: window.BASE_URL + window.ROUTES.salidas_productos_buscar,
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

    /* ===== Eventos de formulario ===== */

    $('#select-producto').on('select2:select', function (e) {
        const producto = e.params.data;
        stockActual = parseFloat(producto.stock) || 0;

        $('#producto-id').val(producto.id);
        $('#info-codigo').text(producto.codigo);
        $('#info-nombre').text(producto.text);
        $('#info-tipo').text(producto.tipo_producto);
        $('#info-stock').text(stockActual.toFixed(3));

        $('#info-producto').removeClass('d-none').hide().fadeIn(300);
        $('#form-salida-container').removeClass('d-none').hide().fadeIn(400);
    });

    $('#select-producto').on('select2:clear', function () {
        limpiarFormulario();
    });

    $('#btn-limpiar-form').on('click', function () {
        limpiarFormulario();
    });

    function limpiarFormulario() {
        $('#form-salida')[0].reset();
        $('#producto-id').val('');
        $('#info-producto').addClass('d-none');
        $('#form-salida-container').addClass('d-none');
        $('#select-producto').val(null).trigger('change');
        stockActual = 0;
    }

    /* ===== Tabla de ítems pendientes ===== */

    $('#btn-agregar-item').on('click', function () {
        const idproducto = $('#producto-id').val();
        const idTipoSalidaA = $('#idTipoSalidaA').val();
        const cantidad = parseFloat($('#cantidad').val());
        const descripcion = $('#descripcion').val().trim();
        const tipoNombre = $('#idTipoSalidaA option:selected').text();

        if (!idproducto) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Selecciona un producto primero.' });
            return;
        }
        if (!idTipoSalidaA) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Selecciona el tipo de salida.' });
            return;
        }
        if (!cantidad || cantidad <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'La cantidad debe ser mayor a 0.' });
            return;
        }
        if (cantidad > stockActual) {
            Swal.fire({
                icon: 'error',
                title: 'Stock insuficiente',
                text: `Solo hay ${stockActual.toFixed(3)} unidades disponibles.`
            });
            return;
        }
        if (!descripcion) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'La descripción es obligatoria.' });
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
                    itemsPendientes[existingIndex].idTipoSalidaA = idTipoSalidaA;
                    itemsPendientes[existingIndex].tipoNombre = tipoNombre;
                    itemsPendientes[existingIndex].descripcion = descripcion;
                    renderTablaItems();
                }
            });
        } else {
            itemsPendientes.push({
                idproducto: idproducto,
                codigo: $('#info-codigo').text(),
                nombre: $('#info-nombre').text(),
                idTipoSalidaA: idTipoSalidaA,
                tipoNombre: tipoNombre,
                cantidad: cantidad,
                descripcion: descripcion
            });
            renderTablaItems();
        }

        $('#form-salida')[0].reset();
        $('#producto-id').val('');
        $('#info-producto').addClass('d-none');
        $('#form-salida-container').addClass('d-none');
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
            $('#btn-procesar-salida').prop('disabled', true);
        } else {
            itemsPendientes.forEach(function (item, index) {
                $tbody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td><small><strong>${item.codigo}</strong><br>${item.nombre}</small></td>
                        <td><small>${item.tipoNombre}</small></td>
                        <td class="text-end">${item.cantidad.toFixed(3)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-danger btn-eliminar-item" data-index="${index}" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            $('#btn-procesar-salida').prop('disabled', false);
        }

        const totalCantidad = itemsPendientes.reduce((sum, item) => sum + item.cantidad, 0);
        $('#total-cantidad').text(totalCantidad.toFixed(3));
        $('#total-items').text(itemsPendientes.length + ' ítem(s)');
    }

    /* ===== Procesar Salida ===== */

    $('#btn-procesar-salida').on('click', function () {
        if (itemsPendientes.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'No hay productos en la lista.' });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: '¿Procesar salida?',
            text: `Se registrarán ${itemsPendientes.length} producto(s) como salida del inventario.`,
            showCancelButton: true,
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (result.isConfirmed) {
                const data = {
                    items: JSON.stringify(itemsPendientes)
                };

                Ajax.post(window.ROUTES.salidas_guardar, data)
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
                            cargarSalidas();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                        }
                    }).fail(function (xhr) {
                        let msg = 'Error al procesar la salida.';
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

    function cargarSalidas() {
        Ajax.post(window.ROUTES.salidas_listar)
            .done(function (res) {
                const data = res.data || [];
                const rows = data.map(function (s) {
                    const badgeClass = s.tipoSalida === 'Perdidas' ? 'bg-danger' : 'bg-info';
                    return [
                        s.idSalidaA,
                        s.fecha,
                        s.hora,
                        `<strong>${s.codigo}</strong><br><small>${s.producto_nombre}</small>`,
                        `<span class="badge ${badgeClass}">${s.tipoSalida}</span>`,
                        parseFloat(s.cantidad).toFixed(3),
                        s.descripcion || '-'
                    ];
                });

                if (tablaSalidas) {
                    tablaSalidas.clear().destroy();
                }

                tablaSalidas = $('#tabla-salidas').DataTable({
                    data: rows,
                    columns: [
                        { title: '#' },
                        { title: 'Fecha' },
                        { title: 'Hora' },
                        { title: 'Producto' },
                        { title: 'Tipo' },
                        { title: 'Cantidad', className: 'text-end' },
                        { title: 'Descripción' }
                    ],
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todos']],
                    order: [[0, 'desc']],
                    dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
                });
            }).fail(function () {
                if (tablaSalidas) tablaSalidas.clear().destroy();
                $('#tabla-salidas tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">Error al cargar salidas</td></tr>');
            });
    }

    /* ===== Inicialización ===== */

    initSelect2();
    cargarSalidas();
});
