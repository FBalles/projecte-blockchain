<?php $rol = $_SESSION['rol'] ?? null; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Catálogo de Servicios') ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav>
    <strong>Catálogo de Servicios</strong>
    <?php if ($rol === 'admin'): ?>
        <a href="/admin">Dashboard</a>
        <a href="/admin/servicios">Servicios</a>
        <a href="/admin/trazabilidad">Trazabilidad</a>
    <?php elseif ($rol === 'empresa'): ?>
        <a href="/empresa/servicios">Catálogo</a>
        <a href="/empresa/solicitudes">Mis solicitudes</a>
    <?php endif; ?>
    <!-- Formulario POST para el logout -->
    <form action="/logout" method="POST" style="display: inline;">
        <button type="submit" class="logout" style="background: none; border: none; cursor: pointer; color: inherit; font: inherit;">Salir</button>
    </form>
</nav>
<main>
    <?= $content ?? '' ?>
</main>
</body>
</html>