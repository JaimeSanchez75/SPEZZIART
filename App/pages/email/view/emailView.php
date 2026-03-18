
<form action="App/pages/email/resetear/guardarContraseña" method="POST">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['t']); ?>">

    <div>
        <label>Nueva contraseña:</label>
        <input type="password" name="pass1" required minlength="8">
    </div>

    <div>
        <label>Confirmar contraseña:</label>
        <input type="password" name="pass2" required minlength="8">
    </div>

    <button type="submit">Cambiar contraseña</button>
</form>