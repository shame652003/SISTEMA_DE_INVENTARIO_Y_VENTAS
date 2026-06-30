/**
 * ventas.js - Módulo de Ventas
 * Select2 + producto dinámico + pagos por toggle + validación estricta
 */

$(function () {

    let productosPendientes = [];
    let pagosActivos = [];
    let clienteSeleccionado = null;
    let productoSeleccionado = null;
    let tasaBcv = 0;

    /* ===== Inicialización ===== */

    function init() {
        cargarTasaBcv();
        initSelect2Cliente();
        initSelect2Producto();
        initEventos();
    }

    function cargarTasaBcv() {
        Ajax.post(window.ROUTES.ventas_tasa_bcv)
            .done(function (res) {
                if (res.ok) {
                    tasaBcv = parseFloat(res.tasa) || 0;
                }
            });
    }

    /* ===== Select2 Cliente ===== */

    function initSelect2Cliente() {
        $('#select-cliente').select2({
            placeholder: 'Escribe la cédula o nombre del cliente...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: window.BASE_URL + window.ROUTES.ventas_clientes_buscar,
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

    $('#select-cliente').on('select2:select', function (e) {
        const cliente = e.params.data;
        clienteSeleccionado = cliente;

        $('#info-cedula').text(cliente.cedula);
        $('#info-nombre').text(cliente.text);
        $('#info-telefono').text(cliente.telefono || 'No registrado');

        Ajax.post(window.ROUTES.ventas_cliente_credito, { cedula: cliente.cedula })
            .done(function (res) {
                if (res.ok) {
                    const saldo = parseFloat(res.saldo) || 0;
                    $('#info-credito').text('$' + saldo.toFixed(2));
                }
            });

        $('#info-cliente').removeClass('d-none').hide().fadeIn(300);
    });

    $('#select-cliente').on('select2:clear', function () {
        clienteSeleccionado = null;
        $('#info-cliente').addClass('d-none');
    });

    /* ===== Modal Nuevo Cliente ===== */

    $('#form-nuevo-cliente').on('submit', function (e) {
        e.preventDefault();
        Validaciones.limpiarErrores('#form-nuevo-cliente');
        if (!Validaciones.validarFormulario('#form-nuevo-cliente')) return;

        const data = {
            cedula: $('#cliente-cedula').val(),
            nombre: $('#cliente-nombre').val(),
            apellido: $('#cliente-apellido').val()
        };

        Ajax.post(window.ROUTES.ventas_cliente_guardar, data)
            .done(function (res) {
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: res.mensaje,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#form-nuevo-cliente')[0].reset();
                    bootstrap.Modal.getInstance('#modal-nuevo-cliente').hide();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                }
            }).fail(function (xhr) {
                let msg = 'Error al registrar el cliente.';
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.mensaje) msg = r.mensaje;
                } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            });
    });

    /* ===== Select2 Producto ===== */

    function initSelect2Producto() {
        $('#select-producto').select2({
            placeholder: 'Escribe el código o nombre del repuesto...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: window.BASE_URL + window.ROUTES.ventas_productos_buscar,
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

    $('#select-producto').on('select2:select', function (e) {
        const producto = e.params.data;
        productoSeleccionado = producto;

        // Obtener info completa
        Ajax.post(window.ROUTES.ventas_producto_info, { id: producto.id })
            .done(function (res) {
                if (res.ok && res.data) {
                    const p = res.data;
                    const stock = parseFloat(p.stock) || 0;
                    const stockMin = parseFloat(p.stock_minimo) || 0;

                    // Badge de stock con color
                    let stockBadge = '';
                    if (stock === 0) {
                        stockBadge = '<span class="badge bg-danger rounded-pill fs-6">' + stock.toFixed(3) + '</span>';
                    } else if (stock <= stockMin) {
                        stockBadge = '<span class="badge bg-warning text-dark rounded-pill fs-6">' + stock.toFixed(3) + '</span>';
                    } else {
                        stockBadge = '<span class="badge bg-success rounded-pill fs-6">' + stock.toFixed(3) + '</span>';
                    }

                    $('#info-stock').html(stockBadge);
                    $('#info-precio-bs').text('Bs. ' + new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parseFloat(p.precio_venta_ves) || 0));
                    $('#info-precio-bcv').text('$' + (parseFloat(p.precio_bcv) || 0).toFixed(2));

                    // Configurar cantidad
                    $('#cantidad').val(1).attr('max', stock);
                    
                    // Mostrar info y hacer autofocus
                    $('#info-producto').removeClass('d-none').hide().fadeIn(300, function() {
                        $('#cantidad').focus().select();
                    });
                }
            });
    });

    $('#select-producto').on('select2:clear', function () {
        productoSeleccionado = null;
        $('#info-producto').addClass('d-none');
    });

    /* ===== Eventos de cantidad ===== */

    function initEventos() {
        // Botón menos
        $('#btn-cant-menos').on('click', function () {
            const $input = $('#cantidad');
            let val = parseFloat($input.val()) || 1;
            val = Math.max(1, val - 1);
            $input.val(val.toFixed(3));
            validarCantidad();
        });

        // Botón más
        $('#btn-cant-mas').on('click', function () {
            const $input = $('#cantidad');
            let val = parseFloat($input.val()) || 1;
            const max = parseFloat($input.attr('max')) || 999999;
            val = Math.min(max, val + 1);
            $input.val(val.toFixed(3));
            validarCantidad();
        });

        // Cambio manual
        $('#cantidad').on('input', function () {
            validarCantidad();
        });

        // Agregar producto
        $('#btn-agregar-producto').on('click', agregarProducto);

        // Enter en cantidad
        $('#cantidad').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                agregarProducto();
            }
        });

        // Limpiar
        $('#btn-limpiar').on('click', limpiarTodo);

        // Procesar venta
        $('#btn-procesar-venta').on('click', procesarVenta);
    }

    function validarCantidad() {
        const cantidad = parseFloat($('#cantidad').val()) || 0;
        const max = parseFloat($('#cantidad').attr('max')) || 0;
        const $btn = $('#btn-agregar-producto');

        if (cantidad > max) {
            $btn.prop('disabled', true).addClass('btn-danger').removeClass('btn-primary');
            $('#info-stock').find('.badge').removeClass('bg-success bg-warning').addClass('bg-danger');
        } else {
            $btn.prop('disabled', false).addClass('btn-primary').removeClass('btn-danger');
        }
    }

    function agregarProducto() {
        if (!productoSeleccionado) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Selecciona un producto.' });
            return;
        }

        const cantidad = parseFloat($('#cantidad').val());
        const stock = parseFloat($('#cantidad').attr('max'));

        if (!cantidad || cantidad <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'La cantidad debe ser mayor a 0.' });
            return;
        }

        if (cantidad > stock) {
            Swal.fire({
                icon: 'error',
                title: 'Stock insuficiente',
                text: `Solo hay ${stock.toFixed(3)} unidades disponibles.`
            });
            return;
        }

        const p = productoSeleccionado;
        const existingIndex = productosPendientes.findIndex(item => item.idproducto == p.id);

        if (existingIndex >= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Producto ya agregado',
                text: '¿Deseas actualizar la cantidad?',
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    productosPendientes[existingIndex].cantidad = cantidad;
                    renderTablaProductos();
                    resetProducto();
                }
            });
        } else {
            productosPendientes.push({
                idproducto: p.id,
                codigo: p.codigo,
                nombre: p.text,
                precio_venta_usd: parseFloat(p.precio_venta_usd) || 0,
                precio_venta_ves: parseFloat(p.precio_venta_ves) || 0,
                precio_bcv: parseFloat(p.precio_bcv) || 0,
                cantidad: cantidad
            });
            renderTablaProductos();
            resetProducto();
        }
    }

    function resetProducto() {
        $('#select-producto').val(null).trigger('change');
        $('#cantidad').val(1);
        $('#info-producto').addClass('d-none');
        productoSeleccionado = null;
    }

    $(document).on('click', '.btn-eliminar-producto', function () {
        const index = $(this).data('index');
        productosPendientes.splice(index, 1);
        renderTablaProductos();
    });

    function renderTablaProductos() {
        const $tbody = $('#tabla-productos tbody');
        $tbody.empty();

        if (productosPendientes.length === 0) {
            $tbody.html('<tr class="text-muted text-center"><td colspan="10">Sin productos agregados</td></tr>');
            $('#btn-procesar-venta').prop('disabled', true);
        } else {
            productosPendientes.forEach(function (item, index) {
                const subtotalUsd = item.precio_venta_usd * item.cantidad;
                const subtotalVes = item.precio_venta_ves * item.cantidad;
                const precioVesFormateado = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(item.precio_venta_ves);
                const subtotalVesFormateado = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(subtotalVes);

                $tbody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.codigo}</td>
                        <td>${item.nombre}</td>
                        <td class="text-center"><span class="badge bg-primary rounded-pill">Bs. ${precioVesFormateado}</span></td>
                        <td class="text-center"><span class="badge bg-info text-dark rounded-pill">$${item.precio_bcv.toFixed(2)}</span></td>
                        <td class="text-center"><span class="badge bg-success rounded-pill">$${item.precio_venta_usd.toFixed(2)}</span></td>
                        <td class="text-end">${item.cantidad.toFixed(3)}</td>
                        <td class="text-end fw-bold">$${subtotalUsd.toFixed(2)}</td>
                        <td class="text-end fw-bold">${subtotalVesFormateado}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-danger rounded-circle btn-eliminar-producto" data-index="${index}" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            $('#btn-procesar-venta').prop('disabled', false);
        }

        const totalUsd = productosPendientes.reduce((sum, item) => sum + (item.precio_venta_usd * item.cantidad), 0);
        const totalVes = productosPendientes.reduce((sum, item) => sum + (item.precio_venta_ves * item.cantidad), 0);
        const totalVesFormateado = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalVes);

        $('#total-usd').text('$' + totalUsd.toFixed(2));
        $('#total-bs').text('Bs. ' + totalVesFormateado);
        $('#total-items').text(productosPendientes.length + ' ítem(s)');
        $('#total-pagar').text('$' + totalUsd.toFixed(2));
        $('#btn-total').text('$' + totalUsd.toFixed(2));

        actualizarPendiente();
    }

    /* ===== Pagos por Toggle ===== */

    $(document).on('click', '.metodo-pago-btn', function () {
        const $btn = $(this);
        const id = $btn.data('id');
        const nombre = $btn.data('nombre');

        if ($btn.hasClass('active')) {
            // Desactivar
            $btn.removeClass('active btn-primary').addClass('btn-outline-secondary');
            pagosActivos = pagosActivos.filter(p => p.idtipo_de_pagos !== id);
        } else {
            // Activar
            $btn.removeClass('btn-outline-secondary').addClass('active btn-primary');
            pagosActivos.push({
                idtipo_de_pagos: id,
                nombre: nombre,
                monto_recibido: 0,
                moneda: 'USD',
                referencia: ''
            });
        }

        renderPagosActivos();
    });

    function renderPagosActivos() {
        const $container = $('#pagos-container');
        $container.empty();

        if (pagosActivos.length === 0) {
            $('#detalle-pagos').addClass('d-none');
            actualizarPendiente();
            return;
        }

        $('#detalle-pagos').removeClass('d-none');

        const totalVenta = productosPendientes.reduce((sum, item) => sum + (item.precio_venta_usd * item.cantidad), 0);

        pagosActivos.forEach(function (pago, index) {
            // Auto-fill: primer método recibe el total
            if (index === 0 && pago.monto_recibido === 0) {
                pago.monto_recibido = totalVenta;
            }

            // Símbolo dinámico según moneda
            const esVes = pago.moneda === 'VES';
            const prefijo = esVes ? 'Bs.' : '$';
            const montoValue = pago.monto_recibido.toFixed(2);

            $container.append(`
                <div class="row mb-3 pago-row" data-index="${index}">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">💳 ${pago.nombre}</label>
                        <input type="text" class="form-control" value="${pago.nombre}" disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">${prefijo}</span>
                            <input type="number" class="form-control pago-monto" data-index="${index}" 
                                   value="${montoValue}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Moneda</label>
                        <select class="form-select pago-moneda" data-index="${index}">
                            <option value="USD" ${pago.moneda === 'USD' ? 'selected' : ''}>USD</option>
                            <option value="VES" ${pago.moneda === 'VES' ? 'selected' : ''}>VES</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Referencia</label>
                        <input type="text" class="form-control pago-referencia" data-index="${index}" 
                               value="${pago.referencia}" placeholder="Opcional">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-outline-danger btn-sm rounded-circle btn-quitar-pago" data-index="${index}">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            `);
        });

        actualizarPendiente();
    }

    $(document).on('input', '.pago-monto', function () {
        const index = $(this).data('index');
        pagosActivos[index].monto_recibido = parseFloat($(this).val()) || 0;
        actualizarPendiente();
    });

    $(document).on('change', '.pago-moneda', function () {
        const index = $(this).data('index');
        const monedaAnterior = pagosActivos[index].moneda;
        const nuevaMoneda = $(this).val();
        
        pagosActivos[index].moneda = nuevaMoneda;
        
        // Convertir monto entre monedas
        if (monedaAnterior !== nuevaMoneda && tasaBcv > 0) {
            if (nuevaMoneda === 'VES') {
                pagosActivos[index].monto_recibido = parseFloat((pagosActivos[index].monto_recibido * tasaBcv).toFixed(2));
            } else {
                pagosActivos[index].monto_recibido = parseFloat((pagosActivos[index].monto_recibido / tasaBcv).toFixed(2));
            }
        }
        
        renderPagosActivos();
    });

    $(document).on('input', '.pago-referencia', function () {
        const index = $(this).data('index');
        pagosActivos[index].referencia = $(this).val();
    });

    $(document).on('click', '.btn-quitar-pago', function () {
        const index = $(this).data('index');
        const pago = pagosActivos[index];
        
        // Desactivar botón
        $(`.metodo-pago-btn[data-id="${pago.idtipo_de_pagos}"]`).removeClass('active btn-primary').addClass('btn-outline-secondary');
        
        pagosActivos.splice(index, 1);
        renderPagosActivos();
    });

    function actualizarPendiente() {
        const totalUsd = productosPendientes.reduce((sum, item) => sum + (item.precio_venta_usd * item.cantidad), 0);

        let totalPagadoUsd = 0;
        pagosActivos.forEach(function (pago) {
            if (pago.moneda === 'USD') {
                totalPagadoUsd += pago.monto_recibido;
            } else {
                totalPagadoUsd += tasaBcv > 0 ? pago.monto_recibido / tasaBcv : 0;
            }
        });

        const pendiente = Math.max(0, totalUsd - totalPagadoUsd);

        $('#total-pagado').text('$' + totalPagadoUsd.toFixed(2));
        $('#pendiente').text('$' + pendiente.toFixed(2));
    }

    /* ===== Procesar Venta ===== */

    function procesarVenta() {
        if (!clienteSeleccionado) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe seleccionar un cliente.' });
            return;
        }
        if (productosPendientes.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe agregar al menos un producto.' });
            return;
        }
        if (pagosActivos.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe agregar al menos un método de pago.' });
            return;
        }

        const totalUsd = productosPendientes.reduce((sum, item) => sum + (item.precio_venta_usd * item.cantidad), 0);
        let totalPagadoUsd = 0;
        pagosActivos.forEach(function (pago) {
            if (pago.moneda === 'USD') {
                totalPagadoUsd += pago.monto_recibido;
            } else {
                totalPagadoUsd += tasaBcv > 0 ? pago.monto_recibido / tasaBcv : 0;
            }
        });

        const pendiente = totalUsd - totalPagadoUsd;

        // Validación estricta
        if (pendiente > 0.01) {
            Swal.fire({
                icon: 'error',
                title: 'Validación de pagos',
                text: `El total pagado ($${totalPagadoUsd.toFixed(2)}) es menor al total de la venta ($${totalUsd.toFixed(2)}). Pendiente: $${pendiente.toFixed(2)}`
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: '¿Procesar venta?',
            text: `Se registrarán ${productosPendientes.length} producto(s) y ${pagosActivos.length} pago(s).`,
            showCancelButton: true,
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd'
        }).then(function (result) {
            if (result.isConfirmed) {
                const data = {
                    cedula_cliente: clienteSeleccionado.cedula,
                    productos: JSON.stringify(productosPendientes),
                    pagos: JSON.stringify(pagosActivos)
                };

                Ajax.post(window.ROUTES.ventas_guardar, data)
                    .done(function (res) {
                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: res.mensaje,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            limpiarFormulario();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                        }
                    }).fail(function (xhr) {
                        let msg = 'Error al procesar la venta.';
                        try {
                            const r = JSON.parse(xhr.responseText);
                            if (r.mensaje) msg = r.mensaje;
                        } catch (e) {}
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    });
            }
        });
    }

    /* ===== Limpiar Todo ===== */

    function limpiarFormulario() {
        clienteSeleccionado = null;
        productoSeleccionado = null;
        productosPendientes = [];
        pagosActivos = [];

        $('#select-cliente').val(null).trigger('change');
        $('#info-cliente').addClass('d-none');
        $('#select-producto').val(null).trigger('change');
        $('#info-producto').addClass('d-none');
        $('#cantidad').val(1);

        $('.metodo-pago-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
        $('#detalle-pagos').addClass('d-none');

        renderTablaProductos();
        actualizarPendiente();
    }

    function limpiarTodo() {
        Swal.fire({
            icon: 'warning',
            title: '¿Cancelar venta?',
            text: 'Se perderán todos los datos ingresados.',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (result.isConfirmed) {
                limpiarFormulario();
            }
        });
    }

    /* ===== Inicialización ===== */

    init();
});
