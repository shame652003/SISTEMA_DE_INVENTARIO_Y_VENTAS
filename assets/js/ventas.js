/**
 * ventas.js - Modulo de Ventas
 * Select2 + carrito dinamico + pagos multiples + calculos BCV
 */

$(function () {

    var tasaBcv = 0;
    var tasaActual = null;
    var tiposPago = [];
    var equiposCliente = [];
    var carrito = [];
    var pagos = [];
    var clienteSeleccionado = null;
    var esCredito = false;
    var monedaGlobal = 'USD';
    var imagenProductoActual = '';

    var formatoUsd = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var formatoVes = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    /* ===== Inicializacion ===== */

    function initConfig() {
        try {
            var data = JSON.parse($('#tiposPagoData').text());
            if (Array.isArray(data)) tiposPago = data;
        } catch (e) {}

        Ajax.post(window.ROUTES.ventas_config_inicial)
            .done(function (res) {
                if (res.ok) {
                    tasaBcv = parseFloat(res.tasa_bcv || 0);
                    tasaActual = res.tasa || null;
                    if (res.tiposPago && Array.isArray(res.tiposPago)) {
                        tiposPago = res.tiposPago;
                    }
                    if (res.equiposCliente && Array.isArray(res.equiposCliente)) {
                        equiposCliente = res.equiposCliente;
                        llenarTipoClienteModal();
                    }
                    var tasaFormateada = formatoVes.format(tasaBcv);
                    $('#tasa-bcv-display').text('Tasa BCV: Bs. ' + tasaFormateada);
                }
            });
    }

    /* ===== Helpers BCV ===== */

    function calcPrecioBcv(precioVes) {
        if (tasaBcv > 0) return precioVes / tasaBcv;
        return 0;
    }

    function calcVesDesdeUsd(precioUsd) {
        if (tasaBcv > 0) return precioUsd * tasaBcv;
        return 0;
    }

    function redistribuirPagos(indexEditado) {
        if (pagos.length < 2) return;
        var totalSegunMoneda = monedaGlobal === 'USD' ? getTotalUsdCarrito() : getTotalVesCarrito();
        var montoEditado = parseFloat(pagos[indexEditado].monto_recibido) || 0;
        var restante = totalSegunMoneda - montoEditado;
        if (restante < 0) restante = 0;

        var otrosCount = pagos.length - 1;
        if (otrosCount <= 0) return;
        var montoPorOtro = Math.floor((restante / otrosCount) * 100) / 100;
        var montoPrimerOtro = Math.round((restante - montoPorOtro * (otrosCount - 1)) * 100) / 100;

        var idxOtro = 0;
        for (var i = 0; i < pagos.length; i++) {
            if (i === indexEditado) continue;
            var monto = (idxOtro === 0) ? montoPrimerOtro : montoPorOtro;
            pagos[i].monto_recibido = monto;
            if (monedaGlobal === 'VES') {
                pagos[i].monto_equivalente = calcPrecioBcv(monto);
            }

            $('.input-monto-pago[data-index="' + i + '"]').val(monto.toFixed(2));
            if (monedaGlobal === 'VES') {
                var eq = pagos[i].monto_equivalente;
                $('.input-monto-equivalente-pago[data-index="' + i + '"]').val(eq ? eq.toFixed(2) : '');
            }
            idxOtro++;
        }

        for (var j = 0; j < pagos.length; j++) {
            validarMontoPago(j);
        }
    }

    function validarMontoPago(index) {
        var totalSegunMoneda = monedaGlobal === 'USD' ? getTotalUsdCarrito() : getTotalVesCarrito();
        var sumaTodos = 0;
        for (var i = 0; i < pagos.length; i++) {
            sumaTodos += parseFloat(pagos[i].monto_recibido) || 0;
        }
        var excedente = sumaTodos - totalSegunMoneda;

        var $montoInput = $('.input-monto-pago[data-index="' + index + '"]');
        var $eqInput = $('.input-monto-equivalente-pago[data-index="' + index + '"]');
        var $msgMonto = $montoInput.closest('.monto-col').find('.invalid-feedback-monto');
        var $msgEq = $eqInput.closest('.monto-col-eq').find('.invalid-feedback-monto');

        if (excedente > 0.005) {
            var signo = monedaGlobal === 'USD' ? '$' : 'Bs. ';
            var fmt = monedaGlobal === 'USD' ? formatoUsd : formatoVes;
            $montoInput.addClass('is-invalid');
            if ($eqInput.length) $eqInput.addClass('is-invalid');
            $msgMonto.text('Supera el total en ' + signo + fmt.format(excedente));
            if ($msgEq.length) $msgEq.text('Supera el total en ' + signo + fmt.format(excedente));
        } else {
            $montoInput.removeClass('is-invalid');
            if ($eqInput.length) $eqInput.removeClass('is-invalid');
            $msgMonto.text('');
            if ($msgEq.length) $msgEq.text('');
        }
    }

    /* ===== Select2 Clientes ===== */

    function initSelect2Cliente() {
        $('#select-cliente').select2({
            placeholder: 'Buscar cliente por cedula o nombre...',
            allowClear: true,
            minimumInputLength: 1,
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

    function initSelect2Producto() {
        $('#select-producto').select2({
            placeholder: 'Buscar producto por codigo o nombre...',
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

    /* ===== Eventos Cliente ===== */

    function mostrarInfoCliente(c) {
        clienteSeleccionado = { cedula: c.cedula, nombre: c.nombre + ' ' + (c.apellido || '') };
        $('#info-cliente-nombre').text(c.nombre + ' ' + (c.apellido || ''));
        $('#info-cliente-cedula').text(c.cedula);

        if (c.telefono) {
            $('#info-cliente-telefono-wrap').show();
            $('#info-cliente-telefono').text(c.telefono);
        } else {
            $('#info-cliente-telefono-wrap').hide();
        }

        if (c.correo) {
            $('#info-cliente-email-wrap').show();
            $('#info-cliente-email').text(c.correo);
        } else {
            $('#info-cliente-email-wrap').hide();
        }

        if (c.direccion) {
            $('#info-cliente-direccion-wrap').show();
            $('#info-cliente-direccion').text(c.direccion);
        } else {
            $('#info-cliente-direccion-wrap').hide();
        }

        var saldoUsd = parseFloat(c.saldo_deudor_usd || 0);
        var saldoBcv = parseFloat(c.saldo_deudor_bcv || 0);
        if (saldoUsd > 0 || saldoBcv > 0) {
            $('#info-cliente-credito').removeClass('d-none');
            $('#info-cliente-saldo-usd').text(saldoUsd.toFixed(2));
            $('#info-cliente-saldo-bcv').text(saldoBcv.toFixed(2));
        } else {
            $('#info-cliente-credito').addClass('d-none');
        }

        $('#info-cliente').removeClass('d-none').hide().fadeIn(300);
        actualizarBotonProcesar();
    }

    $('#select-cliente').on('select2:select', function (e) {
        var cliente = e.params.data;
        Ajax.post(window.ROUTES.ventas_cliente_info, { cedula: cliente.id })
            .done(function (res) {
                if (res.ok && res.data) {
                    mostrarInfoCliente(res.data);
                }
            });
    });

    $('#select-cliente').on('select2:clear', function () {
        clienteSeleccionado = null;
        $('#info-cliente').addClass('d-none');
        actualizarBotonProcesar();
    });

    /* ===== Modal Nuevo Cliente ===== */

    function llenarTipoClienteModal() {
        var $select = $('#nc-tipo-cliente');
        $select.empty().append('<option value="">Seleccionar...</option>');
        equiposCliente.forEach(function (eq) {
            $select.append('<option value="' + eq.idEquipoCliente + '">' + eq.tipo_equipo + '</option>');
        });
    }

    $('#btn-nuevo-cliente').on('click', function () {
        $('#form-nuevo-cliente')[0].reset();
        Validaciones.limpiarErrores('#form-nuevo-cliente');
        if (equiposCliente.length > 0) {
            llenarTipoClienteModal();
        }
        new bootstrap.Modal('#modal-nuevo-cliente').show();
    });

    $('#nc-nombre, #nc-apellido').on('blur', function () {
        Validaciones.formatearCapitalizacion(this);
    });

    $('#btn-guardar-nuevo-cliente').on('click', function () {
        Validaciones.limpiarErrores('#form-nuevo-cliente');

        if (!Validaciones.requerido('#nc-cedula', 'Cédula')) return;
        if (!Validaciones.cedula('#nc-cedula', 'Cédula')) return;
        if (!Validaciones.requerido('#nc-nombre', 'Nombre')) return;
        if (!Validaciones.soloLetras('#nc-nombre', 'Nombre')) return;
        if (!Validaciones.longitudMinima('#nc-nombre', 2, 'Nombre')) return;
        if (!Validaciones.requerido('#nc-apellido', 'Apellido')) return;
        if (!Validaciones.soloLetras('#nc-apellido', 'Apellido')) return;
        if (!Validaciones.longitudMinima('#nc-apellido', 2, 'Apellido')) return;
        if (!Validaciones.requerido('#nc-tipo-cliente', 'Tipo de Cliente')) return;

        Validaciones.formatearCapitalizacion('#nc-nombre');
        Validaciones.formatearCapitalizacion('#nc-apellido');

        var cedula = parseInt($('#nc-cedula').val()) || 0;
        var nombre = $('#nc-nombre').val().trim();
        var apellido = $('#nc-apellido').val().trim();
        var idEquipoCliente = parseInt($('#nc-tipo-cliente').val()) || 0;

        $('#btn-guardar-nuevo-cliente').prop('disabled', true);

        Ajax.post(window.ROUTES.ventas_cliente_registrar_rapido, {
            cedula: cedula,
            nombre: nombre,
            apellido: apellido,
            idEquipoCliente: idEquipoCliente
        }).done(function (res) {
            if (res.ok && res.data) {
                Swal.fire({ icon: 'success', title: 'Cliente registrado', text: res.mensaje, timer: 1500, showConfirmButton: false });
                bootstrap.Modal.getInstance('#modal-nuevo-cliente').hide();

                var c = res.data;
                var option = new Option(c.cedula + ' - ' + c.nombre + ' ' + (c.apellido || ''), c.cedula, true, true);
                $('#select-cliente').append(option).trigger('change');

                mostrarInfoCliente(c);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
            }
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al registrar el cliente.' });
        }).always(function () {
            $('#btn-guardar-nuevo-cliente').prop('disabled', false);
        });
    });

    /* ===== Eventos Producto ===== */

    $('#select-producto').on('select2:select', function (e) {
        var producto = e.params.data;
        $('#producto-id').val(producto.id);

        Ajax.post(window.ROUTES.ventas_producto_info, { id: producto.id })
            .done(function (res) {
                if (res.ok && res.data) {
                    var p = res.data;
                    var stock = parseFloat(p.stock) || 0;
                    var precioUsd = parseFloat(p.precio_venta_usd) || 0;
                    var precioVes = parseFloat(p.precio_venta_ves) || 0;
                    var costoUsd = parseFloat(p.precio_costo_usd) || 0;
                    var precioBcv = calcPrecioBcv(precioVes);

                    $('#producto-costo-usd').val(costoUsd);
                    $('#producto-precio-usd').val(precioUsd);
                    $('#producto-precio-ves').val(precioVes);

                    $('#info-producto-codigo').text(p.codigo || 'N/A');
                    $('#info-producto-nombre').text(p.nombre);
                    $('#info-producto-tipo').text(p.tipo_producto || 'N/A');
                    $('#info-producto-marca').text(p.marca || 'Sin marca');

                    if (p.imgproducto) {
                        imagenProductoActual = window.BASE_URL + '/' + p.imgproducto;
                        $('#img-producto-venta').attr('src', imagenProductoActual);
                        $('#img-producto-venta-container').removeClass('d-none');
                    } else {
                        imagenProductoActual = '';
                        $('#img-producto-venta-container').addClass('d-none');
                    }

                    var stockClass = 'text-success';
                    if (stock === 0) stockClass = 'text-danger';
                    else if (stock <= parseFloat(p.stock_minimo || 0)) stockClass = 'text-warning';
                    $('#info-producto-stock').html('<span class="' + stockClass + '">' + stock.toFixed(0) + '</span>');

                    $('#info-producto-precio-usd').text('$' + formatoUsd.format(precioUsd));
                    $('#info-producto-precio-ves').text('Bs. ' + formatoVes.format(precioVes));
                    $('#info-producto-precio-bcv').text('$' + formatoUsd.format(precioBcv));

                    $('#info-producto').removeClass('d-none').hide().fadeIn(300);
                    $('#form-carrito-container').removeClass('d-none').hide().fadeIn(400);

                    $('#cantidad').val('1').trigger('input');
                }
            });
    });

    $('#select-producto').on('select2:clear', function () {
        limpiarFormularioProducto();
    });

    $('#cantidad').on('input', function () {
        actualizarPreviewSubtotales();
    });

    $('#btn-limpiar-form').on('click', function () {
        limpiarFormularioProducto();
    });

    function limpiarFormularioProducto() {
        $('#form-carrito')[0].reset();
        $('#producto-id').val('');
        $('#producto-costo-usd').val('0');
        $('#producto-precio-usd').val('0');
        $('#producto-precio-ves').val('0');
        $('#preview-subtotal-usd').val('$0.00');
        $('#preview-subtotal-ves').val('Bs. 0.00');
        $('#preview-subtotal-bcv').val('$0.00');
        $('#info-producto').addClass('d-none');
        $('#form-carrito-container').addClass('d-none');
        $('#img-producto-venta-container').addClass('d-none');
        $('#img-producto-venta').attr('src', '');
        imagenProductoActual = '';
        $('#select-producto').val(null).trigger('change');
    }

    function actualizarPreviewSubtotales() {
        var cantidad = parseInt($('#cantidad').val()) || 0;
        var precioUsd = parseFloat($('#producto-precio-usd').val()) || 0;
        var precioVes = parseFloat($('#producto-precio-ves').val()) || 0;

        var subtotalUsd = cantidad * precioUsd;
        var subtotalVes = cantidad * precioVes;
        var subtotalBcv = calcPrecioBcv(subtotalVes);

        $('#preview-subtotal-usd').val('$' + formatoUsd.format(subtotalUsd));
        $('#preview-subtotal-ves').val('Bs. ' + formatoVes.format(subtotalVes));
        $('#preview-subtotal-bcv').val('$' + formatoUsd.format(subtotalBcv));
    }

    /* ===== Carrito ===== */

    $('#btn-agregar-carrito').on('click', function () {
        var idproducto = parseInt($('#producto-id').val()) || 0;
        var cantidad = parseInt($('#cantidad').val()) || 0;
        var costoUsd = parseFloat($('#producto-costo-usd').val()) || 0;
        var precioUsd = parseFloat($('#producto-precio-usd').val()) || 0;
        var precioVes = parseFloat($('#producto-precio-ves').val()) || 0;

        if (!idproducto) {
            Swal.fire({ icon: 'warning', title: 'Atencion', text: 'Selecciona un producto primero.' });
            return;
        }
        if (!cantidad || cantidad <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atencion', text: 'La cantidad debe ser mayor a 0.' });
            return;
        }

        var stockText = $('#info-producto-stock').text();
        var stock = parseInt(stockText) || 0;
        if (cantidad > stock) {
            Swal.fire({ icon: 'error', title: 'Stock insuficiente', text: 'Solo hay ' + stock + ' unidades disponibles.' });
            return;
        }

        var existente = carrito.findIndex(function (item) { return item.idproducto === idproducto; });
        if (existente >= 0) {
            var nuevaCantidad = carrito[existente].cantidad + cantidad;
            if (nuevaCantidad > stock) {
                Swal.fire({ icon: 'error', title: 'Stock insuficiente', text: 'Ya tienes ' + carrito[existente].cantidad + ' en el carrito. Solo hay ' + stock + ' disponibles.' });
                return;
            }
            carrito[existente].cantidad = nuevaCantidad;
        } else {
            carrito.push({
                idproducto: idproducto,
                codigo: $('#info-producto-codigo').text(),
                nombre: $('#info-producto-nombre').text(),
                cantidad: cantidad,
                costo_unitario_usd: costoUsd,
                precio_unitario_usd: precioUsd,
                precio_unitario_ves: precioVes,
                precio_unitario_bcv: calcPrecioBcv(precioVes),
                stock: stock,
                imgproducto: imagenProductoActual
            });
        }

        renderCarrito();
        limpiarFormularioProducto();
    });

    $(document).on('click', '.btn-eliminar-carrito', function () {
        var index = $(this).data('index');
        carrito.splice(index, 1);
        renderCarrito();
    });

    $(document).on('change', '.input-cantidad-carrito', function () {
        var index = $(this).data('index');
        var nuevaCantidad = parseInt($(this).val()) || 1;
        var item = carrito[index];
        var stock = parseInt(item.stock) || 0;

        if (nuevaCantidad <= 0) {
            nuevaCantidad = 1;
            $(this).val(1);
        }
        if (nuevaCantidad > stock) {
            Swal.fire({ icon: 'warning', title: 'Stock insuficiente', text: 'Solo hay ' + stock + ' unidades disponibles.', timer: 2000, showConfirmButton: false });
            $(this).val(item.cantidad);
            return;
        }

        carrito[index].cantidad = nuevaCantidad;
        renderCarrito();
    });

    function renderCarrito() {
        var $container = $('#carrito-container');
        $container.empty();

        if (carrito.length === 0) {
            pagos = [];
            renderPagos();
            $container.html('<p class="text-muted text-center mb-0 py-3" id="carrito-vacio">Sin productos en el carrito</p>');
            $('#carrito-footer').addClass('d-none');
            $('#carrito-contador').text('0');
            $('#btn-procesar-venta').prop('disabled', true);
            $('#btn-agregar-pago').prop('disabled', true);
            $('#total-usd').text('$0.00');
            $('#total-ves').text('Bs. 0.00');
            $('#total-bcv').text('$0.00');
        } else {
            var totalUsd = 0;
            var totalVes = 0;

            carrito.forEach(function (item, index) {
                var subtotalUsd = item.cantidad * item.precio_unitario_usd;
                var subtotalVes = item.cantidad * item.precio_unitario_ves;
                var subtotalBcv = calcPrecioBcv(subtotalVes);
                var precioBcv = item.precio_unitario_bcv || calcPrecioBcv(item.precio_unitario_ves);
                totalUsd += subtotalUsd;
                totalVes += subtotalVes;

                var imgTag = item.imgproducto
                    ? '<div class="me-3 flex-shrink-0" style="width:65px; height:65px;"><img src="' + item.imgproducto + '" alt="" class="rounded border" style="width:100%; height:100%; object-fit:contain;"></div>'
                    : '';

                var $card = $(
                    '<div class="border rounded-3 p-3 mb-2 bg-white shadow-sm">' +
                        '<div class="d-flex align-items-start">' +
                            imgTag +
                            '<div class="flex-grow-1">' +
                                '<div class="d-flex align-items-start justify-content-between mb-2">' +
                                    '<div class="flex-grow-1">' +
                                        '<span class="badge bg-secondary me-2">' + (index + 1) + '</span>' +
                                        '<strong>' + item.codigo + '</strong>' +
                                        '<span class="text-muted mx-1">—</span>' +
                                        '<span>' + item.nombre + '</span>' +
                                    '</div>' +
                                    '<button class="btn btn-sm btn-outline-danger btn-eliminar-carrito ms-2 flex-shrink-0" data-index="' + index + '" title="Eliminar">' +
                                        '<i class="bi bi-trash"></i>' +
                                    '</button>' +
                                '</div>' +
                                '<div class="mb-2">' +
                                    '<span class="text-muted me-1">P.U.:</span>' +
                                    '<span class="text-success">$' + formatoUsd.format(item.precio_unitario_usd) + '</span>' +
                                    ' &nbsp;|&nbsp; ' +
                                    '<span class="text-dark fw-semibold">Bs. ' + formatoVes.format(item.precio_unitario_ves) + '</span>' +
                                    ' &nbsp;|&nbsp; ' +
                                    '<span class="text-info">$' + formatoUsd.format(precioBcv) + '</span>' +
                                '</div>' +
                                '<div class="row align-items-center g-2">' +
                                    '<div class="col-auto">' +
                                        '<label class="form-label small mb-0">Cant:</label>' +
                                    '</div>' +
                                    '<div class="col-auto" style="width:70px">' +
                                        '<input type="number" class="form-control form-control-sm input-cantidad-carrito" data-index="' + index + '" value="' + item.cantidad + '" step="1" min="1">' +
                                    '</div>' +
                                    '<div class="col text-end">' +
                                        '<span><span class="text-muted">Sub. USD</span> <strong class="text-success">$' + formatoUsd.format(subtotalUsd) + '</strong></span>' +
                                        ' &nbsp;|&nbsp; ' +
                                        '<span><span class="text-muted">Sub. VES</span> <strong class="text-dark">Bs. ' + formatoVes.format(subtotalVes) + '</strong></span>' +
                                        ' &nbsp;|&nbsp; ' +
                                        '<span><span class="text-muted">Sub. BCV</span> <strong class="text-info">$' + formatoUsd.format(subtotalBcv) + '</strong></span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );

                $container.append($card);
            });

            var totalBcv = calcPrecioBcv(totalVes);

            $('#carrito-footer').removeClass('d-none');
            $('#total-usd').text('$' + formatoUsd.format(totalUsd));
            $('#total-ves').text('Bs. ' + formatoVes.format(totalVes));
            $('#total-bcv').text('$' + formatoUsd.format(totalBcv));
            $('#carrito-contador').text(carrito.length);

            $('#btn-agregar-pago').prop('disabled', false);
            actualizarBotonProcesar();
        }

        actualizarResumenPagos();
    }

    function getTotalUsdCarrito() {
        return carrito.reduce(function (sum, item) {
            return sum + (item.cantidad * item.precio_unitario_usd);
        }, 0);
    }

    function getTotalVesCarrito() {
        return carrito.reduce(function (sum, item) {
            return sum + (item.cantidad * item.precio_unitario_ves);
        }, 0);
    }

    /* ===== Pagos ===== */

    function tiposPagoPorMoneda() {
        var usdPermitidos = ['binance usdt', 'credito', 'efectivo', 'zelle'];
        var vesPermitidos = ['punto', 'transferencia', 'efectivo', 'biopago', 'credito'];
        var permitidos = monedaGlobal === 'USD' ? usdPermitidos : vesPermitidos;
        return tiposPago.filter(function (tp) {
            return permitidos.indexOf(tp.tipoPago.toLowerCase()) >= 0;
        });
    }

    /* --- Moneda Global --- */

    $('#moneda-usd, #moneda-ves').on('change', function () {
        if (!this.checked) return;
        var nueva = this.value;
        if (nueva === monedaGlobal) return;

        if (pagos.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cambiar moneda',
                text: 'Al cambiar la moneda se limpiaran los pagos configurados.',
                showCancelButton: true,
                confirmButtonText: 'Si, cambiar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) {
                    monedaGlobal = nueva;
                    pagos = [];
                    renderPagos();
                } else {
                    $('#' + (monedaGlobal === 'USD' ? 'moneda-usd' : 'moneda-ves')).prop('checked', true);
                }
            });
        } else {
            monedaGlobal = nueva;
            actualizarResumenPagos();
        }
    });

    $('#btn-agregar-pago').on('click', function () {
        if (pagos.length >= 3) {
            Swal.fire({ icon: 'warning', title: 'Atencion', text: 'Maximo 3 metodos de pago por venta.' });
            return;
        }
        agregarFilaPago();
    });

    $(document).on('click', '.btn-eliminar-pago', function () {
        var index = $(this).data('index');
        pagos.splice(index, 1);
        renderPagos();
    });

    $(document).on('change', '.radio-tipo-pago', function () {
        var index = $(this).data('index');
        var idTipo = parseInt($(this).val()) || 0;
        if (pagos[index]) {
            pagos[index].idtipo_de_pagos = idTipo;
        }
        verificarCredito();
        renderPagos();
        actualizarResumenPagos();
    });

    $(document).on('input', '.input-monto-pago', function () {
        var index = $(this).data('index');
        var monto = parseFloat($(this).val()) || 0;
        if (pagos[index]) {
            pagos[index].monto_recibido = monto;
            if (monedaGlobal === 'VES') {
                pagos[index].monto_equivalente = calcPrecioBcv(monto);
            }
        }
        if (monedaGlobal === 'VES') {
            var eqVal = pagos[index] ? pagos[index].monto_equivalente : 0;
            $('.input-monto-equivalente-pago[data-index="' + index + '"]').val(eqVal ? eqVal.toFixed(2) : '');
        }
        validarMontoPago(index);
        redistribuirPagos(index);
        actualizarResumenPagos();
    });

    $(document).on('input', '.input-monto-equivalente-pago', function () {
        var index = $(this).data('index');
        var montoEq = parseFloat($(this).val()) || 0;
        var montoPrincipal = tasaBcv > 0 ? parseFloat((montoEq * tasaBcv).toFixed(2)) : 0;

        if (pagos[index]) {
            pagos[index].monto_equivalente = montoEq;
            pagos[index].monto_recibido = montoPrincipal;
        }
        $('.input-monto-pago[data-index="' + index + '"]').val(montoPrincipal.toFixed(2));

        validarMontoPago(index);
        redistribuirPagos(index);
        actualizarResumenPagos();
    });

    $(document).on('blur', '.input-monto-pago, .input-monto-equivalente-pago', function () {
        var val = parseFloat($(this).val());
        if (!isNaN(val)) {
            $(this).val(val.toFixed(2));
        }
    });

    $(document).on('input', '.input-referencia-pago', function () {
        var index = $(this).data('index');
        if (pagos[index]) {
            pagos[index].referencia = $(this).val();
        }
    });

    $(document).on('change', '.toggle-referencia', function () {
        var index = $(this).data('index');
        var $row = $(this).closest('.pago-fila');
        var $refRow = $row.find('.referencia-row');
        var $refInput = $refRow.find('.input-referencia-pago');

        if ($(this).is(':checked')) {
            $refRow.slideDown(150);
            setTimeout(function () { $refInput.focus(); }, 200);
        } else {
            $refRow.slideUp(150);
            if (pagos[index]) pagos[index].referencia = '';
            $refInput.val('');
        }
    });

    function agregarFilaPago() {
        var totalSegunMoneda = monedaGlobal === 'USD' ? getTotalUsdCarrito() : getTotalVesCarrito();
        var tiposFiltrados = tiposPagoPorMoneda();

        var idsUsados = pagos.map(function (p) { return p.idtipo_de_pagos; });
        var primerDisponible = 0;
        for (var k = 0; k < tiposFiltrados.length; k++) {
            if (idsUsados.indexOf(tiposFiltrados[k].idtipo_de_pagos) < 0) {
                primerDisponible = tiposFiltrados[k].idtipo_de_pagos;
                break;
            }
        }

        pagos.push({
            idtipo_de_pagos: primerDisponible,
            moneda: monedaGlobal,
            monto_recibido: 0,
            monto_equivalente: 0,
            referencia: ''
        });

        if (pagos.length > 1 && totalSegunMoneda > 0) {
            var montoBase = Math.round((totalSegunMoneda / pagos.length) * 100) / 100;
            var montoPrimeros = Math.round((totalSegunMoneda - montoBase * (pagos.length - 1)) * 100) / 100;
            for (var i = 0; i < pagos.length; i++) {
                var monto = (i === 0) ? montoPrimeros : montoBase;
                pagos[i].monto_recibido = monto;
                pagos[i].monto_equivalente = monedaGlobal === 'USD' ? calcVesDesdeUsd(monto) : calcPrecioBcv(monto);
            }
        }

        renderPagos();
    }

    function renderPagos() {
        var $container = $('#pagos-container');
        $container.empty();

        if (pagos.length === 0) {
            $('#pagos-vacio').removeClass('d-none');
            $('#resumen-pagos').addClass('d-none');
            actualizarBotonProcesar();
            return;
        }

        $('#pagos-vacio').addClass('d-none');

        var esUnSoloPago = pagos.length === 1;
        var totalSegunMoneda = monedaGlobal === 'USD' ? getTotalUsdCarrito() : getTotalVesCarrito();
        var esModoVes = monedaGlobal === 'VES';

        if (esUnSoloPago && totalSegunMoneda > 0) {
            pagos[0].monto_recibido = totalSegunMoneda;
            if (esModoVes) {
                pagos[0].monto_equivalente = calcPrecioBcv(totalSegunMoneda);
            }
        }

        pagos.forEach(function (pago, index) {
            var $row = $('<div class="mb-3 pago-fila border rounded p-3 bg-light" data-index="' + index + '"></div>');

            var $btnGroup = $('<div class="btn-group btn-group-sm d-flex flex-wrap w-100" role="group"></div>');
            var tiposFiltrados = tiposPagoPorMoneda();
            tiposFiltrados.forEach(function (tp) {
                var esCredito = tp.tipoPago.toLowerCase() === 'credito';
                var radioId = 'pago-tipo-' + index + '-' + tp.idtipo_de_pagos;
                var checked = pago.idtipo_de_pagos == tp.idtipo_de_pagos ? ' checked' : '';
                var btnClass = esCredito ? 'btn-outline-danger' : 'btn-outline-primary';

                var usadoEnOtro = pagos.some(function (p, idx) {
                    return idx !== index && p.idtipo_de_pagos == tp.idtipo_de_pagos;
                });
                var disabledAttr = usadoEnOtro ? ' disabled' : '';
                var disabledClass = usadoEnOtro ? ' opacity-50 pe-none' : '';

                $btnGroup.append(
                    '<input type="radio" class="btn-check radio-tipo-pago" name="pago-tipo-' + index + '" id="' + radioId + '" value="' + tp.idtipo_de_pagos + '" data-index="' + index + '"' + checked + disabledAttr + '>' +
                    '<label class="btn btn-sm ' + btnClass + disabledClass + '" for="' + radioId + '">' + tp.tipoPago + '</label>'
                );
            });

            var readonlyAttr = esUnSoloPago ? ' readonly' : '';
            var readonlyClass = esUnSoloPago ? ' bg-light' : '';

            var mostrarAutoBadge = pagos.length > 1;
            var autoBadge = mostrarAutoBadge ? ' <span class="badge bg-info ms-1 auto-badge">Auto</span>' : '';

            var tieneRef = pago.referencia && pago.referencia.length > 0;
            var refChecked = tieneRef ? ' checked' : '';
            var refRowDisplay = tieneRef ? '' : ' style="display:none"';

            if (esModoVes) {
                var $filaMontos = $(
                    '<div class="row g-2 mt-2 align-items-end">' +
                        '<div class="col-md-5 monto-col">' +
                            '<label class="form-label small mb-1">Monto VES' + autoBadge + '</label>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text">Bs.</span>' +
                                '<input type="number" class="form-control input-monto-pago' + readonlyClass + '" data-index="' + index + '" value="' + (pago.monto_recibido ? pago.monto_recibido.toFixed(2) : '') + '" step="0.01" min="0" placeholder="0.00"' + readonlyAttr + '>' +
                            '</div>' +
                            '<div class="invalid-feedback-monto text-danger small mt-1"></div>' +
                        '</div>' +
                        '<div class="col-md-3 monto-col-eq">' +
                            '<label class="form-label small mb-1">≈ BCV</label>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text">$</span>' +
                                '<input type="number" class="form-control input-monto-equivalente-pago' + readonlyClass + '" data-index="' + index + '" value="' + (pago.monto_equivalente ? pago.monto_equivalente.toFixed(2) : '') + '" step="0.01" min="0" placeholder="0.00"' + readonlyAttr + '>' +
                            '</div>' +
                            '<div class="invalid-feedback-monto text-danger small mt-1"></div>' +
                        '</div>' +
                        '<div class="col-md-2 d-flex align-items-end gap-2">' +
                            '<div class="form-check mb-2">' +
                                '<input type="checkbox" class="form-check-input toggle-referencia" data-index="' + index + '"' + refChecked + '>' +
                                '<label class="form-check-label small">Ref.</label>' +
                            '</div>' +
                            '<button class="btn btn-sm btn-outline-danger btn-eliminar-pago mb-2" data-index="' + index + '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="row mt-1 referencia-row"' + refRowDisplay + '>' +
                        '<div class="col-md-8">' +
                            '<input type="text" class="form-control form-control-sm input-referencia-pago" data-index="' + index + '" value="' + (pago.referencia || '') + '" placeholder="N° de referencia" maxlength="100">' +
                        '</div>' +
                    '</div>'
                );
            } else {
                var $filaMontos = $(
                    '<div class="row g-2 mt-2 align-items-end">' +
                        '<div class="col-md-7 monto-col">' +
                            '<label class="form-label small mb-1">Monto USD' + autoBadge + '</label>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text">$</span>' +
                                '<input type="number" class="form-control input-monto-pago' + readonlyClass + '" data-index="' + index + '" value="' + (pago.monto_recibido ? pago.monto_recibido.toFixed(2) : '') + '" step="0.01" min="0" placeholder="0.00"' + readonlyAttr + '>' +
                            '</div>' +
                            '<div class="invalid-feedback-monto text-danger small mt-1"></div>' +
                        '</div>' +
                        '<div class="col-md-3 d-flex align-items-end gap-2">' +
                            '<div class="form-check mb-2">' +
                                '<input type="checkbox" class="form-check-input toggle-referencia" data-index="' + index + '"' + refChecked + '>' +
                                '<label class="form-check-label small">Ref.</label>' +
                            '</div>' +
                            '<button class="btn btn-sm btn-outline-danger btn-eliminar-pago mb-2" data-index="' + index + '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="row mt-1 referencia-row"' + refRowDisplay + '>' +
                        '<div class="col-md-7">' +
                            '<input type="text" class="form-control form-control-sm input-referencia-pago" data-index="' + index + '" value="' + (pago.referencia || '') + '" placeholder="N° de referencia" maxlength="100">' +
                        '</div>' +
                    '</div>'
                );
            }

            $row.append($btnGroup).append($filaMontos);
            $container.append($row);
        });

        $('#resumen-pagos').removeClass('d-none');
        verificarCredito();
        actualizarResumenPagos();
    }

    function verificarCredito() {
        esCredito = pagos.some(function (p) {
            if (!p.idtipo_de_pagos) return false;
            var found = tiposPago.find(function (tp) { return tp.idtipo_de_pagos == p.idtipo_de_pagos; });
            return found && found.tipoPago.toLowerCase() === 'credito';
        });
        actualizarBotonProcesar();
    }

    function actualizarResumenPagos() {
        if (pagos.length === 0) {
            $('#resumen-pagos').addClass('d-none');
            actualizarBotonProcesar();
            return;
        }

        $('#resumen-pagos').removeClass('d-none');

        var totalVenta, totalPagado;

        if (monedaGlobal === 'USD') {
            totalVenta = getTotalUsdCarrito();
            $('#resumen-label-total').text('Total Venta USD:');
            $('#resumen-total-bcv-container').addClass('d-none');
            totalPagado = pagos.reduce(function (sum, p) { return sum + (parseFloat(p.monto_recibido) || 0); }, 0);
            var pendiente = totalVenta - totalPagado;
            $('#resumen-total').text('$' + formatoUsd.format(totalVenta));
            $('#resumen-pagado').text('$' + formatoUsd.format(totalPagado));
            if (pendiente < -0.01) {
                $('#resumen-pendiente').text('-$' + formatoUsd.format(Math.abs(pendiente)) + ' (excedente)').removeClass('text-danger').addClass('text-warning');
            } else if (Math.abs(pendiente) < 0.01) {
                $('#resumen-pendiente').text('$0.00').removeClass('text-danger text-warning');
            } else {
                $('#resumen-pendiente').text('$' + formatoUsd.format(pendiente)).removeClass('text-warning').addClass('text-danger');
            }
        } else {
            totalVenta = getTotalVesCarrito();
            var bcvEq = calcPrecioBcv(totalVenta);
            $('#resumen-label-total').text('Total Venta VES:');
            $('#resumen-total').text('Bs. ' + formatoVes.format(totalVenta));
            $('#resumen-total-bcv').text('$' + formatoUsd.format(bcvEq));
            $('#resumen-total-bcv-container').removeClass('d-none');
            totalPagado = pagos.reduce(function (sum, p) { return sum + (parseFloat(p.monto_recibido) || 0); }, 0);
            var pendienteVes = totalVenta - totalPagado;
            $('#resumen-pagado').text('Bs. ' + formatoVes.format(totalPagado));
            if (pendienteVes < -0.01) {
                $('#resumen-pendiente').text('-Bs. ' + formatoVes.format(Math.abs(pendienteVes)) + ' (excedente)').removeClass('text-danger').addClass('text-warning');
            } else if (Math.abs(pendienteVes) < 0.01) {
                $('#resumen-pendiente').text('Bs. 0.00').removeClass('text-danger text-warning');
            } else {
                $('#resumen-pendiente').text('Bs. ' + formatoVes.format(pendienteVes)).removeClass('text-warning').addClass('text-danger');
            }
        }

        actualizarBotonProcesar();
    }

    function actualizarBotonProcesar() {
        var tieneCliente = !!clienteSeleccionado;
        var tieneProductos = carrito.length > 0;
        var pagosCompletos = false;

        if (pagos.length > 0) {
            var totalVenta = monedaGlobal === 'USD' ? getTotalUsdCarrito() : getTotalVesCarrito();
            var totalPagado = 0;
            var todosConMonto = true;

            pagos.forEach(function (p) {
                var monto = parseFloat(p.monto_recibido) || 0;
                if (monto <= 0) todosConMonto = false;
                if (p.idtipo_de_pagos <= 0) todosConMonto = false;
                totalPagado += monto;
            });

            pagosCompletos = todosConMonto && Math.abs(totalPagado - totalVenta) < 0.01;
        }

        var habilitar = tieneCliente && tieneProductos && pagosCompletos;
        $('#btn-procesar-venta').prop('disabled', !habilitar);

        if (pagos.length > 0 && carrito.length === 0) {
            $('#btn-procesar-venta').prop('disabled', true);
        }
    }

    /* ===== Cancelar Compra ===== */

    $('#btn-cancelar-compra').on('click', function () {
        if (carrito.length === 0 && pagos.length === 0 && !clienteSeleccionado) {
            Swal.fire({ icon: 'info', title: 'Sin cambios', text: 'No hay nada que cancelar.', timer: 1500, showConfirmButton: false });
            return;
        }
        Swal.fire({
            icon: 'warning',
            title: 'Cancelar compra?',
            text: 'Se limpiara el carrito, los pagos y el cliente seleccionado.',
            showCancelButton: true,
            confirmButtonText: 'Si, cancelar',
            cancelButtonText: 'No',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (result.isConfirmed) {
                reiniciarVenta();
                Swal.fire({ icon: 'success', title: 'Compra cancelada', text: 'Se ha limpiado todo.', timer: 1500, showConfirmButton: false });
            }
        });
    });

    /* ===== Procesar Venta ===== */

    $('#btn-procesar-venta').on('click', function () {
        if (carrito.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atencion', text: 'El carrito esta vacio.' });
            return;
        }
        if (!clienteSeleccionado) {
            Swal.fire({ icon: 'warning', title: 'Atencion', text: 'Debe seleccionar un cliente.' });
            return;
        }
        if (pagos.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atencion', text: 'Debe agregar al menos un metodo de pago.' });
            return;
        }

        var totalVentaUsd = getTotalUsdCarrito();
        var totalVentaVes = getTotalVesCarrito();

        if (monedaGlobal === 'USD') {
            var totalPagado = pagos.reduce(function (sum, p) { return sum + (parseFloat(p.monto_recibido) || 0); }, 0);
            if (Math.abs(totalPagado - totalVentaUsd) > 0.5) {
                Swal.fire({ icon: 'warning', title: 'Atencion', text: 'La suma de los pagos debe ser igual al total de la venta.' });
                return;
            }
        } else {
            var totalPagadoVes = pagos.reduce(function (sum, p) { return sum + (parseFloat(p.monto_recibido) || 0); }, 0);
            if (Math.abs(totalPagadoVes - totalVentaVes) > 0.5) {
                Swal.fire({ icon: 'warning', title: 'Atencion', text: 'La suma de los pagos debe ser igual al total de la venta.' });
                return;
            }
        }

        var mensajeConfirm = 'Cliente: ' + clienteSeleccionado.nombre + '\n' +
            'Productos: ' + carrito.length + '\n' +
            'Total: ' + (monedaGlobal === 'USD' ? '$' + formatoUsd.format(totalVentaUsd) + ' USD' : 'Bs. ' + formatoVes.format(totalVentaVes) + ' VES');


        if (esCredito) {
            mensajeConfirm += '\n\nATENCION: Esta venta incluye pago a CREDITO.';
        }

        var $btn = $(this);
        $btn.prop('disabled', true);

        Swal.fire({
            icon: 'question',
            title: 'Procesar venta?',
            text: mensajeConfirm,
            showCancelButton: true,
            confirmButtonText: 'Si, procesar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#198754',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve) {
                    var data = {
                        cedula_cliente: clienteSeleccionado.cedula,
                        items: JSON.stringify(carrito.map(function (item) {
                            return {
                                idproducto: item.idproducto,
                                cantidad: item.cantidad,
                                costo_unitario_usd: item.costo_unitario_usd,
                                precio_unitario_usd: item.precio_unitario_usd,
                                precio_unitario_ves: item.precio_unitario_ves,
                                precio_unitario_bcv: item.precio_unitario_bcv || calcPrecioBcv(item.precio_unitario_ves)
                            };
                        })),
                        pagos: JSON.stringify(pagos.map(function (p) {
                            var monto = parseFloat(p.monto_recibido) || 0;
                            var bcv = p.moneda === 'USD' ? monto : (tasaBcv > 0 ? parseFloat((monto / tasaBcv).toFixed(2)) : 0);
                            return {
                                idtipo_de_pagos: p.idtipo_de_pagos,
                                moneda: p.moneda,
                                monto_recibido: monto,
                                monto_bcv: bcv,
                                referencia: p.referencia || null
                            };
                        })),
                        total_usd: totalVentaUsd,
                        total_ves: totalVentaVes,
                        idTasa: tasaActual ? tasaActual.idTasa : 0
                    };

                    Ajax.post(window.ROUTES.ventas_guardar, data)
                        .done(function (res) { resolve(res); })
                        .fail(function (xhr) {
                            var msg = 'Error al procesar la venta.';
                            try {
                                var r = JSON.parse(xhr.responseText);
                                if (r.mensaje) msg = r.mensaje;
                            } catch (e) {}
                            resolve({ ok: false, mensaje: msg });
                        });
                });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); }
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                var res = result.value;
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Venta procesada',
                        text: res.mensaje,
                        timer: 2500,
                        showConfirmButton: false
                    });
                    reiniciarVenta();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                    actualizarBotonProcesar();
                }
            } else {
                actualizarBotonProcesar();
            }
        });
    });

    function reiniciarVenta() {
        carrito = [];
        pagos = [];
        clienteSeleccionado = null;
        esCredito = false;
        monedaGlobal = 'USD';
        imagenProductoActual = '';
        $('#moneda-usd').prop('checked', true);
        renderCarrito();
        renderPagos();
        $('#select-cliente').val(null).trigger('change');
        $('#select-producto').val(null).trigger('change');
        $('#info-cliente').addClass('d-none');
        $('#info-producto').addClass('d-none');
        $('#form-carrito-container').addClass('d-none');
        $('#img-producto-venta-container').addClass('d-none');
        $('#img-producto-venta').attr('src', '');
        $('#pagos-container').empty();
        $('#pagos-vacio').removeClass('d-none');
        $('#resumen-pagos').addClass('d-none');
        $('#btn-procesar-venta').prop('disabled', true);
        $('#btn-agregar-pago').prop('disabled', true);
    }

    /* ===== Inicializacion ===== */

    initConfig();
    initSelect2Cliente();
    initSelect2Producto();
});
