/**
 * login.js - Autenticación JWT + cookie httpOnly + URLs encriptadas
 */
$(function () {

    $('#form-login').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.validarFormulario('#form-login')) return;

        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Ingresando...');

        $.ajax({
            url: Ajax.BASE_URL + window.ROUTES.auth_login,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json'
        })
        .done(function (res) {
            if (res.ok) {
                Ajax.guardarRefreshToken(res.refresh_token);
                localStorage.setItem('usuario', JSON.stringify(res.usuario));
                window.location.replace(Ajax.BASE_URL + window.NAV.dashboard);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.mensaje || 'Error al iniciar sesión' });
            }
        })
        .fail(function (xhr) {
            var msg = 'Error de conexión al servidor.';
            try { var r = JSON.parse(xhr.responseText); if (r.mensaje) msg = r.mensaje; } catch (e) {}
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        })
        .always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right me-1"></i> Ingresar');
        });
    });

    $('#form-recuperar').on('submit', function (e) {
        e.preventDefault();
        if (!Validaciones.requerido('#recuperar-email', 'El correo')) return;
        if (!Validaciones.email('#recuperar-email')) return;
        Ajax.post(window.ROUTES.recuperar, $(this).serialize())
            .done(function (res) { Swal.fire({ icon: 'success', title: 'Correo enviado', text: res.mensaje }); });
    });

    // Autorenovar sesión si venimos por token expirado
    if (window.location.search.indexOf('expired=1') !== -1) {
        Ajax.refrescarToken().then(function (ok) {
            if (ok) {
                window.location.replace(Ajax.BASE_URL + window.NAV.dashboard);
            } else {
                Ajax.limpiarTokens();
            }
        });
        return;
    }

    // Si ya hay sesión activa, refrescar token antes de ir al dashboard
    if (Ajax.getRefreshToken()) {
        Ajax.refrescarToken().then(function (ok) {
            if (ok) {
                window.location.replace(Ajax.BASE_URL + window.NAV.dashboard);
            } else {
                Ajax.limpiarTokens();
            }
        });
    }
});
