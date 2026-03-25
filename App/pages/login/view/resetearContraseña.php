<form action="/App/pages/login/actualizarContrasena" method="POST">
    <h2>Crea tu nueva contraseña</h2>
    
    <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="hidden" name="datos[email]" value="<?php echo $usuario['Email']; ?>">
    
    <div class="mb-3">
        <label>Nueva Contraseña</label>
        <input type="password" name="datos[contrasena]" class="form-control" required minlength="6">
    </div>
    
    <div class="mb-3">
        <label>Confirmar Contraseña</label>
        <input type="password" name="datos[contrasena1]" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
</form>