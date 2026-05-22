<?php
declare(strict_types=1);
class Flash // Clase para manejar mensajes flash que se muestran al usuario después de redireccionar. Los mensajes se almacenan en la sesión y se eliminan después de ser consumidos.
{
    private const TIPOS_VALIDOS = ['success', 'danger', 'warning', 'info'];
    public static function add(string $tipo, string $mensaje): void // Agrega un mensaje flash al array de sesión. El tipo se valida contra una lista de tipos permitidos.
    {
        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {$tipo = 'info';}
        if (session_status() !== PHP_SESSION_ACTIVE) {@session_start();}
        if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {$_SESSION['flash'] = [];}
        $_SESSION['flash'][] = 
        [
            'tipo'    => $tipo,
            'mensaje' => $mensaje,
        ];
    }
    public static function success(string $mensaje): void {self::add('success', $mensaje);} // Agrega un mensaje flash de éxito
    public static function error(string $mensaje): void {self::add('danger', $mensaje);} // Agrega un mensaje flash de error
    public static function warning(string $mensaje): void {self::add('warning', $mensaje);} // Agrega un mensaje flash de advertencia
    public static function info(string $mensaje): void {self::add('info', $mensaje);} // Agrega un mensaje flash de información
    public static function consume(): array // Devuelve todos los mensajes flash almacenados en la sesión y los elimina para que no se muestren nuevamente en futuras solicitudes.
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {@session_start();}
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return is_array($flashes) ? $flashes : [];
    }
}
