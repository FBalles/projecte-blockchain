<?php
declare(strict_types=1);

class Solicitud
{
    public static function deEmpresa(int $empresaId): array
    {
        $stmt = db()->prepare("
            SELECT s.*, sv.nombre AS servicio_nombre
            FROM solicitudes s
            JOIN servicios sv ON sv.id = s.servicio_id
            WHERE s.empresa_id = ?
            ORDER BY s.fecha DESC
        ");
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll();
    }

    public static function pendientes(): array
    {
        return db()->query("
            SELECT s.*, sv.nombre AS servicio_nombre, u.nombre AS empresa_nombre
            FROM solicitudes s
            JOIN servicios sv ON sv.id = s.servicio_id
            JOIN usuarios u ON u.id = s.empresa_id
            WHERE s.estado = 'pendiente'
            ORDER BY s.fecha
        ")->fetchAll();
    }

    public static function crear(int $servicioId, int $empresaId): void
    {
        db()->prepare("INSERT INTO solicitudes (servicio_id, empresa_id, estado) VALUES (?,?,'pendiente')")
            ->execute([$servicioId, $empresaId]);
    }

    public static function cambiarEstado(int $id, string $estado, ?string $txHash = null): void
    {
        if ($txHash) {
            db()->prepare("UPDATE solicitudes SET estado = ?, tx_hash = ? WHERE id = ?")
                ->execute([$estado, $txHash, $id]);
        } else {
            db()->prepare("UPDATE solicitudes SET estado = ? WHERE id = ?")
                ->execute([$estado, $id]);
        }
    }

    public static function buscar(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM solicitudes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}