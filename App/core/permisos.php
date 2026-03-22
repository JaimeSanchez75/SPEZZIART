<?php
//El archivo requiere que se haya verificado el JWT previamente (si la ruta no es pública).
function requireAdmin()
{
    $user = Auth::user();

    if (!$user) {
        http_response_code(401);
        return false;
    }

    if ($user['role'] !== 'admin') {
        http_response_code(403);

        return false;
    }

    return true;
}