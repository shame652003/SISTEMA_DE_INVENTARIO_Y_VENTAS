/**
 * productos.js - Lógica CRUD de productos (repuestos para motos)
 */

$(function () {

    const modalListaProductos = new bootstrap.Modal('#modal-lista-productos');
    const modalTipoProducto = new bootstrap.Modal('#modal-tipo-producto');
    let tablaProductos = null;

    /* ===== Helpers ===== */

    const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    let cameraStream = null;
    let fotoBlob = null;

    function mostrarModoNormal() {
        $('#camara-video').hide();
        $('#camara-canvas').hide();
        $('#img-preview').show();
        $('#btn-tomar-foto').show();
        $('#btn-seleccionar-archivo').show();
        $('#btn-capturar').hide();
        $('#btn-cancelar-camara').hide();
        $('#camara-controles').show();
        $('#camara-acciones').hide();
    }

    function mostrarModoCamara() {
        $('#img-preview').hide();
        $('#camara-canvas').hide();
        $('#camara-video').show();
        $('#btn-tomar-foto').hide();
        $('#btn-seleccionar-archivo').hide();
        $('#btn-capturar').show();
        $('#btn-cancelar-camara').show();
        $('#camara-controles').show();
        $('#camara-acciones').show();
    }

    function mostrarModoPreview() {
        $('#camara-video').hide();
        $('#camara-canvas').hide();
        $('#img-preview').show();
        $('#btn-tomar-foto').hide();
        $('#btn-seleccionar-archivo').hide();
        $('#btn-capturar').hide();
        $('#btn-cancelar-camara').show();
        $('#camara-controles').show();
        $('#camara-acciones').show();
    }

    function mostrarPreviewImagen(file) {
        if (file) {
            if (!ALLOWED_MIME.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Formato no válido',
                    text: 'Solo se permiten imágenes JPEG, PNG y WebP.',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#imagen').val('');
                fotoBlob = null;
                return false;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#img-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
            fotoBlob = file;
            return true;
        } else {
            $('#img-preview').attr('src', window.BASE_URL + '/assets/img/placeholder-product.svg');
            fotoBlob = null;
            return false;
        }
    }

    function detenerCamara() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function (track) { track.stop(); });
            cameraStream = null;
        }
    }

    function resetFormulario() {
        $('#form-producto')[0].reset();
        $('#producto-id').val('');
        $('#btn-guardar-texto').text('Registrar');
        $('#img-preview').attr('src', window.BASE_URL + '/assets/img/placeholder-product.svg');
        $('#marca').prop('disabled', true);
        Validaciones.limpiarErrores('#form-producto');
        $('#btn-guardar').prop('disabled', false);
        detenerCamara();
        fotoBlob = null;
        mostrarModoNormal();
    }

    /* ===== Carga de Tipos ===== */

    function cargarTipos() {
        Ajax.post(window.ROUTES.productos_tipos_listar)
            .done(function (res) {
                const $select = $('#idTipoA');
                const valorActual = $select.val();
                $select.html('<option value=""></option>');
                (res.data || []).forEach(function (t) {
                    $select.append(`<option value="${t.idTipoA}">${t.tipo}</option>`);
                });
                if (valorActual) $select.val(valorActual);
            });
    }

    /* ===== DataTable Productos ===== */

    function cargarProductos() {
        Ajax.post(window.ROUTES.productos_listar)
            .done(function (res) {
                const data = res.data || [];
                const rows = data.map(function (p) {
                    const img = p.imgproducto
                        ? `<img src="${window.BASE_URL}/${p.imgproducto}" class="rounded" style="width:50px;height:50px;object-fit:cover;" onerror="this.onerror=null;this.src='${window.BASE_URL}/assets/img/placeholder-product.svg';">`
                        : `<span class="badge bg-secondary">Sin imagen</span>`;
                    return [
                        img,
                        p.codigo,
                        p.nombre,
                        p.tipo_producto || '-',
                        p.marca || '-',
                        `<div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-warning btn-editar" data-id="${p.idproducto}" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${p.idproducto}" data-nombre="${p.nombre}" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>`
                    ];
                });

                if (tablaProductos) {
                    tablaProductos.clear().destroy();
                }

                tablaProductos = $('#tabla-productos').DataTable({
                    data: rows,
                    columns: [
                        { title: 'Imagen', orderable: false, searchable: false },
                        { title: 'Código' },
                        { title: 'Nombre' },
                        { title: 'Tipo' },
                        { title: 'Marca' },
                        { title: 'Acciones', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    responsive: true,
                    language: {
                        url: window.BASE_URL + '/assets/lib/datatables/js/es-ES.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Todos']],
                    order: [[2, 'asc']],
                    dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rtip'
                });
            }).fail(function () {
                if (tablaProductos) tablaProductos.clear().destroy();
                $('#tabla-productos tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">Error al cargar productos</td></tr>');
            });
    }

    /* ===== Eventos ===== */

    $('#imagen').on('change', function () {
        if (this.files[0]) {
            if (mostrarPreviewImagen(this.files[0])) {
                mostrarModoPreview();
            } else {
                mostrarModoNormal();
            }
        } else {
            mostrarModoNormal();
        }
    });

    $('#btn-tomar-foto').on('click', function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            Swal.fire({
                icon: 'warning',
                title: 'Cámara no disponible',
                text: 'Tu navegador no soporta acceso a la cámara o no tienes una cámara conectada.',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 800 }, height: { ideal: 800 } }
        }).then(function (stream) {
            cameraStream = stream;
            const video = $('#camara-video')[0];
            video.srcObject = stream;
            video.onloadedmetadata = function () {
                video.play();
            };
            mostrarModoCamara();
        }).catch(function () {
            navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 800 }, height: { ideal: 800 } }
            }).then(function (stream) {
                cameraStream = stream;
                const video = $('#camara-video')[0];
                video.srcObject = stream;
                video.onloadedmetadata = function () {
                    video.play();
                };
                mostrarModoCamara();
            }).catch(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de cámara',
                    text: 'No se pudo acceder a la cámara. Verifica los permisos.',
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        });
    });

    $('#btn-cancelar-camara').on('click', function () {
        detenerCamara();
        fotoBlob = null;
        $('#imagen').val('');
        $('#img-preview').attr('src', window.BASE_URL + '/assets/img/placeholder-product.svg');
        mostrarModoNormal();
    });

    $('#btn-capturar').on('click', function () {
        const video = $('#camara-video')[0];
        const canvas = $('#camara-canvas')[0];
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        canvas.toBlob(function (blob) {
            fotoBlob = blob;
            const url = URL.createObjectURL(blob);
            $('#img-preview').attr('src', url);
            detenerCamara();
            mostrarModoPreview();
        }, 'image/jpeg', 0.9);
    });

    $('#btn-seleccionar-archivo').on('click', function () {
        $('#imagen').trigger('click');
    });

    $('#acMarca').on('change', function () {
        $('#marca').prop('disabled', !this.checked);
        if (!this.checked) $('#marca').val('');
    });

    $('#btn-cancelar').on('click', function () {
        resetFormulario();
    });

    $('#btn-ver-productos').on('click', function () {
        cargarProductos();
    });

    /* ===== Validaciones en tiempo real ===== */

    $('#codigo').on('input', function () {
        Validaciones.formatearMayusculas('#codigo');
    }).on('blur', function () {
        Validaciones.formatearMayusculas('#codigo');
        Validaciones.codigoFormato('#codigo', 'Código');
        Validaciones.codigoUnico('#codigo', window.ROUTES.productos_verificar_codigo, 'Código');
    });

    $('#nombre').on('input', function () {
        Validaciones.formatearMayusculas('#nombre');
    }).on('blur', function () {
        Validaciones.formatearMayusculas('#nombre');
    });

    $('#marca').on('blur', function () {
        Validaciones.formatearCapitalizacion('#marca');
    });

    $('#tipo-nombre').on('blur', function () {
        Validaciones.formatearCapitalizacion('#tipo-nombre');
        Validaciones.tipoUnico('#tipo-nombre', window.ROUTES.productos_tipos_verificar, 'Tipo');
    });

    /* ===== Guardar Producto ===== */

    $('#form-producto').on('submit', function (e) {
        e.preventDefault();
        Validaciones.limpiarErrores('#form-producto');

        if (!Validaciones.validarFormulario('#form-producto')) return;

        Validaciones.formatearMayusculas('#codigo');
        if (!Validaciones.codigoFormato('#codigo', 'Código')) return;

        const $btn = $('#btn-guardar');
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);

        const formData = new FormData(this);
        if (!$('#acMarca').is(':checked')) {
            formData.set('marca', '');
        }

        if (fotoBlob) {
            formData.set('imagen', fotoBlob, 'foto_capturada.jpg');
        }

        Ajax.upload(window.ROUTES.productos_guardar, formData)
            .done(function (res) {
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: res.mensaje,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    resetFormulario();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                }
            }).fail(function (xhr) {
                let msg = 'Error al guardar el producto.';
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.mensaje) msg = r.mensaje;
                } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }).always(function () {
                $btn.prop('disabled', false);
            });
    });

    /* ===== Editar Producto ===== */

    $(document).on('click', '.btn-editar', function () {
        const id = $(this).data('id');
        Ajax.post(window.ROUTES.productos_obtener, { id: id })
            .done(function (res) {
                if (!res.ok || !res.data) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el producto.' });
                    return;
                }
                const p = res.data;
                $('#producto-id').val(p.idproducto);
                $('#codigo').val(p.codigo);
                $('#nombre').val(p.nombre);
                $('#idTipoA').val(p.idTipoA);

                if (p.marca) {
                    $('#acMarca').prop('checked', true);
                    $('#marca').prop('disabled', false).val(p.marca);
                } else {
                    $('#acMarca').prop('checked', false);
                    $('#marca').prop('disabled', true).val('');
                }

                if (p.imgproducto) {
                    $('#img-preview').attr('src', window.BASE_URL + '/' + p.imgproducto);
                } else {
                    $('#img-preview').attr('src', window.BASE_URL + '/assets/img/placeholder-product.svg');
                }

                $('#btn-guardar-texto').text('Actualizar');
                modalListaProductos.hide();
                $('html, body').animate({ scrollTop: 0 }, 400);
            });
    });

    /* ===== Eliminar Producto ===== */

    $(document).on('click', '.btn-eliminar', function () {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');

        $.ajax({
            url: window.BASE_URL + window.ROUTES.productos_obtener,
            type: 'POST',
            data: { id: id },
            dataType: 'json'
        }).done(function (res) {
            if (res.ok && res.data && res.data.tiene_ventas) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede eliminar',
                    text: `El producto "${nombre}" no se puede eliminar porque tiene ventas asociadas.`,
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar producto?',
                text: `¿Estás seguro de eliminar "${nombre}"?`,
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then(function (result) {
                if (result.isConfirmed) {
                    Ajax.post(window.ROUTES.productos_eliminar, { id: id })
                        .done(function (res) {
                            if (res.ok) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: res.mensaje,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                cargarProductos();
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                            }
                        });
                }
            });
        });
    });

    /* ===== Guardar Tipo Producto ===== */

    $('#form-tipo-producto').on('submit', function (e) {
        e.preventDefault();
        Validaciones.limpiarErrores('#form-tipo-producto');
        if (!Validaciones.validarFormulario('#form-tipo-producto')) return;

        Validaciones.formatearCapitalizacion('#tipo-nombre');

        const $btn = $(this).find('button[type="submit"]');
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);

        Ajax.post(window.ROUTES.productos_tipos_guardar, $(this).serialize())
            .done(function (res) {
                if (res.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: res.mensaje,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#form-tipo-producto')[0].reset();
                    modalTipoProducto.hide();
                    cargarTipos();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje });
                }
            }).fail(function (xhr) {
                let msg = 'Error al guardar el tipo.';
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.mensaje) msg = r.mensaje;
                } catch (e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }).always(function () {
                $btn.prop('disabled', false);
            });
    });

    /* ===== Inicialización ===== */

    mostrarModoNormal();

    $('#modal-tipo-producto').on('shown.bs.modal', function () {
        $('#tipo-nombre').trigger('focus');
    });

    cargarTipos();
});
