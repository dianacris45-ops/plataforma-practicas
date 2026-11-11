<?php
namespace App;

use PDO;

/**
 * Crea una sola conexión PDO para toda la aplicación (patrón Singleton sencillo).
 */
class Database
{
    private static ?PDO $conexion = null;

    public static function conectar(): PDO
    {
        if (self::$conexion === null) {
            $config = require __DIR__ . '/../config/database.php';
            self::$conexion = new PDO($config['dsn'], $config['usuario'], $config['clave'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$conexion;
    }
}
