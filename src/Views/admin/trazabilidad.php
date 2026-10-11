<?php 
$titulo = 'Trazabilidad Blockchain'; 

// Map event types to readable Bootstrap badge styles
$badgeClasses = [
    'solicitud'  => 'bg-primary text-white',
    'aceptacion' => 'bg-success text-white',
    'entrega'    => 'bg-info text-dark',
    'rechazo'    => 'bg-danger text-white',
    'aprobacion' => 'bg-warning text-dark',
];
?>

<h2>Trazabilidad Blockchain (MultiversX)</h2>

<table class="table table-striped align-middle">
    <thead>
        <tr>
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
                <td colspan="5" class="text-center">No hay eventos registrados en la blockchain.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <?php 
                    $typeKey = strtolower((string)$event['event_type']);
                    $badgeStyle = $badgeClasses[$typeKey] ?? 'bg-secondary text-white';
                ?>
                <tr>
                    <td><?= htmlspecialchars((string)$event['service_id']) ?></td>
                    <td><?= htmlspecialchars((string)$event['company_id']) ?></td>
                    <td>
                        <span class="badge <?= $badgeStyle ?>">
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