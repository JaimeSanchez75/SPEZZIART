<?php
//El archivo requiere que se haya verificado el JWT previamente (si la ruta no es pública).
function requireAdmin(): void
{
    if (empty($_SESSION['user'])) 
    {
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }
    if (empty($_SESSION['user']->role) ||$_SESSION['user']->role !== 'admin') 
    {
        http_response_code(403);
        echo json_encode(['error' => 'Permisos insuficientes']);
        exit;
    }
}