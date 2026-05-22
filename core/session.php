<?php
declare(strict_types=1);
require_once __DIR__ . '/logger.php';
function initSession(): void
{
    Logger::info('session.php', 'initSession', 'Servidor', 'Iniciando sesión');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_set_cookie_params
    ([
        'lifetime' => 21600,
        'path' => '/',
        'domain' => $_ENV['DOMAIN'] ?? 'a4.dawbaza.es',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_name('app_session');
    session_start();
    if (empty($_SESSION['csrf_token'])) 
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        Logger::info('session.php', 'initSession', 'Servidor', 'Nuevo token CSRF generado');
    }
    Logger::success('session.php', 'initSession', 'Servidor', 'Sesión iniciada correctamente');
}
function csrf_token(): string{return $_SESSION['csrf_token'] ?? '';}
function csrf_verify(): void
{
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['POST', 'PUT', 'DELETE'])) {
        return;
    }
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = $_POST['csrf_token'] ?? null;
    if (!$requestToken) 
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $requestToken = $input['csrf_token'] ?? null;
    }
    if (!$requestToken) 
    {
        $headers = getallheaders();
        $requestToken = $headers['X-CSRF-Token'] ?? null;
    }
    if (!$sessionToken || !$requestToken || !hash_equals($sessionToken, $requestToken)) 
    {
        Logger::error('session.php', 'csrf_verify', $method, 403, "Token CSRF inválido");
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'CSRF token inválido']);
        exit;
    }
    Logger::info('session.php', 'csrf_verify', $method, 'Token CSRF válido');
}