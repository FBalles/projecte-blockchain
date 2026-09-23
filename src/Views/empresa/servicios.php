<?php $titulo = 'Catálogo'; ?>
<h2>Servicios disponibles</h2>
<?php if (empty($servicios)): ?>
    <p>No hay servicios disponibles en este momento.</p>
<?php else: ?>
<?php foreach ($servicios as $s): ?>
<div class="servicio">
    <h3><?= htmlspecialchars($s->nombre) ?></h3>
    <p><?= htmlspecialchars($s->descripcion) ?></p>
    <p class="precio"><?= number_format($s->precio, 2, ',', ' ') ?> €</p>
    <form method="post" action="/empresa/solicitudes" style="display:inline">
        <input type="hidden" name="servicio_id" value="<?= $s->id ?>">
        <button type="submit">Solicitar</button>
    </form>
</div>
<?php endforeach; ?>
<?php endif; ?>