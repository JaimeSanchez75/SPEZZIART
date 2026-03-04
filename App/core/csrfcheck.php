<?php
/* Protección CSRF para formularios. */
function csrf_token(): string
{
    if (empty($_COOKIE['csrf_token'])) {setcookie('csrf_token', bin2hex(random_bytes(32)), time() + 3600, '/');}
    return $_COOKIE['csrf_token'];
}

function csrf_verify(): void
{
    $postToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_COOKIE['csrf_token'] ?? '';

    if (empty($postToken) || empty($sessionToken) || !hash_equals($sessionToken, $postToken)) 
    {
        header('Content-Type: application/json'); // Para que JS lo entienda
        http_response_code(403);
        echo json_encode(['error' => 'Token CSRF inválido o sesión caducada. Por favor, recarga la página.']);
        exit;
    }
}