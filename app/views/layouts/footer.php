<?php
use App\Core\UrlCipher;

$rutasGlobales = [
    'auth_login'   => '/auth/login',
    'auth_refresh' => '/auth/refresh',
    'auth_logout'  => '/auth/logout',
    'auth_me'      => '/auth/me',
    'login'        => '/login',
    'recuperar'    => '/recuperar',
    'dashboard'          => '/dashboard',
    'dashboard_bcv_actualizar'      => '/dashboard/bcv/actualizar',
    'dashboard_bcv_consultar_api'   => '/dashboard/bcv/consultar-api',
    'dashboard_margen_actualizar'   => '/dashboard/margen/actualizar',
    'dashboard_graficas'             => '/dashboard/graficas',
    'dashboard_ultimas_ventas'      => '/dashboard/ultimas-ventas',
];

$rutasPorModulo = [
    'usuarios' => [
        'usuarios'          => '/usuarios',
        'usuarios_listar'   => '/usuarios/listar',
        'usuarios_obtener'  => '/usuarios/obtener',
        'usuarios_guardar'  => '/usuarios/guardar',
        'usuarios_eliminar' => '/usuarios/eliminar',
        'usuarios_verificar_cedula' => '/usuarios/verificar-cedula',
        'usuarios_roles_listar'   => '/usuarios/roles/listar',
        'usuarios_roles_guardar'  => '/usuarios/roles/guardar',
        'usuarios_roles_eliminar' => '/usuarios/roles/eliminar',
        'usuarios_roles_verificar_uso' => '/usuarios/roles/verificar-uso',
    ],
    'perfil' => [
        'perfil'            => '/perfil',
        'perfil_obtener'    => '/perfil/obtener',
        'perfil_actualizar' => '/perfil/actualizar',
        'perfil_cambiar_clave' => '/perfil/cambiar-clave',
    ],
    'bitacora' => [
        'bitacora'        => '/bitacora',
        'bitacora_listar' => '/bitacora/listar',
    ],
    'ayuda' => [
        'ayuda' => '/ayuda',
    ],
    'clientes' => [
        'clientes'          => '/clientes',
        'clientes_listar'   => '/clientes/listar',
        'clientes_obtener'  => '/clientes/obtener',
        'clientes_guardar'  => '/clientes/guardar',
        'clientes_eliminar' => '/clientes/eliminar',
        'clientes_verificar_cedula' => '/clientes/verificar-cedula',
        'clientes_equipos_listar'   => '/clientes/equipos/listar',
        'clientes_verificar_ventas' => '/clientes/verificar-ventas',
    ],
    'productos' => [
        'productos'          => '/productos',
        'productos_listar'   => '/productos/listar',
        'productos_obtener'  => '/productos/obtener',
        'productos_guardar'  => '/productos/guardar',
        'productos_eliminar' => '/productos/eliminar',
        'productos_tipos_listar'  => '/productos/tipos/listar',
        'productos_tipos_guardar' => '/productos/tipos/guardar',
        'productos_tipos_eliminar'=> '/productos/tipos/eliminar',
        'productos_verificar_codigo' => '/productos/verificar-codigo',
        'productos_tipos_verificar'  => '/productos/tipos/verificar',
    ],
    'entradas' => [
        'entradas'               => '/entradas',
        'entradas_listar'        => '/entradas/listar',
        'entradas_guardar'       => '/entradas/guardar',
        'entradas_detalle'         => '/entradas/detalle',
        'entradas_productos_buscar' => '/entradas/productos/buscar',
        'entradas_producto_info'    => '/entradas/producto/info',
        'entradas_config_precios'   => '/entradas/config/precios',
    ],
    'salidas' => [
        'salidas'               => '/salidas',
        'salidas_listar'        => '/salidas/listar',
        'salidas_guardar'       => '/salidas/guardar',
        'salidas_productos_buscar' => '/salidas/productos/buscar',
        'salidas_producto_info'    => '/salidas/producto/info',
    ],
    'ventas' => [
        'ventas'               => '/ventas',
        'ventas_config_inicial'   => '/ventas/config/inicial',
        'ventas_clientes_buscar'  => '/ventas/clientes/buscar',
        'ventas_cliente_info'     => '/ventas/cliente/info',
        'ventas_cliente_registrar_rapido' => '/ventas/cliente/registrar-rapido',
        'ventas_productos_buscar' => '/ventas/productos/buscar',
        'ventas_producto_info'    => '/ventas/producto/info',
        'ventas_guardar'          => '/ventas/guardar',
        'ventas_listar'           => '/ventas/listar',
        'ventas_detalle'          => '/ventas/detalle',
    ],
    'stock' => [
        'stock'               => '/stock',
        'stock_listar'        => '/stock/listar',
        'stock_productos_buscar' => '/stock/productos/buscar',
        'stock_producto_info'    => '/stock/producto/info',
    ],
    'reportes_pagos' => [
        'reportes_pagos'              => '/reportes/pagos',
        'reportes_pagos_generar'      => '/reportes/pagos/generar',
        'reportes_pagos_creditos'     => '/reportes/pagos/creditos',
        'reportes_pagos_cliente_detalle' => '/reportes/pagos/cliente-detalle',
        'reportes_pagos_venta_detalle'   => '/reportes/pagos/venta-detalle',
        'reportes_pagos_tipos'        => '/reportes/pagos/tipos',
        'reportes_pagos_clientes_buscar' => '/reportes/pagos/clientes/buscar',
        'reportes_pagos_saldo_credito'   => '/reportes/pagos/saldo-credito',
        'reportes_pagos_abonar_credito'  => '/reportes/pagos/abonar-credito',
        'reportes_pagos_creditos_detalle' => '/reportes/pagos/creditos-detalle',
        'reportes_pagos_fechas_ventas'   => '/reportes/pagos/fechas-ventas',
        'reportes_pagos_pdf_historial'  => '/reportes/pagos/pdf/historial',
        'reportes_pagos_pdf_creditos'   => '/reportes/pagos/pdf/creditos',
        'reportes_pagos_pdf_venta'      => '/reportes/pagos/pdf/venta',
        'reportes_pagos_abono_detalle'  => '/reportes/pagos/abono-detalle',
    ],
    'reportes_generales' => [
        'reportes_generales'        => '/reportes/generales',
        'reportes_generales_generar' => '/reportes/generales/generar',
    ],
];

