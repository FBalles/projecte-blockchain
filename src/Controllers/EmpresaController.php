<?php
declare(strict_types=1);

class EmpresaController
{
    public static function catalogo(array $params = []): void
    {
        view('empresa/servicios.php', ['servicios' => Servicio::disponibles()]);
    }

    public static function solicitar(array $params = []): void
    {
        $servicioId = (int)$_POST['servicio_id'];
        $empresaId  = (int)$_SESSION['user_id'];

        $solicitudId = Solicitud::crear($servicioId, $empresaId);

        $txHash = MultiversxClient::send($solicitudId, $servicioId, $empresaId, 'solicitud');
        if ($txHash) {
            Solicitud::cambiarEstado($solicitudId, 'pendiente', $txHash);
        }

        header('Location: /empresa/solicitudes');
        exit;
    }

    public static function misSolicitudes(array $params = []): void
    {
        view('empresa/solicitudes.php', [
            'solicitudes' => Solicitud::deEmpresa((int)$_SESSION['user_id'])
        ]);
    }

    public static function validar(array $params): void
    {
        $id = (int)$params['id'];
        $solicitud = Solicitud::buscar($id);
        if (!$solicitud) return;

        Solicitud::cambiarEstado($id, 'validada');

        $txHash = MultiversxClient::send(
            (int) $solicitud['id'],
            $solicitud['servicio_id'],
            $solicitud['empresa_id'],
            'aprobacion'
        );
        if ($txHash) {
            Solicitud::cambiarEstado($id, 'validada', $txHash);
        }

        header('Location: /empresa/solicitudes');
        exit;
    }
}