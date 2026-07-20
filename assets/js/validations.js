/**
 * validations.js - Funciones reutilizables de validación de formularios
 */

const Validaciones = {
    /**
     * Valida que un campo no esté vacío
     */
    requerido: function (selector, nombre = 'Este campo') {
        const valor = $(selector).val().trim();
        if (!valor) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} es obligatorio.`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Valida formato de email
     */
    email: function (selector) {
        const valor = $(selector).val().trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (valor && !regex.test(valor)) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, 'Ingrese un correo electrónico válido.');
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Valida longitud mínima
     */
    longitudMinima: function (selector, min, nombre = 'Este campo') {
        const valor = $(selector).val().trim();
        if (valor && valor.length < min) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} debe tener al menos ${min} caracteres.`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Valida que sea un número
     */
    numero: function (selector, nombre = 'Este campo') {
        const valor = $(selector).val().trim();
        if (valor && isNaN(valor)) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} debe ser un número.`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Valida que sea un número positivo
     */
    numeroPositivo: function (selector, nombre = 'Este campo') {
        const valor = parseFloat($(selector).val());
        if (isNaN(valor) || valor <= 0) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} debe ser un número positivo.`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Valida que solo contenga letras (sin números ni símbolos)
     */
    soloLetras: function (selector, nombre = 'Este campo') {
        const valor = $(selector).val().trim();
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
        if (valor && !regex.test(valor)) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} solo debe contener letras.`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Valida formato de teléfono (Venezuela)
     */
    telefono: function (selector, nombre = 'Teléfono') {
        const valor = $(selector).val().trim();
        const regex = /^[0-9]{4}[-]?[0-9]{7}$/;
        if (valor && !regex.test(valor)) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} debe tener formato válido (Ej: 0414-1234567).`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Valida que sea un número entero positivo (cédula)
     */
    cedula: function (selector, nombre = 'Cédula') {
        const valor = $(selector).val().trim();
        const regex = /^[0-9]+$/;
        if (valor && !regex.test(valor)) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} debe contener solo números.`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Formatea el valor de un input a Capitalización (Title Case)
     * aDmiNIstrador -> Administrador, juan carlos -> Juan Carlos
     */
    formatearCapitalizacion: function (selector) {
        const $input = $(selector);
        const valor = $input.val().trim();
        if (!valor) return;
        const formateado = valor.replace(/\b\w+/g, function (word) {
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        });
        if (formateado !== valor) {
            $input.val(formateado);
        }
    },

    /**
     * Verifica vía AJAX si una cédula ya está registrada.
     * Retorna una Promise que resuelve a true (válido) o false (duplicado).
     * Muestra/limpia el error inline automáticamente.
     * @param {string} selector - Selector CSS del input
     * @param {string} url     - Ruta del endpoint de verificación
     * @param {string} nombre  - Nombre del campo para el mensaje
     * @returns {Promise<boolean>}
     */
    cedulaUnica: function (selector, url, nombre = 'Cédula') {
        const $input = $(selector);
        const valor = $input.val().trim();
        if (!valor || !/^[0-9]+$/.test(valor)) {
            return Promise.resolve(true);
        }

        return new Promise(function (resolve) {
            Ajax.post(url, { cedula: valor })
                .done(function (res) {
                    if (res.ok && res.existe) {
                        $input.addClass('is-invalid');
                        Validaciones._mostrarError(selector, `${nombre} "${valor}" ya está registrada.`);
                        resolve(false);
                    } else {
                        $input.removeClass('is-invalid');
                        $input.siblings('.invalid-feedback').remove();
                        resolve(true);
                    }
                })
                .fail(function () {
                    resolve(true);
                });
        });
    },

    /**
     * Valida que el código tenga formato alfanumérico (letras y números)
     */
    codigoFormato: function (selector, nombre = 'Código') {
        const valor = $(selector).val().trim();
        const regex = /^[A-Za-z0-9]+$/;
        if (valor && !regex.test(valor)) {
            $(selector).addClass('is-invalid');
            this._mostrarError(selector, `${nombre} solo debe contener letras y números.`);
            return false;
        }
        $(selector).removeClass('is-invalid');
        return true;
    },

    /**
     * Fuerza mayúsculas en un campo de texto
     */
    formatearMayusculas: function (selector) {
        const $input = $(selector);
        const valor = $input.val();
        if (!valor) return;
        const formateado = valor.toUpperCase();
        if (formateado !== valor) {
            $input.val(formateado);
        }
    },

    /**
     * Verifica vía AJAX si un código de producto ya está registrado.
     */
    codigoUnico: function (selector, url, nombre = 'Código') {
        const $input = $(selector);
        const valor = $input.val().trim();
        if (!valor) return Promise.resolve(true);

        return new Promise(function (resolve) {
            Ajax.post(url, { codigo: valor, id: $('#producto-id').val() || '' })
                .done(function (res) {
                    if (res.ok && res.existe) {
                        $input.addClass('is-invalid');
                        Validaciones._mostrarError(selector, `${nombre} "${valor}" ya está registrado.`);
                        resolve(false);
                    } else {
                        $input.removeClass('is-invalid');
                        $input.siblings('.invalid-feedback').remove();
                        resolve(true);
                    }
                })
                .fail(function () {
                    resolve(true);
                });
        });
    },

    /**
     * Verifica vía AJAX si un tipo de producto ya está registrado.
     */
    tipoUnico: function (selector, url, nombre = 'Tipo') {
        const $input = $(selector);
        const valor = $input.val().trim();
        if (!valor) return Promise.resolve(true);

        return new Promise(function (resolve) {
            Ajax.post(url, { tipo: valor })
                .done(function (res) {
                    if (res.ok && res.existe) {
                        $input.addClass('is-invalid');
                        Validaciones._mostrarError(selector, `${nombre} "${valor}" ya está registrado.`);
                        resolve(false);
                    } else {
                        $input.removeClass('is-invalid');
                        $input.siblings('.invalid-feedback').remove();
                        resolve(true);
                    }
                })
                .fail(function () {
                    resolve(true);
                });
        });
    },

    /**
     * Valida todos los campos requeridos de un formulario
     */
    validarFormulario: function (formSelector) {
        let valido = true;
        $(formSelector).find('[required]').each(function () {
            const nombre = $(this).attr('name') || 'Este campo';
            if (!Validaciones.requerido(this, nombre)) {
                valido = false;
            }
        });

        $(formSelector).find('input[type="email"]').each(function () {
            if (!Validaciones.email(this)) {
                valido = false;
            }
        });

        return valido;
    },

    /**
     * Limpia los errores de un formulario
     */
    limpiarErrores: function (formSelector) {
        $(formSelector).find('.is-invalid').removeClass('is-invalid');
        $(formSelector).find('.invalid-feedback').remove();
    },

    /**
     * Muestra mensaje de error debajo del campo
     */
    _mostrarError: function (selector, mensaje) {
        $(selector).siblings('.invalid-feedback').remove();
        $(selector).after(`<div class="invalid-feedback">${mensaje}</div>`);
    }
};
