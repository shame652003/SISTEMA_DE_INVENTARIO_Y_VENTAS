/**
 * global_ajax.js - Configuración AJAX con JWT vía cookie httpOnly
 * El access token vive en cookie httpOnly (inmune a XSS).
 * El refresh token vive en localStorage (necesario para refresh vía JS).
 * Su hash está almacenado en BD (refresh_tokens).
 */

const Ajax = (function () {
    const BASE_URL = window.BASE_URL || '';
    let isRefreshing = false;
    let refreshQueue = [];
    let refreshFailed = false; // evitar bucle infinito si el retry tambien falla

    function getRefreshToken() {
        return localStorage.getItem('refresh_token');
    }

    function guardarRefreshToken(token) {
        if (token) localStorage.setItem('refresh_token', token);
    }

    function limpiarTokens() {
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('usuario');
        // SameSite=Lax debe coincidir con el setcookie del servidor
        document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Lax;';
    }

    function procesarCola(error) {
        refreshQueue.forEach(function (item) {
            if (error) item.reject(error);
            else item.resolve();
        });
        refreshQueue = [];
    }

    async function refrescarToken() {
        if (refreshFailed) return false;

        const refreshToken = getRefreshToken();
        if (!refreshToken) return false;

        try {
            const res = await $.ajax({
                url: BASE_URL + window.ROUTES.auth_refresh,
                type: 'POST',
                data: { refresh_token: refreshToken },
                dataType: 'json'
            });

            if (res.ok) {
                guardarRefreshToken(res.refresh_token);
                refreshFailed = false;
                return true;
            }
        } catch (e) {}

        refreshFailed = true;
        limpiarTokens();
        return false;
    }

    // Redirigir a login (usar replace para no acumular historial)
    function irALogin() {
        window.location.replace(BASE_URL + window.NAV.login);
    }

    $.ajaxSetup({
        dataType: 'json',
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        complete: function () {
            $('#ajax-loader').hide();
        }
    });

    // Interceptor global de errores 401
    $(document).ajaxError(async function (event, xhr, settings) {
        if (xhr.status === 401 && !settings.url.includes('/auth/refresh') && !settings.url.includes('/auth/login') && !settings.url.includes('/auth/me') && !settings.url.includes('/auth/logout')) {
            if (!isRefreshing) {
                isRefreshing = true;
                const ok = await refrescarToken();
                isRefreshing = false;
                procesarCola(!ok);

                if (ok) {
                    // Reintentar el request original y propagar su respuesta
                    $.ajax(settings)
                        .done(function (data, textStatus, jqXHR) {
                            // Disparar evento global para que el codigo original pueda reaccionar
                            $(document).trigger('ajax-retry-success', [settings, data, textStatus, jqXHR]);
                        })
                        .fail(function (retryXhr) {
                            if (retryXhr.status === 401) {
                                // El retry tambien fallo con 401 -> cookie no se aplico, ir a login
                                refreshFailed = true;
                                limpiarTokens();
                                irALogin();
                            } else {
                                $(document).trigger('ajax-retry-error', [settings, retryXhr]);
                            }
                        });
                } else {
                    Swal.fire({ icon: 'info', title: 'Sesion expirada', text: 'Inicie sesion nuevamente.' }).then(function () {
                        irALogin();
                    });
                }
            } else {
                // Ya hay un refresh en curso, encolar este request
                return new Promise(function (resolve, reject) {
                    refreshQueue.push({ resolve, reject });
                }).then(function () {
                    return $.ajax(settings);
                }).catch(function () {
                    // Refresh fallo, el request original se descarta
                });
            }
        } else if (xhr.status === 403) {
            Swal.fire({ icon: 'warning', title: 'Acceso denegado', text: 'No tienes permisos para realizar esta accion.' });
        } else if (xhr.status === 500) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error interno del servidor.' });
        }
    });

    return {
        get: function (url, data = {}) {
            return $.get(BASE_URL + url, data);
        },

        post: function (url, data = {}, options = {}) {
            return $.post(BASE_URL + url, data, null, 'json').fail(function (xhr) {
                if (options.onError) options.onError(xhr);
            });
        },

        upload: function (url, formData) {
            return $.ajax({
                url: BASE_URL + url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            });
        },

        getRefreshToken: getRefreshToken,
        guardarRefreshToken: guardarRefreshToken,
        limpiarTokens: limpiarTokens,
        refrescarToken: refrescarToken,
        BASE_URL: BASE_URL
    };
})();
