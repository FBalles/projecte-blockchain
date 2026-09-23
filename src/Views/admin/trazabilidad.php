<?php $titulo = 'Trazabilidad Blockchain'; ?>
<h2>Trazabilidad en MultiversX Devnet</h2>
<p>Eventos registrados por esta wallet en el smart contract:</p>

<?php if (empty($events)): ?>
    <p>Sin eventos registrados todavía.</p>
<?php else: ?>
<table>
    <tr><th>#</th><th>Servicio ID</th><th>Empresa ID</th><th>Evento</th><th>Timestamp</th></tr>
    <?php foreach ($events as $i => $e):
        $parts = explode('|', $e);
    ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($parts[0] ?? '') ?></td>
        <td><?= htmlspecialchars($parts[1] ?? '') ?></td>
        <td><?= htmlspecialchars($parts[2] ?? '') ?></td>
        <td><?= htmlspecialchars($parts[3] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>