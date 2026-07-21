/**
 * clientes.js - CRUD de Clientes con DataTables server-side y validaciones en tiempo real
 */

$(function () {

    const modalCliente = new bootstrap.Modal(document.getElementById('modal-cliente'));

    let tablaClientes = null;

    // ═══════════════════════════════════════════════════════
    // VALIDACIONES EN TIEMPO REAL
    // ═══════════════════════════════════════════════════════

    function validarCampo($input) {
        const selector = '#' + $input.attr('id');
        const tipo = $input.attr('type');
        const nombre = $input.attr('placeholder') || $input.attr('name') || 'Este campo';
        let valido = true;

        if ($input.prop('required') && !Validaciones.requerido(selector, nombre)) {
            valido = false;
        }

        if (tipo === 'email' && valido) {
            if (!Validaciones.email(selector)) valido = false;
        }

        if (tipo === 'number' && valido) {
            if (!Validaciones.numero(selector, nombre)) valido = false;
        }

        return valido;
    }

    $('#modal-cliente input, #modal-cliente select, #modal-cliente textarea').on('blur', function () {
        validarCampo($(this));
    });

    $('#modal-cliente input, #modal-cliente select, #modal-cliente textarea').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    var cedulaDebounce = null;
    $('#cliente-cedula').on('blur', function () {
        var $this = $(this);
        Validaciones.cedula('#cliente-cedula', 'Cédula');
        if (!$this.prop('readonly')) {
            if (cedulaDebounce) clearTimeout(cedulaDebounce);
            cedulaDebounce = setTimeout(function () {
                Validaciones.cedulaUnica('#cliente-cedula', window.ROUTES.clientes_verificar_cedula, 'Cédula');
            }, 300);
        }
    });

    $('#cliente-nombre, #cliente-segNombre, #cliente-apellido, #cliente-segApellido').on('blur', function () {
        Validaciones.soloLetras(this, $(this).attr('placeholder') || 'Este campo');
        Validaciones.formatearCapitalizacion(this);
    });

    $('#cliente-telefono').on('blur', function () {
        Validaciones.telefono('#cliente-telefono', 'Teléfono');
    });

    $('#cliente-equipo').on('change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    $('#modal-cliente').on('shown.bs.modal', function () {
        Validaciones.limpiarErrores('#form-cliente');
        cedulaDebounce = null;
    });

    // ═══════════════════════════════════════════════════════
    // DATATABLE SERVER-SIDE
    // ═══════════════════════════════════════════════════════

    function initDataTable() {
        tablaClientes = $('#tabla-clientes').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.BASE_URL + window.ROUTES.clientes_listar,
                type: 'POST'
            },
            columns: [
                { data: 'cedula' },
                {
                    data: 'nombre',
                    render: function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            var partes = [row.nombre, row.segNombre, row.apellido, row.segApellido].filter(Boolean);
                            return '<div class="fw-semibold">' + partes.join(' ') + '</div>';
                        }
                        return data;
                    }
                },
                {
                    data: 'telefono',
                    render: function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            return data || '—';
                        }
                        return data;
                    }
                },
                {
                    data: 'correo',
                    render: function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            return data || '—';
                        }
                        return data;
                    }
                },
                {
                    data: 'tipo_equipo',
                    render: function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            return '<span class="badge bg-info bg-opacity-10 text-info px-2 py-1">' + (data || 'N/A') + '</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'status',
                    render: function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            if (parseInt(data) === 1) {
                                return '<span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Activo</span>';
                            }
                            return '<span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">Inactivo</span>';
                        }
                        return data;
                    }
                },
                {
                    data: 'cedula',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            var partes = [row.nombre, row.segNombre, row.apellido, row.segApellido].filter(Boolean);
                            var nombreCompleto = partes.join(' ');
                            return '<div class="text-end">' +
                                '<button class="btn btn-sm btn-outline-primary btn-editar-cliente" data-cedula="' + data + '" title="Editar">' +
                                    '<i class="bi bi-pencil"></i>' +
                                '</button>' +
                                '<button class="btn btn-sm btn-outline-danger btn-eliminar-cliente" data-cedula="' + data + '" data-nombre="' + nombreCompleto + '" title="Eliminar">' +
                                    '<i class="bi bi-trash"></i>' +
                                '</button>' +
                            '</div>';
                        }
                        return data;
                    },
                    orderable: false,
                    searchable: false
                }
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
    }

    // ═══════════════════════════════════════════════════════
    // CARGAR EQUIPOS
    // ═══════════════════════════════════════════════════════

    function cargarEquipos() {
        Ajax.post(window.ROUTES.clientes_equipos_listar)
            .done(function (res) {
                const data = res.data || [];
                let opciones = '<option value="">Seleccione un tipo</option>';
                data.forEach(function (e) {
                    opciones += '<option value="' + e.idEquipoCliente + '">' + e.tipo_equipo + '</option>';
                });
                $('#cliente-equipo').html(opciones);
                $('#cliente-equipo').select2({
                    dropdownParent: $('#modal-cliente'),
                    placeholder: 'Seleccione un tipo',
                    allowClear: true,
                    width: '100%'
                });
            });
    }

    // ═══════════════════════════════════════════════════════
    // CLIENTES
    // ═══════════════════════════════════════════════════════

    $('#btn-nuevo').on('click', function () {
        $('#form-cliente')[0].reset();
        $('#cliente-id').val('');
        $('#cliente-equipo').val('').trigger('change');
        $('#titulo-modal-cliente').html('<i class="bi bi-person-plus me-2 text-primary"></i>Nuevo Cliente');
        $('#cliente-cedula').prop('readonly', false);
    });

    $(document).on('click', '.btn-editar-cliente', function () {
        const cedula = $(this).data('cedula');
        Ajax.post(window.ROUTES.clientes_obtener, { cedula: cedula })
            .done(function (res) {
                if (!res.ok || !res.data) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Cliente no encontrado' });
                    }
                    return;
                }
                const c = res.data;
                $('#cliente-id').val(c.cedula);
                $('#cliente-cedula').val(c.cedula).prop('readonly', true);
                $('#cliente-nombre').val(c.nombre);
                $('#cliente-segNombre').val(c.segNombre || '');
                $('#cliente-apellido').val(c.apellido);
                $('#cliente-segApellido').val(c.segApellido || '');
                $('#cliente-direccion').val(c.direccion || '');
                $('#cliente-correo').val(c.correo || '');
                $('#cliente-telefono').val(c.telefono || '');
                $('#cliente-equipo').val(c.idEquipoCliente).trigger('change');
                $('#cliente-status').val(c.status);
                $('#titulo-modal-cliente').html('<i class="bi bi-pencil me-2 text-primary"></i>Editar Cliente');
                modalCliente.show();
            });
    });

    $(document).on('click', '.btn-eliminar-cliente', function () {
        const cedula = $(this).data('cedula');
        const nombre = $(this).data('nombre');

        Ajax.post(window.ROUTES.clientes_verificar_ventas, { cedula: cedula })
            .done(function (res) {
                if (res.ok && res.enUso) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cliente con ventas',
                            text: 'El cliente "' + nombre + '" tiene ' + res.count + ' venta(s) registrada(s) y no puede ser eliminado.'
                        });
                    }
                    return;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: '¿Eliminar cliente?',
                        text: '¿Estás seguro de eliminar a ' + nombre + '?',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#dc3545'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            Ajax.post(window.ROUTES.clientes_eliminar, { cedula: cedula })
                                .done(function (res) {
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({ icon: 'success', title: 'Éxito', text: (res && res.mensaje) || 'Eliminado correctamente.', timer: 2500, showConfirmButton: false });
                                    }
                                    tablaClientes.ajax.reload(null, false);
                                });
                        }
                    });
                } else {
                    if (confirm('¿Estás seguro de eliminar a ' + nombre + '?')) {
                        Ajax.post(window.ROUTES.clientes_eliminar, { cedula: cedula })
                            .done(function (res) {
                                tablaClientes.ajax.reload(null, false);
                            });
                    }
                }
            });
    });

    $('#form-cliente').on('submit', function (e) {
        e.preventDefault();
        Validaciones.limpiarErrores('#form-cliente');
        if (!Validaciones.validarFormulario('#form-cliente')) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Complete todos los campos obligatorios.', timer: 3000, showConfirmButton: true });
            }
            return;
        }

        Ajax.post(window.ROUTES.clientes_guardar, $(this).serialize())
            .done(function (res) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Éxito', text: (res && res.mensaje) || 'Operación completada.', timer: 2500, showConfirmButton: false });
                }
                modalCliente.hide();
                tablaClientes.ajax.reload(null, false);
            }).fail(function (xhr) {
                var msg = 'Error al guardar cliente.';
                try { var r = JSON.parse(xhr.responseText); if (r && r.mensaje) msg = r.mensaje; } catch (e) {}
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                } else {
                    alert(msg);
                }
            });
    });

    // ═══════════════════════════════════════════════════════
    // INICIALIZAR
    // ═══════════════════════════════════════════════════════

    initDataTable();
    cargarEquipos();

});
