<?php $titulo = 'Mis solicitudes'; ?>
<h2>Mis solicitudes</h2>
<?php if (empty($solicitudes)): ?>
    <p>No has realizado ninguna solicitud.</p>
<?php else: ?>
<table>
    <tr><th>Servicio</th><th>Estado</th><th>Fecha</th><th>Tx Hash</th><th>Acción</th></tr>
    <?php foreach ($solicitudes as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['servicio_nombre']) ?></td>
        <td><span class="badge badge-<?= $s['estado'] ?>"><?= ucfirst($s['estado']) ?></span></td>
        <td><?= $s['fecha'] ?></td>
        <td>
            <?php if ($s['tx_hash']): ?>
                <a href="https://devnet-explorer.multiversx.com/transactions/<?= $s['tx_hash'] ?>" target="_blank">
                    <?= substr($s['tx_hash'], 0, 12) ?>…
                </a>
            <?php else: ?>
                —
            <?php endif; ?>
        </td>
        <td>
            <?php if ($s['estado'] === 'realizada'): ?>
                <form method="post" action="/empresa/solicitudes/<?= $s['id'] ?>/validar" style="display:inline">
                    <button class="btn-ok">✅ Validar</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>