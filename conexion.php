<?php

$host = "localhost";
$usuario = "root";
$password = "";
$bd = "velune";

$conexion = new mysqli($host, $usuario, $password);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->query("CREATE DATABASE IF NOT EXISTS velune");
$conexion->select_db($bd);

$conexion->query("CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

?>