<?php
declare(strict_types=1);

class AdminController
{
    public static function dashboard(array $params = []): void
    {
        $pendientes = Solicitud::pendientes();
        view('admin/dashboard.php', ['pendientes' => $pendientes]);
    }

    public static function listarServicios(array $params = []): void
    {
        view('admin/servicios.php', ['servicios' => Servicio::todos()]);
    }

    public static function guardarServicio(array $params = []): void
    {
        Servicio::guardar(
            $_POST['nombre'],
            $_POST['descripcion'] ?? '',
            (float)($_POST['precio'] ?? 0),
            isset($_POST['disponible'])
        );
        header('Location: /admin/servicios');
        exit;
    }

    public static function toggleServicio(array $params): void
    {
        Servicio::toggle((int)$params['id']);
        header('Location: /admin/servicios');
        exit;
    }

    public static function aceptar(array $params): void
    {
        $id = (int)$params['id'];
        $solicitud = Solicitud::buscar($id);
        if (!$solicitud) return;

        Solicitud::cambiarEstado($id, 'aceptada');

        // Blockchain
        $txHash = MultiversxClient::send(
            $solicitud['servicio_id'],
            $solicitud['empresa_id'],
            'aceptacion'
        );
        if ($txHash) {
            Solicitud::cambiarEstado($id, 'aceptada', $txHash);
        }

        header('Location: /admin');
        exit;
    }

    public static function rechazar(array $params): void
    {
        $id = (int)$params['id'];
        $solicitud = Solicitud::buscar($id);
        if (!$solicitud) return;

        Solicitud::cambiarEstado($id, 'rechazada');

        $txHash = MultiversxClient::send(
            $solicitud['servicio_id'],
            $solicitud['empresa_id'],
            'rechazo'
        );
        if ($txHash) {
            Solicitud::cambiarEstado($id, 'rechazada', $txHash);
        }

        header('Location: /admin');
        exit;
    }

    public static function marcarRealizado(array $params): void
    {
        $id = (int)$params['id'];
        $solicitud = Solicitud::buscar($id);
        if (!$solicitud) return;

        Solicitud::cambiarEstado($id, 'realizada');

        $txHash = MultiversxClient::send(
            $solicitud['servicio_id'],
            $solicitud['empresa_id'],
            'entrega'
        );
        if ($txHash) {
            Solicitud::cambiarEstado($id, 'realizada', $txHash);
        }

        header('Location: /admin');
        exit;
    }

    public static function trazabilidad(array $params = []): void
    {
        $events = MultiversxClient::getHistory();
        view('admin/trazabilidad.php', ['events' => $events]);
    }
}