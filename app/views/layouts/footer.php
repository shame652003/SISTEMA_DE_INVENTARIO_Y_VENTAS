<?php
use App\Core\UrlCipher;

$rutasMap = [
    'auth_login'        => '/auth/login',
    'auth_refresh'      => '/auth/refresh',
    'auth_logout'       => '/auth/logout',
    'auth_me'           => '/auth/me',
    'login'             => '/login',
    'recuperar'         => '/recuperar',
    'dashboard'         => '/dashboard',
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
    'perfil'            => '/perfil',
    'perfil_obtener'    => '/perfil/obtener',
    'perfil_actualizar' => '/perfil/actualizar',
    'perfil_cambiar_clave' => '/perfil/cambiar-clave',
    'bitacora'          => '/bitacora',
    'bitacora_listar'   => '/bitacora/listar',
    'ayuda'             => '/ayuda',
    'clientes'          => '/clientes',
    'clientes_listar'   => '/clientes/listar',
    'clientes_obtener'  => '/clientes/obtener',
    'clientes_guardar'  => '/clientes/guardar',
    'clientes_eliminar' => '/clientes/eliminar',
    'clientes_verificar_cedula' => '/clientes/verificar-cedula',
    'clientes_equipos_listar'   => '/clientes/equipos/listar',
    'clientes_verificar_ventas' => '/clientes/verificar-ventas',
    'productos'          => '/productos',
    'productos_listar'           => '/productos/listar',
    'productos_obtener'          => '/productos/obtener',
    'productos_guardar'          => '/productos/guardar',
    'productos_eliminar'         => '/productos/eliminar',
    'productos_tipos_listar'     => '/productos/tipos/listar',
    'productos_tipos_guardar'    => '/productos/tipos/guardar',
    'productos_tipos_eliminar'   => '/productos/tipos/eliminar',
    'entradas'          => '/entradas',
    'entradas_listar'   => '/entradas/listar',
    'entradas_guardar'  => '/entradas/guardar',
    'salidas'           => '/salidas',
    'salidas_listar'    => '/salidas/listar',
    'salidas_guardar'   => '/salidas/guardar',
    'ventas'            => '/ventas',
    'ventas_listar'     => '/ventas/listar',
    'ventas_guardar'    => '/ventas/guardar',
    'stock'             => '/stock',
    'stock_listar'      => '/stock/listar',
    'reportes_pagos'       => '/reportes/pagos',
    'reportes_pagos_generar' => '/reportes/pagos/generar',
    'reportes_generales'       => '/reportes/generales',
    'reportes_generales_generar' => '/reportes/generales/generar',
];

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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

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

            // Verificar sesión: solo redirige si NO hay refresh_token guardado
            if (!window.AUTH.sesionActiva) {
                window.location.replace(window.BASE_URL + window.NAV.login);
                return;
            }

            $.ajax({
                url: window.BASE_URL + window.ROUTES.auth_me,
                type: 'GET',
                dataType: 'json'
            }).fail(function(xhr) {
                if (xhr.status === 401 && !localStorage.getItem('refresh_token')) {
                    window.location.replace(window.BASE_URL + window.NAV.login);
                }
            });

            // Toggle sidebar en móvil
            $('#sidebar-toggle').on('click', function() {
                $('.sidebar').toggleClass('show');
            });

            // Cerrar sidebar al hacer click en un link en móvil
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
