/**
 * usuarios.js - CRUD de Usuarios y Roles con DataTables server-side y validaciones en tiempo real
 */

$(function () {

    const modalUsuario = new bootstrap.Modal(document.getElementById('modal-usuario'));
    const modalRol = new bootstrap.Modal(document.getElementById('modal-rol'));

    let tablaUsuarios = null;
    let tablaRoles = null;

    var guardandoUsuario = false;
    var guardandoRol = false;
    var eliminandoUsuario = false;
    var eliminandoRol = false;

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

    $('#modal-usuario input, #modal-usuario select').on('blur', function () {
        validarCampo($(this));
    });

    $('#modal-usuario input, #modal-usuario select').on('input change', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    $('#modal-rol input').on('blur', function () {
        validarCampo($(this));
    });

    $('#modal-rol input').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    var cedulaDebounce = null;
    $('#usuario-cedula').on('blur', function () {
        var $this = $(this);
        Validaciones.cedula('#usuario-cedula', 'Cédula');
        if (!$this.prop('readonly')) {
            if (cedulaDebounce) clearTimeout(cedulaDebounce);
            cedulaDebounce = setTimeout(function () {
                Validaciones.cedulaUnica('#usuario-cedula', window.ROUTES.usuarios_verificar_cedula, 'Cédula');
            }, 300);
        }
    });

    $('#usuario-nombre, #usuario-segNombre, #usuario-apellido, #usuario-segApellido').on('blur', function () {
        Validaciones.soloLetras(this, $(this).attr('placeholder') || 'Este campo');
        Validaciones.formatearCapitalizacion(this);
    });

    $('#rol-nombre').on('blur', function () {
        Validaciones.soloLetras(this, 'Nombre del rol');
        Validaciones.formatearCapitalizacion(this);
    });

    $('#modal-usuario').on('shown.bs.modal', function () {
        Validaciones.limpiarErrores('#form-usuario');
        cedulaDebounce = null;
    });

    $('#modal-rol').on('shown.bs.modal', function () {
        Validaciones.limpiarErrores('#form-rol');
    });

    // ═══════════════════════════════════════════════════════
    // DATATABLE USUARIOS SERVER-SIDE
    // ═══════════════════════════════════════════════════════

    function initDataTableUsuarios() {
        tablaUsuarios = $('#tabla-usuarios').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.BASE_URL + window.ROUTES.usuarios_listar,
                type: 'POST'
            },
            columns: [
                {
                    data: 'img',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            if (data) {
                                return '<img src="' + data + '" alt="' + row.nombre + '" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">';
                            }
                            return '<div class="profile-avatar-sm" style="width:40px;height:40px;font-size:0.85rem;">' + (row.nombre?.charAt(0) || '') + (row.apellido?.charAt(0) || '') + '</div>';
                        }
                        return data;
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'cedula' },
                {
                    data: 'nombre',
                    render: function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            var partes = [row.nombre, row.segNombre, row.apellido, row.segApellido].filter(Boolean);
                            return '<div class="fw-semibold">' + partes.join(' ') + '</div><small class="text-muted">' + row.correo + '</small>';
                        }
                        return data;
                    }
                },
                { data: 'correo' },
                { data: 'telefono' },
                {
                    data: 'nombreRol',
                    render: function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            return '<span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">' + (data || 'N/A') + '</span>';
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
                                '<button class="btn btn-sm btn-outline-primary btn-editar-usuario" data-cedula="' + data + '" title="Editar">' +
                                    '<i class="bi bi-pencil"></i>' +
                                '</button>' +
                                '<button class="btn btn-sm btn-outline-danger btn-eliminar-usuario" data-cedula="' + data + '" data-nombre="' + nombreCompleto + '" title="Eliminar">' +
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
            order: [[1, 'asc']],
            dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
        });
    }

    // ═══════════════════════════════════════════════════════
    // DATATABLE ROLES (client-side, pocos registros)
    // ═══════════════════════════════════════════════════════

    function cargarRoles() {
        Ajax.post(window.ROUTES.usuarios_roles_listar)
            .done(function (res) {
                const data = res.data || [];
                let opciones = '<option value="">Seleccione un rol</option>';

                const rows = data.map(function (r) {
                    const estado = parseInt(r.status) === 1
                        ? '<span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Activo</span>'
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">Inactivo</span>';
                    opciones += '<option value="' + r.idRol + '">' + r.nombreRol + '</option>';
                    return {
                        idRol: r.idRol,
                        nombreRol: r.nombreRol,
                        estado: estado,
                        acciones: '<div class="text-end">' +
                            '<button class="btn btn-sm btn-outline-primary btn-editar-rol" data-id="' + r.idRol + '" data-nombre="' + r.nombreRol + '" title="Editar">' +
                                '<i class="bi bi-pencil"></i>' +
                            '</button>' +
                            '<button class="btn btn-sm btn-outline-danger btn-eliminar-rol" data-id="' + r.idRol + '" data-nombre="' + r.nombreRol + '" title="Eliminar">' +
                                '<i class="bi bi-trash"></i>' +
                            '</button>' +
                        '</div>'
                    };
                });

                $('#usuario-rol').html(opciones);

                if (tablaRoles) {
                    tablaRoles.clear().rows.add(rows).draw();
                } else {
                    tablaRoles = $('#tabla-roles').DataTable({
                        data: rows,
                        columns: [
                            { data: 'idRol' },
                            { data: 'nombreRol' },
                            { data: 'estado' },
                            { data: 'acciones', orderable: false, searchable: false }
                        ],
                        responsive: true,
                        language: {
                            url: window.BASE_URL + '/assets/lib/datatables/js/es-ES.json'
                        },
                        pageLength: 5,
                        lengthMenu: [[5, 10, 25, -1], [5, 10, 25, 'Todos']],
                        order: [[0, 'asc']],
                        dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
                    });
                }
            }).fail(function () {
                if (tablaRoles) tablaRoles.clear().destroy();
                $('#tabla-roles tbody').html('<tr><td colspan="4" class="text-center text-muted py-4">Error al cargar roles</td></tr>');
            });
    }

    // ═══════════════════════════════════════════════════════
    // IMAGEN
    // ═══════════════════════════════════════════════════════

    $('#usuario-imagen').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-imagen').attr('src', e.target.result).show();
                $('#avatar-default').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // ═══════════════════════════════════════════════════════
    // USUARIOS
    // ═══════════════════════════════════════════════════════

    $('#btn-nuevo-usuario').on('click', function () {
        $('#form-usuario')[0].reset();
        $('#usuario-id').val('');
        $('#titulo-modal-usuario').html('<i class="bi bi-person-plus me-2 text-primary"></i>Nuevo Usuario');
        $('#usuario-cedula').prop('readonly', false);
        $('#usuario-clave').closest('.col-md-6').show();
        $('#usuario-clave').closest('.col-md-6').find('.form-text').show();
        $('#preview-imagen').hide().attr('src', '');
        $('#avatar-default').show().html('<i class="bi bi-person"></i>');
    });

    $(document).on('click', '.btn-editar-usuario', function () {
        const cedula = $(this).data('cedula');
        Ajax.post(window.ROUTES.usuarios_obtener, { cedula: cedula })
            .done(function (res) {
                if (!res.ok || !res.data) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Usuario no encontrado' });
                    return;
                }
                const u = res.data;
                $('#usuario-id').val(u.cedula);
                $('#usuario-cedula').val(u.cedula).prop('readonly', true);
                $('#usuario-nombre').val(u.nombre);
                $('#usuario-segNombre').val(u.segNombre || '');
                $('#usuario-apellido').val(u.apellido);
                $('#usuario-segApellido').val(u.segApellido || '');
                $('#usuario-correo').val(u.correo);
                $('#usuario-telefono').val(u.telefono);
                $('#usuario-rol').val(u.idRol);
                $('#usuario-status').val(u.status);
                $('#titulo-modal-usuario').html('<i class="bi bi-pencil me-2 text-primary"></i>Editar Usuario');
                $('#usuario-clave').closest('.col-md-6').hide();

                if (u.img) {
                    $('#preview-imagen').attr('src', u.img).show();
                    $('#avatar-default').hide();
                } else {
                    $('#preview-imagen').hide().attr('src', '');
                    $('#avatar-default').show().html((u.nombre?.charAt(0) || '') + (u.apellido?.charAt(0) || ''));
                }

                modalUsuario.show();
            });
    });

    $(document).on('click', '.btn-eliminar-usuario', function () {
        if (eliminandoUsuario) return;
        const cedula = $(this).data('cedula');
        const nombre = $(this).data('nombre');
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar usuario?',
            text: '¿Estás seguro de eliminar a ' + nombre + '?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (result.isConfirmed) {
                eliminandoUsuario = true;
                Ajax.post(window.ROUTES.usuarios_eliminar, { cedula: cedula })
                    .done(function (res) {
                        Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje, timer: 1500, showConfirmButton: false });
                        tablaUsuarios.ajax.reload(null, false);
                    })
                    .always(function () {
                        eliminandoUsuario = false;
                    });
            }
        });
    });

    $('#form-usuario').on('submit', function (e) {
        e.preventDefault();
        if (guardandoUsuario) return;
        Validaciones.limpiarErrores('#form-usuario');
        if (!Validaciones.validarFormulario('#form-usuario')) return;

        guardandoUsuario = true;
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);

        var formData = new FormData(this);
        Ajax.upload(window.ROUTES.usuarios_guardar, formData)
            .done(function (res) {
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje, timer: 1500, showConfirmButton: false });
                modalUsuario.hide();
                tablaUsuarios.ajax.reload(null, false);
            }).fail(function (xhr) {
                var msg = 'Error al guardar usuario.';
                try { var r = JSON.parse(xhr.responseText); if (r.mensaje) msg = r.mensaje; } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }).always(function () {
                guardandoUsuario = false;
                $btn.prop('disabled', false);
            });
    });

    // ═══════════════════════════════════════════════════════
    // ROLES
    // ═══════════════════════════════════════════════════════

    $('#btn-nuevo-rol').on('click', function () {
        $('#form-rol')[0].reset();
        $('#rol-id').val('');
        $('#titulo-modal-rol').html('<i class="bi bi-shield-plus me-2 text-warning"></i>Nuevo Rol');
    });

    $(document).on('click', '.btn-editar-rol', function () {
        const idRol = $(this).data('id');
        const nombre = $(this).data('nombre');

        Ajax.post(window.ROUTES.usuarios_roles_verificar_uso, { idRol: idRol })
            .done(function (res) {
                if (res.ok && res.enUso) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rol en uso',
                        text: 'El rol "' + nombre + '" está asignado a ' + res.count + ' usuario(s) activo(s) y no puede ser editado.'
                    });
                    return;
                }
                $('#rol-id').val(idRol);
                $('#rol-nombre').val(nombre);
                $('#titulo-modal-rol').html('<i class="bi bi-pencil me-2 text-warning"></i>Editar Rol');
                modalRol.show();
            });
    });

    $(document).on('click', '.btn-eliminar-rol', function () {
        if (eliminandoRol) return;
        const idRol = $(this).data('id');
        const nombre = $(this).data('nombre');

        Ajax.post(window.ROUTES.usuarios_roles_verificar_uso, { idRol: idRol })
            .done(function (res) {
                if (res.ok && res.enUso) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rol en uso',
                        text: 'El rol "' + nombre + '" está asignado a ' + res.count + ' usuario(s) activo(s) y no puede ser eliminado.'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: '¿Eliminar rol?',
                    text: '¿Estás seguro de eliminar el rol "' + nombre + '"?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        eliminandoRol = true;
                        Ajax.post(window.ROUTES.usuarios_roles_eliminar, { idRol: idRol })
                            .done(function (res) {
                                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje, timer: 1500, showConfirmButton: false });
                                cargarRoles();
                            })
                            .always(function () {
                                eliminandoRol = false;
                            });
                    }
                });
            });
    });

    $('#form-rol').on('submit', function (e) {
        e.preventDefault();
        if (guardandoRol) return;
        Validaciones.limpiarErrores('#form-rol');
        if (!Validaciones.validarFormulario('#form-rol')) return;

        guardandoRol = true;
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);

        Ajax.post(window.ROUTES.usuarios_roles_guardar, $(this).serialize())
            .done(function (res) {
                Swal.fire({ icon: 'success', title: 'Éxito', text: res.mensaje, timer: 1500, showConfirmButton: false });
                modalRol.hide();
                cargarRoles();
            }).fail(function (xhr) {
                var msg = 'Error al guardar rol.';
                try { var r = JSON.parse(xhr.responseText); if (r.mensaje) msg = r.mensaje; } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }).always(function () {
                guardandoRol = false;
                $btn.prop('disabled', false);
            });
    });

    // ═══════════════════════════════════════════════════════
    // INICIALIZAR
    // ═══════════════════════════════════════════════════════

    initDataTableUsuarios();
    cargarRoles();

});
