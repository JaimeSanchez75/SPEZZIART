<?php 

class Conexion
{
    private static $db = null;

    public static function conectar()
    {
        if (self::$db === null){

            $config = require __DIR__ . '/../config/config.php';

            if (!self::$db){
                try {
                    self::$db = new PDO
                    (
                        "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4",
                        $config['DB_USER'],
                        $config['DB_PASS'],
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );
                } 
                catch (PDOException $e) 
                {
                    error_log($e->getMessage());
                    die("Error de conexión.");
                }
            }
        }
        return self::$db;
    }  
}
        
