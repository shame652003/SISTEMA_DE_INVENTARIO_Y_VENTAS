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
