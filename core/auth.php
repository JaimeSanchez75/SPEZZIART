<?php
declare(strict_types=1);
require_once __DIR__ . '/logger.php';
class Auth
{
    private const MAX_INACTIVITY = 21600; // 6 horas
    public static function login(array $user, bool $remember = false): void // Inicia sesión para el usuario dado, almacenando su información en la sesión. Si $remember es true, la cookie de sesión se configura para durar 30 días; de lo contrario, se configura para expirar al cerrar el navegador.
    {
        Logger::info('auth.php', 'Auth::login', 'auto', "Intento de login para usuario {$user['ID_Usuario']}");
        $_SESSION['user'] = 
        [
            'id' => (int)$user['ID_Usuario'],
            'ModoFit' => (int)$user['ModoFit'],
            'email' => $user['Email'],
            'username' => $user['Username'],
            'nombre' => $user['Nombre'] ?? '',
            'role' => ($user['EsAdmin'] ?? false) ? 'admin' : 'user',
            'avatar' => $user['FotoPerfil'] ?? null,
            'tema' => $user['Tema'] ?? 'sistema',
            'notificaciones' => (int)$user['NotificacionOn'],
            'Peso' => $user['Peso'] ?? null,
            'Altura' => $user['Altura'] ?? null,
            'Edad' => $user['Edad'] ?? null,
            'Sexo' => $user['Sexo'] ?? null,
            'NivelActividad' => $user['NivelActividad'] ?? null
        ];
        $cookieParams = session_get_cookie_params();
        $_SESSION['cookie_expiry'] = time() + $cookieParams['lifetime'];
        $_SESSION['fingerprint'] = self::generateFingerprint();
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        if (!$remember)  // Sesión sin recordar, expira al cerrar el navegador
        {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), 0, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            Logger::info('auth.php', 'Auth::login', 'auto', "Usuario {$user['ID_Usuario']} - Sesión SIN recordar");
        }
        if ($remember) // Sesión con recordar, expira en 30 días
        {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + 2592000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            Logger::info('auth.php', 'Auth::login', 'auto', "Usuario {$user['ID_Usuario']} - Sesión CON recordar (30 días)");
        }
        session_regenerate_id(true);
        Logger::success('auth.php', 'Auth::login', 'auto', "Usuario {$user['ID_Usuario']} login completado");
    }
    public static function check(): bool // Verifica si el usuario está autenticado y si su sesión es válida.
    {
        if (!isset($_SESSION['user']) || !isset($_SESSION['fingerprint']))  // Si no hay datos de usuario o fingerprint en la sesión, no está autenticado
        {
            Logger::warning('auth.php', 'Auth::check', 'auto', 'Sesión sin datos de usuario o fingerprint');
            return false;
        }
        $inactive = time() - ($_SESSION['last_activity'] ?? 0);
        if ($inactive > self::MAX_INACTIVITY) // Si la inactividad supera el límite, cerrar sesión por seguridad
        {
            Logger::warning('auth.php', 'Auth::check', 'auto', "Inactividad de $inactive segundos, cerrando sesión");
            self::logout();
            return false;
        }
        if ($_SESSION['fingerprint'] !== self::generateFingerprint()) // Si el fingerprint no coincide, posible robo de sesión, cerrar sesión por seguridad
        {
            Logger::error('auth.php', 'Auth::check', 'auto', 401, 'Fingerprint no coincide (posible robo de sesión)');
            self::logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        Logger::info('auth.php', 'Auth::check', 'auto', "Usuario {$_SESSION['user']['id']} autenticado");
        return true;
    }
    public static function user(): ?array { return self::check() ? $_SESSION['user'] : null; } // Devuelve la información del usuario autenticado o null si no hay usuario autenticado
    public static function id(): ?int { $user = self::user(); return $user ? $user['id'] : null; } // Devuelve el ID del usuario autenticado o null si no hay usuario autenticado
    public static function isAdmin(): bool { $user = self::user(); return $user && ($user['role'] === 'admin'); } // Devuelve true si el usuario autenticado es administrador, false en caso contrario o si no hay usuario autenticado
    public static function logout(): void // Cierra la sesión del usuario, eliminando los datos de sesión y la cookie de sesión, y redirige a la página de login.
    {
        $userId = $_SESSION['user']['id'] ?? 'desconocido';
        Logger::info('auth.php', 'Auth::logout', 'auto', "Usuario $userId cerró sesión");
        $_SESSION = [];
        if (ini_get("session.use_cookies")) 
        {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: /pages/login');
        exit;
    }
    private static function generateFingerprint(): string{return hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');} // Genera un fingerprint simple basado en la dirección IP del usuario. Se puede mejorar agregando más factores como el user agent, pero esto es un ejemplo básico.
}
function requireAuth(): void // Verifica que el usuario esté autenticado antes de permitir el acceso a la ruta. Si no está autenticado, redirige a la página de login o devuelve un error JSON si es una petición AJAX.
{
    if (!Auth::check()) // Si el usuario no está autenticado, registrar el intento y redirigir o responder con error según el tipo de petición
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $tipo = $isAjax ? 'AJAX' : 'WEB';
        Logger::warning('auth.php', 'requireAuth', $tipo, 'Acceso no autorizado');
        if ($isAjax) 
        {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado', 'redirect' => '/pages/login']);
        } 
        else {header('Location: /pages/login');}
        exit;
    }
    Logger::info('auth.php', 'requireAuth', 'auto', 'Acceso permitido a ruta protegida');
}
function requireAdmin(): void // Verifica que el usuario esté autenticado y sea administrador antes de permitir el acceso a la ruta. Si no está autenticado, redirige a la página de login. Si está autenticado pero no es administrador, devuelve un error 403.
{
    if (!Auth::check())  // Si el usuario no está autenticado, registrar el intento y redirigir a login
    {
        Logger::warning('auth.php', 'requireAdmin', 'WEB', 'No autenticado, redirigiendo');
        header('Location: /pages/login');
        exit;
    }
    if (!Auth::isAdmin())  // Si el usuario está autenticado pero no es administrador, registrar el intento y devolver error 403
    {
        Logger::error('auth.php', 'requireAdmin', 'WEB', 403, 'Usuario no administrador');
        renderErrorPage(
            403,
            'Acceso denegado',
            'No tienes permisos para acceder a este recurso.'
        );
        exit;
    }
    Logger::info('auth.php', 'requireAdmin', 'WEB', 'Admin autorizado');
}