$modulo = $modulo ?? '';
$rutasMap = $rutasGlobales;
if ($modulo !== '' && isset($rutasPorModulo[$modulo])) {
    $rutasMap = array_merge($rutasMap, $rutasPorModulo[$modulo]);
}

$navMap = [];
foreach ($rutasMap as $k => $r) {
    $navMap[$k] = UrlCipher::encrypt($r);
}
?>

<?php if (empty($esPublico) || !$esPublico): ?>
<footer class="app-footer">
    <div class="container-fluid">
        <span>© <?= date('Y') ?> Sistema de Ventas e Inventarios. Todos los derechos reservados.</span>
    </div>
</footer>
<?php endif; ?>

    <!-- jQuery -->
    <script src="<?= BASE_URL ?>/assets/lib/jquery/jquery.min.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="<?= BASE_URL ?>/assets/lib/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Librerías condicionales (Select2, DataTables, etc.) -->
    <?= $extraLibsJS ?? '' ?>

    <!-- Configuración global JS -->
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.ROUTES = <?= json_encode($rutasMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        window.NAV    = <?= json_encode($navMap,   JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        window.AUTH = {
            get usuario() {
                try { return JSON.parse(localStorage.getItem('usuario')); } catch(e) { return null; }
            },
            get sesionActiva() {
                return !!localStorage.getItem('refresh_token');
            },
            logout: async function() {
                var refreshToken = localStorage.getItem('refresh_token');
                if (refreshToken) {
                    try { await $.post(window.BASE_URL + window.ROUTES.auth_logout, { refresh_token: refreshToken }); } catch(e) {}
                }
                localStorage.removeItem('refresh_token');
                localStorage.removeItem('usuario');
                document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Lax;';
                window.location.replace(window.BASE_URL + window.NAV.login);
            }
        };

        $(function() {
            var esPublico = <?= isset($esPublico) && $esPublico ? 'true' : 'false' ?>;
            if (esPublico) return;

            if (!window.AUTH.sesionActiva) {
                window.location.replace(window.BASE_URL + window.NAV.login);
                return;
            }

            $.ajax({
                url: window.BASE_URL + window.ROUTES.auth_me,
                type: 'GET',
                dataType: 'json'
            }).fail(async function(xhr) {
                if (xhr.status === 401) {
                    var ok = await Ajax.refrescarTokenSilencioso();
                    if (!ok) {
                        window.location.replace(window.BASE_URL + window.NAV.login);
                    }
                }
            });

            var REFRESH_INTERVAL = 12 * 60 * 1000;
            var inicioDiferido = 10 * 1000;

            setTimeout(function() {
                Ajax.refrescarTokenSilencioso();

                setInterval(function() {
                    Ajax.refrescarTokenSilencioso();
                }, REFRESH_INTERVAL);
            }, inicioDiferido);

            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    Ajax.refrescarTokenSilencioso();
                }
            });

            $('#sidebar-toggle').on('click', function() {
                $('.sidebar').toggleClass('show');
            });

            $('.sidebar .nav-link').on('click', function() {
                if ($(window).width() < 768) {
                    $('.sidebar').removeClass('show');
                }
            });
        });
    </script>

    <!-- JS Globales -->
    <script src="<?= BASE_URL ?>/assets/js/global_ajax.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/validations.js"></script>

    <?= $extraJS ?? '' ?>
</body>
</html>
