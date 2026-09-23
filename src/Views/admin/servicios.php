<?php $titulo = 'Servicios'; ?>
<h2>Catálogo de servicios</h2>

<h3>Añadir servicio</h3>
<form method="post" action="/admin/servicios" class="form-inline">
    <label>Nombre <input name="nombre" required></label>
    <label>Descripción <textarea name="descripcion" rows="2"></textarea></label>
    <label>Precio (€) <input type="number" step="0.01" name="precio" value="0"></label>
    <label><input type="checkbox" name="disponible" checked> Disponible</label>
    <button type="submit">Guardar</button>
</form>

<h3>Servicios existentes</h3>
<table>
    <tr><th>Nombre</th><th>Precio</th><th>Disponible</th><th>Acción</th></tr>
    <?php foreach ($servicios as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s->nombre) ?></td>
        <td><?= number_format($s->precio, 2, ',', ' ') ?> €</td>
        <td><?= $s->disponible ? '✅' : '❌' ?></td>
        <td>
            <form method="post" action="/admin/servicios/<?= $s->id ?>/toggle" style="display:inline">
                <button>Toggle</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>