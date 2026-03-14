<?php
//El archivo requiere que se haya verificado el JWT previamente (si la ruta no es pública).
function requireAdmin(): void
{
    $user = Auth::user();

    if (!$user) {
        http_response_code(401);
        exit;
    }

    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
}