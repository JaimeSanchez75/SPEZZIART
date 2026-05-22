<?php
class Logger 
{   private static $logFile = null;
    public static function init() // Llamar al inicio de la aplicación para configurar el logger
    {
        $logDir = ROOT_PATH . '/logs';
        if (!is_dir($logDir)) {mkdir($logDir, 0755, true);}
        self::$logFile = $logDir . '/app.log';
        ini_set('log_errors', 1);
        ini_set('error_log', self::$logFile);
    }
    /**
     * Registrar un evento o error
     * @param string $archivo       Nombre del archivo (ej: "PerfilController.php")
     * @param string $metodo        Método HTTP (GET, POST, PUT, DELETE, etc.)
     * @param string $tipo          Tipo de petición: "AJAX", "WEB", "CLI"
     * @param string $estado        "OK", "ERROR", "WARNING", "INFO"
     * @param int|null $codigoError Código de error (si aplica)
     * @param string|null $mensaje  Mensaje de error o información adicional
     */
    public static function log($archivo, $metodo, $tipo, $estado, $codigoError = null, $mensaje = null)  // Creación de la línea de log con formato consistente.
    {
        $fecha = date('d-m-Y H:i:s');
        // Determinar el tipo de petición si no se pasa explícitamente
        if ($tipo === 'auto') {$tipo = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? 'AJAX' : 'WEB';}
        $linea = "[$fecha] - $archivo - $metodo - $tipo - $estado";
        if ($codigoError !== null && $mensaje !== null) {$linea .= " - ERROR [$codigoError]: \"$mensaje\"";} 
        elseif ($mensaje !== null) {$linea .= " - $mensaje";}
        error_log($linea);
    }
    // Métodos de conveniencia
    public static function info($archivo, $metodo, $tipo, $mensaje) {self::log($archivo, $metodo, $tipo, 'INFO', null, $mensaje);} // Ej: Logger::info('session.php', 'initSession', 'Servidor', 'Sesión iniciada correctamente');
    public static function error($archivo, $metodo, $tipo, $codigo, $mensaje) {self::log($archivo, $metodo, $tipo, 'ERROR', $codigo, $mensaje);} // Ej: Logger::error('db.php', 'conectar', 'BD', 500, 'Error al conectar a la base de datos');
    public static function success($archivo, $metodo, $tipo, $mensaje = null) {self::log($archivo, $metodo, $tipo, 'OK', null, $mensaje);} // Ej: Logger::success('auth.php', 'login', 'WEB', 'Usuario autenticado exitosamente');
    public static function warning($archivo, $metodo, $tipo, $mensaje) {self::log($archivo, $metodo, $tipo, 'WARNING', null, $mensaje);} // Ej: Logger::warning('notifications.php', 'crearParaAdmins', 'BD', 'No se encontraron administradores');
}