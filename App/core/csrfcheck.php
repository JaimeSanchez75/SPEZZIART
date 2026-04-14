<?php
/* Protección CSRF para formularios. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));}
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    // 1. Intentar obtener token desde POST normal
    $requestToken = $_POST['csrf_token'] ?? null;

    // 2. Si no viene por POST, intentar desde JSON (para peticiones fetch)
    if (!$requestToken) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data)) {
            $requestToken = $data['csrf_token'] ?? null;
        }
    }

    // 3. Verificar una sola vez
    if (empty($sessionToken) || empty($requestToken) || !hash_equals($sessionToken, $requestToken)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Token CSRF inválido o sesión caducada. Recarga la página.']);
        exit;
    }
}
