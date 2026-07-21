<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;
use App\Models\EquipoCliente;

class ClienteController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('clientes')) return;
        $this->render('clientes/clientes', array_merge([
            'titulo' => 'Gestión de Clientes',
            'seccion' => 'clientes',
        ], $this->datosModulo('clientes', 'clientes.js')), 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('clientes')) return;

        $draw    = (int) ($_POST['draw'] ?? 1);
        $start   = max(0, (int) ($_POST['start'] ?? 0));
        $length  = (int) ($_POST['length'] ?? 10);
        if ($length < 1 && $length !== -1) {
            $length = 10;
        }
        $search  = $_POST['search']['value'] ?? '';

        $colMap  = ['cedula', 'apellido', 'telefono', 'correo', 'tipo_equipo', 'status'];
        $colIdx  = (int) ($_POST['order'][0]['column'] ?? 0);
        $orderBy = $colMap[$colIdx] ?? 'cedula';
        $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');

        $cliente = new Cliente();
        $data = $cliente->obtenerTodosPaginado($start, $length, $search, $orderBy, $orderDir);
        $totalFiltrado = $cliente->contarClientes($search);
        $totalSinFiltro = $cliente->contarClientes('');

        $this->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalSinFiltro,
            'recordsFiltered' => $totalFiltrado,
            'data'            => $data ?: [],
        ]);
    }

    public function obtener(): void
    {
        if (!$this->verificarPermiso('clientes')) return;
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $cliente = new Cliente();
        $c = $cliente->obtenerPorCedula((int) $cedula);
        $this->json(['ok' => !!$c, 'data' => $c]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('clientes')) return;

        $id = $_POST['id'] ?? '';
        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = mb_convert_case(trim($_POST['nombre'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $segNombre = mb_convert_case(trim($_POST['segNombre'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $apellido = mb_convert_case(trim($_POST['apellido'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $segApellido = mb_convert_case(trim($_POST['segApellido'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $direccion = trim($_POST['direccion'] ?? '');
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');
        $idEquipoCliente = $_POST['idEquipoCliente'] ?? 0;
        $status = $_POST['status'] ?? 1;

        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($idEquipoCliente)) {
            $this->json(['ok' => false, 'mensaje' => 'Los campos cédula, nombre, apellido y tipo de cliente son obligatorios.'], 400);
            return;
        }

        $cedulaInt = (int) $cedula;
        if ($cedulaInt <= 0 || $cedulaInt > 999999999) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula fuera de rango válido.'], 400);
            return;
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->json(['ok' => false, 'mensaje' => 'Correo electrónico inválido.'], 400);
            return;
        }

        if ($telefono !== '' && !preg_match('/^[\d\-\(\)\+\s]{7,20}$/', $telefono)) {
            $this->json(['ok' => false, 'mensaje' => 'Teléfono inválido. Use solo dígitos, guiones, paréntesis o +.'], 400);
            return;
        }

        if (!in_array((int) $status, [0, 1], true)) {
            $status = 1;
        }

        $cliente = new Cliente();

        if (empty($id)) {
            if ($cliente->existeCedula($cedulaInt)) {
                $this->json(['ok' => false, 'mensaje' => "La cédula $cedula ya está registrada."], 409);
                return;
            }
            $datos = [
                'cedula' => $cedulaInt,
                'nombre' => $nombre,
                'segNombre' => $segNombre,
                'apellido' => $apellido,
                'segApellido' => $segApellido,
                'direccion' => $direccion,
                'correo' => $correo,
                'telefono' => $telefono,
                'idEquipoCliente' => (int) $idEquipoCliente,
                'status' => (int) $status,
            ];
            $ok = $cliente->crear($datos);
            $mensaje = $ok ? 'Cliente creado correctamente.' : 'Error al crear cliente.';
        } else {
            $datos = [
                'nombre' => $nombre,
                'segNombre' => $segNombre,
                'apellido' => $apellido,
                'segApellido' => $segApellido,
                'direccion' => $direccion,
                'correo' => $correo,
                'telefono' => $telefono,
                'idEquipoCliente' => (int) $idEquipoCliente,
                'status' => (int) $status,
            ];
            $ok = $cliente->actualizar((int) $id, $datos);
            $mensaje = $ok ? 'Cliente actualizado correctamente.' : 'Error al actualizar cliente.';
        }

        $this->json(['ok' => $ok, 'mensaje' => $mensaje], $ok ? 200 : 500);
    }

    public function eliminar(): void
    {
        if (!$this->verificarPermiso('clientes')) return;
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $cliente = new Cliente();
        $ok = $cliente->eliminar((int) $cedula);
        $this->json(['ok' => $ok, 'mensaje' => $ok ? 'Cliente eliminado correctamente.' : 'Error al eliminar cliente.'], $ok ? 200 : 500);
    }

    public function verificarCedula(): void
    {
        if (!$this->verificarPermiso('clientes')) return;
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $cliente = new Cliente();
        $existe = $cliente->existeCedula((int) $cedula);
        $this->json(['ok' => true, 'existe' => $existe]);
    }

    public function listarEquipos(): void
    {
        if (!$this->verificarPermiso('clientes')) return;
        $equipo = new EquipoCliente();
        $equipos = $equipo->obtenerTodos();
        $this->json(['data' => $equipos ?: []]);
    }

    public function verificarVentas(): void
    {
        if (!$this->verificarPermiso('clientes')) return;
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $cliente = new Cliente();
        $count = $cliente->contarVentas((int) $cedula);
        $this->json(['ok' => true, 'enUso' => $count > 0, 'count' => $count]);
    }
}
