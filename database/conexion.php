<?php

class Conexion extends PDO
{
    private $host = 'localhost';
    private $puerto = '3306';
    private $baseDatos = 'SufragioDB';
    private $usuario = 'root';
    private $password = 'Admin2025#';

    public function __construct()
    {
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->puerto . ';dbname=' . $this->baseDatos . ';charset=utf8mb4';

        try {
            parent::__construct($dsn, $this->usuario, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            echo 'Error de conexión: ' . $e->getMessage();
            exit;
        }
    }
}

if (!function_exists('obtenerConexion')) {
    function obtenerConexion()
    {
        static $conexion = null;

        if ($conexion instanceof Conexion) {
            return $conexion;
        }

        $conexion = new Conexion();
        return $conexion;
    }
}
