# Documentación del Sistema de Ventas e Inventario

En base al análisis de la base de datos `inventario-sistema.sql`, se ha estructurado el flujo del sistema, sus vistas, los requisitos funcionales y el diseño para el módulo de ingreso de productos.

---

## 1. Requisitos Funcionales

El sistema debe cumplir con las siguientes funcionalidades clave, soportadas por la estructura de la base de datos:

*   **Gestión de Configuración y Precios:** 
    *   Registrar y actualizar la tasa de cambio del BCV.
    *   Definir y modificar los márgenes de ganancia para ventas en Divisas (USD) y Bolívares (VES).
*   **Gestión de Inventario:** 
    *   Categorizar productos (Tipos de producto).
    *   Registrar, modificar y consultar productos.
    *   Controlar entradas (compras/abastecimiento) y salidas (pérdidas/ajustes) de productos.
    *   Cálculo automático de precios de venta basado en el costo, margen y tasa BCV.
    *   Monitoreo de stock (stock mínimo, disponible y agotado).
*   **Gestión de Ventas:** 
    *   Procesar ventas a clientes, descontando el stock automáticamente.
    *   Soportar diferentes métodos de pago (Efectivo, Transferencia, Pago Móvil, Zelle, Crédito, etc.).
    *   Manejar ventas multimoneda (USD y VES).
*   **Gestión de Clientes y Créditos:** 
    *   Registrar clientes y sus equipos asociados (ej. Motos).
    *   Manejar cuentas por cobrar para clientes con pagos a crédito.
*   **Seguridad y Auditoría:** 
    *   Gestión de usuarios y roles.
    *   Registro automático de acciones en una bitácora del sistema.
*   **Reportes y Estadísticas:** 
    *   Visualizar información consolidada de ventas diarias, semanales, mensuales, productos más vendidos, y pagos por tipo/moneda.

---

## 2. Flujo del Sistema y sus Vistas

El sistema operará a través de un flujo intuitivo distribuido en las siguientes vistas:

1.  **Vista de Autenticación (Login):** Ingreso seguro para usuarios registrados.
2.  **Vista Principal (Dashboard):**
    *   Tarjetas de resumen: Ventas del día, productos activos, stock bajo, stock agotado.
    *   **Modales de Configuración Rápida:** Botones para abrir modales donde se actualiza el Precio BCV, el Margen de Ganancia Divisa (ej. 50%) y el Margen de Ganancia VES (ej. 75%).
    *   Gráficos estadísticos rápidos.
3.  **Módulo de Inventario:**
    *   **Vista de Productos:** Tabla interactiva con el catálogo, stock y precios. (Incluye Modal de Ingreso/Edición).
    *   **Vista de Entradas:** Formulario/Tabla para registrar el abastecimiento del inventario.
    *   **Vista de Salidas:** Registro de mermas o ajustes manuales de inventario.
4.  **Módulo de Ventas:**
    *   **Vista de Punto de Venta (POS):** Interfaz rápida para seleccionar cliente, buscar productos, agregar al carrito y procesar el pago.
    *   **Vista de Historial de Ventas:** Listado de ventas realizadas y sus detalles.
5.  **Módulo de Clientes:**
    *   **Vista de Directorio de Clientes.**
    *   **Vista de Cuentas por Cobrar (Créditos pendientes).**
6.  **Módulo de Reportes:** Vistas dedicadas para analizar datos generados por las vistas SQL (ej. `vw_productos_mas_vendidos`).
7.  **Módulo de Configuración/Seguridad:** Vistas de Usuarios, Roles y Bitácora.

---

## 3. Flujo de Cálculo de Precios (Lógica de Modales en el Dashboard)

El sistema maneja un flujo de cálculo inteligente y automatizado gracias a la base de datos:

*   **Configuración en Modales:** Desde el Dashboard, el usuario abre los modales para fijar el **Precio BCV**, el **Margen Divisa (50%)** y el **Margen Bolívares (75%)**. Estos datos se guardan en las tablas `bcv_tasas` y `margen_ganancia`.
*   **Cálculo Automático por Triggers:** 
    *   Cuando se **ingresa o actualiza un producto**, los triggers de la base de datos (`trg_producto_calcular_precios_insert` y `trg_producto_calcular_precios_update`) toman el precio de costo del producto en USD.
    *   Calculan el Precio Divisa sumando el 50%: `Precio Venta USD = Costo USD + (Costo USD * 50%)`.
    *   Calculan el Precio Bolívares sumando el 75% y multiplicando por la tasa BCV: `Precio Venta VES = (Costo USD + (Costo USD * 75%)) * Tasa BCV`.
*   **Actualización Masiva:** Si el usuario actualiza la Tasa BCV o los Márgenes desde el Dashboard, se dispara un proceso interno (`sp_recalcular_precios_productos`) que **recalcula automáticamente todos los precios de los productos en el inventario**, sin necesidad de actualizarlos uno por uno.

---

## 4. Tareas para Crear la Vista de "Ingresar Productos" (Flujo Perfecto y Limpio)

Para lograr una experiencia de usuario (UX) óptima al ingresar productos, se recomienda el siguiente flujo y estructura:

### Flujo del Usuario (Paso a Paso)
1.  **Acción:** El usuario entra a la "Vista de Productos" y hace clic en el botón flotante o principal **"+ Nuevo Producto"**.
2.  **Apertura:** Se despliega un Modal superpuesto (sin recargar la página) centrado en pantalla.
3.  **Llenado:** El usuario completa el formulario. Los campos requeridos tienen validación visual en tiempo real.
4.  **Guardado:** Al presionar "Guardar", se envía la data mediante AJAX/Fetch al servidor. 
5.  **Cálculo Transparente:** La base de datos, mediante sus Triggers, calcula los precios de venta automáticamente.
6.  **Feedback:** El modal se cierra, se muestra una notificación flotante (Toast) de "Producto Registrado Exitosamente", y la tabla de productos se recarga dinámicamente para mostrar el nuevo registro.

### Campos requeridos para el Modal de "Ingresar Producto"

Basado en la tabla `producto` de la base de datos, el modal debe contener los siguientes campos de entrada:

| Campo / Input | Tipo de Dato | Obligatorio | Descripción / Comportamiento |
| :--- | :--- | :---: | :--- |
| **Código** | `text` / `number` | ✅ Sí | Código de barras o SKU identificador único. |
| **Nombre** | `text` | ✅ Sí | Nombre descriptivo del producto. |
| **Marca** | `text` | ✅ Sí | Marca comercial del artículo. |
| **Tipo de Producto** | `select` | ✅ Sí | Menú desplegable alimentado por la tabla `tipo_productos`. |
| **Costo (USD)** | `number` (decimal) | ✅ Sí | El costo de compra del producto. **(Base para cálculos)**. |
| **Stock Inicial** | `number` (decimal) | ❌ No | Cantidad con la que inicia. Por defecto puede ser `0`. |
| **Stock Mínimo** | `number` (decimal) | ✅ Sí | Cantidad mínima para disparar alertas (Por defecto `5`). |
| **Imagen** | `file` | ❌ No | Subida de foto del producto (`imgproducto`). |

> [!TIP]
> **No incluyas campos para Precio de Venta (USD/VES)** en el formulario de creación. Como la base de datos se encarga de calcularlos automáticamente usando los márgenes del 50% y 75% con el BCV, mostrarlos en el formulario de creación podría generar confusión. Puedes mostrarlos como "Solo Lectura" en la vista de edición o en la tabla principal.
