<?php
declare(strict_types=1);

class EmpresaController
{
    public static function projecte-blockchain(array $params = []): void
    {
        view('empresa/servicios.php', ['servicios' => Servicio::disponibles()]);
    }

    public static function solicitar(array $params = []): void
    {
        $servicioId = (int)$_POST['servicio_id'];
        $empresaId  = (int)$_SESSION['user_id'];

        Solicitud::crear($servicioId, $empresaId);

        // Blockchain
        $txHash = MultiversxClient::send($servicioId, $empresaId, 'solicitud');
        if ($txHash) {
            $stmt = db()->prepare("UPDATE solicitudes SET tx_hash = ? WHERE servicio_id = ? AND empresa_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$txHash, $servicioId, $empresaId]);
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