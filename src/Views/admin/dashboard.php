<?php $titulo = 'Dashboard'; ?>
<h2>Panel de administración</h2>
<p><strong><?= count($pendientes) ?></strong> solicitudes pendientes</p>

<h3>Solicitudes pendientes</h3>
<?php if (empty($pendientes)): ?>
    <p>No hay solicitudes pendientes.</p>
<?php else: ?>
<table>
    <tr><th>Empresa</th><th>Servicio</th><th>Fecha</th><th>Acciones</th></tr>
    <?php foreach ($pendientes as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['empresa_nombre']) ?></td>
        <td><?= htmlspecialchars($s['servicio_nombre']) ?></td>
        <td><?= $s['fecha'] ?></td>
        <td>
            <form method="post" action="/admin/solicitudes/<?= $s['id'] ?>/aceptar" style="display:inline">
                <button class="btn-ok">Aceptar</button>
            </form>
            <form method="post" action="/admin/solicitudes/<?= $s['id'] ?>/rechazar" style="display:inline">
                <button class="btn-err">Rechazar</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h3>Solicitudes aceptadas (marcar como realizadas)</h3>
<?php
$aceptadas = db()->query("
    SELECT s.*, sv.nombre AS servicio_nombre, u.nombre AS empresa_nombre
    FROM solicitudes s
    JOIN servicios sv ON sv.id = s.servicio_id
    JOIN usuarios u ON u.id = s.empresa_id
    WHERE s.estado = 'aceptada'
")->fetchAll();
?>
<?php if (empty($aceptadas)): ?>
    <p>Ninguna.</p>
<?php else: ?>
<table>
    <tr><th>Empresa</th><th>Servicio</th><th>Fecha</th><th>Acción</th></tr>
    <?php foreach ($aceptadas as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['empresa_nombre']) ?></td>
        <td><?= htmlspecialchars($s['servicio_nombre']) ?></td>
        <td><?= $s['fecha'] ?></td>
        <td>
            <form method="post" action="/admin/solicitudes/<?= $s['id'] ?>/realizado" style="display:inline">
                <button class="btn-deliver">Marcar realizada</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>