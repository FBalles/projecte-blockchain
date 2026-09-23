<?php $titulo = 'Login'; ?>
<div class="card">
    <h2>Iniciar sesión</h2>
    <?php if (isset($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" action="/login">
        <label>Email <input type="email" name="email" required></label>
        <label>Contraseña <input type="password" name="password" required></label>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="/register">¿No tienes cuenta? Regístrate</a></p>
</div>