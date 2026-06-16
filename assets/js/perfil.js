/**
 * perfil.js - Lógica de edición de perfil con validaciones en tiempo real
 */

$(function () {

    const modalClave = new bootstrap.Modal(document.getElementById('modal-cambiar-clave'));

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

        // Solo letras para nombre/apellido
        if ((selector === '#perfil-nombre' || selector === '#perfil-segNombre' ||
             selector === '#perfil-apellido' || selector === '#perfil-segApellido') && valido) {
            if (!Validaciones.soloLetras(selector, nombre)) valido = false;
        }

        // Teléfono
        if (selector === '#perfil-telefono' && valido) {
            if (!Validaciones.telefono(selector)) valido = false;
        }

        return valido;
    }

    $('#form-perfil input').on('blur', function () {
        validarCampo($(this));
    });

    $('#form-perfil input').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    $('#form-cambiar-clave input').on('blur', function () {
        validarCampo($(this));
    });

    $('#form-cambiar-clave input').on('input', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    // Limpiar errores al abrir modal
    $('#modal-cambiar-clave').on('shown.bs.modal', function () {
        Validaciones.limpiarErrores('#form-cambiar-clave');
        $('#form-cambiar-clave')[0].reset();
    });

    // ═══════════════════════════════════════════════════════
    // TOGGLE CONTRASEÑA (Modal)
    // ═══════════════════════════════════════════════════════

    $(document).on('click', '.btn-toggle-clave-modal', function () {
        const targetId = $(this).data('target');
        const $input = $('#' + targetId);
        const $icon = $(this).find('i');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // ═══════════════════════════════════════════════════════
    // PREVIEW DE IMAGEN + ELIMINAR FOTO
    // ═══════════════════════════════════════════════════════

    $('#perfil-imagen').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#perfil-preview-img').attr('src', e.target.result).show();
                $('#perfil-avatar-default').hide();
                $('#btn-eliminar-foto').show();
                $('#perfil-eliminar-img').val('0');
            };
            reader.readAsDataURL(file);
        }
    });

    $('#btn-eliminar-foto').on('click', function () {
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar foto?',
            text: '¿Estás seguro de eliminar tu foto de perfil?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#perfil-preview-img').hide().attr('src', '');
                $('#perfil-avatar-default').show();
                $('#perfil-imagen').val('');
                $('#perfil-eliminar-img').val('1');
                $('#btn-eliminar-foto').hide();
            }
        });
    });

    // ═══════════════════════════════════════════════════════
    // ACTUALIZAR HEADER Y SIDEBAR EN TIEMPO REAL
    // ═══════════════════════════════════════════════════════

    function actualizarLayout(u) {
        const nombreHeader = [u.nombre, u.apellido].filter(Boolean).join(' ');
        const iniciales = ((u.nombre || '').charAt(0) + (u.apellido || '').charAt(0)).toUpperCase();

        // Header
        $('#header-nombre').text(nombreHeader);
        $('#header-rol').text(u.nombreRol || 'Usuario');
        if (u.img) {
            $('#header-avatar-img').attr('src', u.img).show();
            $('#header-avatar-text').hide();
        } else {
            $('#header-avatar-img').hide().attr('src', '');
            $('#header-avatar-text').text(iniciales).show();
        }

        // Sidebar
        $('#sidebar-nombre').text(nombreHeader);
        $('#sidebar-rol').text(u.nombreRol || 'Usuario');
        if (u.img) {
            $('#sidebar-avatar-img').attr('src', u.img).show();
            $('#sidebar-avatar-text').hide();
        } else {
            $('#sidebar-avatar-img').hide().attr('src', '');
            $('#sidebar-avatar-text').text(iniciales).show();
        }

        // Auth global + localStorage
        if (window.AUTH && window.AUTH.usuario) {
            window.AUTH.usuario.nombre = nombreHeader;
            window.AUTH.usuario.img = u.img || null;
            window.AUTH.usuario.nombreRol = u.nombreRol || 'Usuario';
            localStorage.setItem('usuario', JSON.stringify(window.AUTH.usuario));
        }
    }

    // ═══════════════════════════════════════════════════════
    // CARGAR DATOS DEL PERFIL
    // ═══════════════════════════════════════════════════════

    function cargarPerfil() {
        Ajax.post(window.ROUTES.perfil_obtener)
            .done(function (res) {
                if (!res.ok || !res.data) {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje || 'No se pudo cargar el perfil.' });
                    return;
                }

                const u = res.data;
                const nombreCompleto = [u.nombre, u.segNombre, u.apellido, u.segApellido].filter(Boolean).join(' ');

                // Info card
                $('#perfil-nombre-completo').text(nombreCompleto);
                $('#perfil-rol').text(u.nombreRol || 'Usuario');
                $('#perfil-info-correo').text(u.correo);
                $('#perfil-info-telefono').text(u.telefono);
                $('#perfil-info-cedula').text(u.cedula);

                // Avatar
                if (u.img) {
                    $('#perfil-preview-img').attr('src', u.img).show();
                    $('#perfil-avatar-default').hide();
                    $('#btn-eliminar-foto').show();
                } else {
                    $('#perfil-preview-img').hide().attr('src', '');
                    $('#perfil-avatar-default').show().html(`${(u.nombre?.charAt(0) || '') + (u.apellido?.charAt(0) || '')}`);
                    $('#btn-eliminar-foto').hide();
                }

                // Formulario
                $('#perfil-nombre').val(u.nombre);
                $('#perfil-segNombre').val(u.segNombre || '');
                $('#perfil-apellido').val(u.apellido);
                $('#perfil-segApellido').val(u.segApellido || '');
                $('#perfil-correo').val(u.correo);
                $('#perfil-telefono').val(u.telefono);
                $('#perfil-eliminar-img').val('0');

                actualizarLayout(u);
            })
            .fail(function (xhr) {
                var msg = 'Error al cargar el perfil.';
                try { var r = JSON.parse(xhr.responseText); if (r.mensaje) msg = r.mensaje; } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            });
    }

    // ═══════════════════════════════════════════════════════
    // GUARDAR PERFIL
    // ═══════════════════════════════════════════════════════

    $('#form-perfil').on('submit', function (e) {
        e.preventDefault();
        Validaciones.limpiarErrores('#form-perfil');

        // Validar campos obligatorios
        if (!Validaciones.validarFormulario('#form-perfil')) return;

        // Validar email
        if (!Validaciones.email('#perfil-correo')) return;

        // Validar solo letras
        if (!Validaciones.soloLetras('#perfil-nombre', 'Nombre')) return;
        if (!Validaciones.soloLetras('#perfil-apellido', 'Apellido')) return;

        // Validar teléfono
        if (!Validaciones.telefono('#perfil-telefono')) return;

        var formData = new FormData(this);

        Ajax.upload(window.ROUTES.perfil_actualizar, formData)
            .done(function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: res.mensaje,
                    timer: 2000,
                    showConfirmButton: false
                });
                cargarPerfil();
            })
            .fail(function (xhr) {
                var msg = 'Error al actualizar perfil.';
                try { var r = JSON.parse(xhr.responseText); if (r.mensaje) msg = r.mensaje; } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            });
    });

    // ═══════════════════════════════════════════════════════
    // CAMBIAR CONTRASEÑA
    // ═══════════════════════════════════════════════════════

    $('#form-cambiar-clave').on('submit', function (e) {
        e.preventDefault();
        Validaciones.limpiarErrores('#form-cambiar-clave');

        if (!Validaciones.validarFormulario('#form-cambiar-clave')) return;

        const claveNueva = $('#clave-nueva').val().trim();
        const claveConfirmar = $('#clave-confirmar').val().trim();

        if (claveNueva !== claveConfirmar) {
            $('#clave-confirmar').addClass('is-invalid');
            Validaciones._mostrarError('#clave-confirmar', 'Las contraseñas no coinciden.');
            return;
        }

        if (claveNueva.length < 6) {
            $('#clave-nueva').addClass('is-invalid');
            Validaciones._mostrarError('#clave-nueva', 'La contraseña debe tener al menos 6 caracteres.');
            return;
        }

        Ajax.post(window.ROUTES.perfil_cambiar_clave, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: res.mensaje,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    modalClave.hide();
                    $('#form-cambiar-clave')[0].reset();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                }
            })
            .fail(function (xhr) {
                var msg = 'Error al cambiar contraseña.';
                try { var r = JSON.parse(xhr.responseText); if (r.mensaje) msg = r.mensaje; } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            });
    });

    // ═══════════════════════════════════════════════════════
    // INICIALIZAR
    // ═══════════════════════════════════════════════════════

    cargarPerfil();

});
