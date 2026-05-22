<?php 
declare(strict_types=1);
require_once __DIR__ . '/logger.php';
class Conexion // Clase para manejar la conexión a la base de datos utilizando PDO. Implementa un patrón singleton para reutilizar la misma conexión durante toda la ejecución del script y evitar múltiples conexiones innecesarias.
{
    private static $db = null;
    public static function conectar() // Devuelve una instancia de PDO conectada a la base de datos. Si la conexión ya existe, devuelve la misma instancia.
    {
        if (self::$db === null) 
        {
            try 
            {
                self::$db = new PDO 
                (
                    "mysql:host=". $_ENV['DB_HOST'] .";dbname=".$_ENV['DB_NAME'].";charset=utf8mb4",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                self::$db->exec("SET time_zone = '+02:00'");
                
                Logger::info('db.php', 'Conexion::conectar', 'BD', 'Conexión establecida correctamente');
            } 
            catch (PDOException $e) 
            {
                Logger::error('db.php', 'Conexion::conectar', 'BD', $e->getCode(), $e->getMessage());
                die("Error de conexión.");
            }
        }
        return self::$db;
    }
}