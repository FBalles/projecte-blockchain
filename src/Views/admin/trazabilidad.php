<?php 
$titulo = 'Trazabilidad Blockchain'; 

// Map event types to explicit inline CSS styles
$badgeStyles = [
    'solicitud'  => 'background-color: #0d6efd !important; color: #ffffff !important;', // Solid Blue / White text
    'aceptacion' => 'background-color: #198754 !important; color: #ffffff !important;', // Solid Green / White text
    'entrega'    => 'background-color: #0dcaf0 !important; color: #000000 !important;', // Light Cyan / Black text
    'rechazo'    => 'background-color: #dc3545 !important; color: #ffffff !important;', // Solid Red / White text
    'aprobacion' => 'background-color: #ffc107 !important; color: #000000 !important;', // Solid Yellow / Black text
];
?>

<h2>Trazabilidad Blockchain (MultiversX)</h2>

<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>ID Solicitud</th>
            <th>ID Servicio</th>
            <th>ID Empresa</th>
            <th>Evento</th>
            <th>Fecha</th>
            <th>Hash Transacción (Devnet)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($events)): ?>
            <tr>
                <td colspan="6" class="text-center">No hay eventos registrados en la blockchain.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <?php 
                    $typeKey = strtolower((string)$event['event_type']);
                    $style = $badgeStyles[$typeKey] ?? 'background-color: #6c757d !important; color: #ffffff !important;';
                ?>
                <tr>
                    <td><?= htmlspecialchars((string)($event['request_id'] ?? '—')) ?></td>
                    <td><?= htmlspecialchars((string)$event['service_id']) ?></td>
                    <td><?= htmlspecialchars((string)$event['company_id']) ?></td>
                    <td>
                        <span class="badge" style="<?= $style ?> padding: 6px 10px; font-weight: 600; display: inline-block;">
                            <?= htmlspecialchars((string)$event['event_type']) ?>
                        </span>
                    </td>
                    <td><?= date('Y-m-d H:i:s', $event['timestamp']) ?></td>
                    <td>
                        <?php if (!empty($event['tx_hash'])): ?>
                            <a href="<?= htmlspecialchars($event['explorer_url']) ?>" target="_blank" rel="noopener noreferrer" class="font-monospace">
                                <?= htmlspecialchars(substr($event['tx_hash'], 0, 10) . '...' . substr($event['tx_hash'], -10)) ?>
                                ↗
                            </a>
                        <?php else: ?>
                            <span class="text-muted">No disponible</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>