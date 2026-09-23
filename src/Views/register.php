<?php $titulo = 'Registro'; ?>
<div class="card">
    <h2>Registro de empresa</h2>
    <form method="post" action="/register">
        <label>Nombre empresa <input name="empresa" required></label>
        <label>Email <input type="email" name="email" required></label>
        <label>Contraseña <input type="password" name="password" required minlength="8"></label>
        <button type="submit">Registrarse</button>
    </form>
    <p><a href="/login">Ya tienes cuenta</a></p>
</div>