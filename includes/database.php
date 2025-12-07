<?php
include_once __DIR__ . '/../database/conexion.php';

function db()
{
    return obtenerConexion();
}

function dbQuery($sql, $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
