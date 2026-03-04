<?php
global $userData;  // Add this line

require_once __DIR__ . '/../index.php';
//El archivo requiere que se haya verificado el JWT previamente (si la ruta no es pública).
function requireAdmin(): void
{
    global $userData;  // Also add this inside the function
    
    if (empty($userData['role'])) 
    {
        http_response_code(401);
        echo json_encode(['error' => 'No autenticado']);
        exit;
    }
    if (empty($userData['role']) || $userData['role'] !== 'admin') 
    {
        http_response_code(403);
        echo json_encode(['error' => 'Permisos insuficientes']);
        exit;
    }
}