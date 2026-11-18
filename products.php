
<?php
require_once __DIR__ . '/conexion.php';

$sql = "SELECT
            id,
            nombre AS name,
            precio AS price,
            imagen AS img,
            descripcion AS description,
            categoria AS category,
            stock
        FROM productos
        WHERE activo = 1
        ORDER BY id ASC";

$resultado = $conexion->query($sql);

$products = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $products[] = $fila;
    }
}

function findProduct($id) {
    global $products;

    foreach ($products as $p) {
        if ((int)$p['id'] === (int)$id) {
            return $p;
        }
    }

    return null;
}
?>
