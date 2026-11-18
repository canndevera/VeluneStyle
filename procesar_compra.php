
<?php
session_start();
require_once __DIR__ . '/conexion.php';

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$error = '';
$pedidoId = 0;

if (empty($cart)) {
    $error = 'Tu carrito está vacío.';
} else {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion->begin_transaction();

        $productosCompra = [];
        $total = 0;

        // Verificar productos, precios y stock desde MySQL
        foreach ($cart as $item) {
            $id = (int)$item['id'];
            $cantidad = (int)$item['qty'];

            if ($id <= 0 || $cantidad <= 0) {
                throw new Exception('Hay un producto inválido en el carrito.');
            }

            $consulta = $conexion->prepare(
                "SELECT id, nombre, precio, stock, activo
                 FROM productos
                 WHERE id = ?
                 FOR UPDATE"
            );

            $consulta->bind_param("i", $id);
            $consulta->execute();
            $producto = $consulta->get_result()->fetch_assoc();
            $consulta->close();

            if (!$producto || (int)$producto['activo'] !== 1) {
                throw new Exception('Uno de los productos ya no está disponible.');
            }

            if ((int)$producto['stock'] < $cantidad) {
                throw new Exception(
                    'No hay stock suficiente de ' . $producto['nombre'] .
                    '. Stock disponible: ' . $producto['stock']
                );
            }

            $precio = (float)$producto['precio'];
            $subtotal = round($precio * $cantidad, 2);

            $total += $subtotal;

            $productosCompra[] = [
                'id' => (int)$producto['id'],
                'cantidad' => $cantidad,
                'precio' => $precio,
                'subtotal' => $subtotal
            ];
        }

        $total = round($total, 2);

        // Crear el pedido
        $usuarioId = (int)$_SESSION['user_id'];
        $totalBD = number_format($total, 2, '.', '');

        $insertPedido = $conexion->prepare(
            "INSERT INTO pedidos (usuario_id, total)
             VALUES (?, ?)"
        );

        $insertPedido->bind_param("id", $usuarioId, $totalBD);
        $insertPedido->execute();

        $pedidoId = $conexion->insert_id;
        $insertPedido->close();

        // Guardar los detalles y descontar stock
        $insertDetalle = $conexion->prepare(
            "INSERT INTO detalle_pedido
             (pedido_id, producto_id, cantidad, precio, subtotal)
             VALUES (?, ?, ?, ?, ?)"
        );

        $actualizarStock = $conexion->prepare(
            "UPDATE productos
             SET stock = stock - ?
             WHERE id = ? AND stock >= ?"
        );

        foreach ($productosCompra as $producto) {
            $productoId = $producto['id'];
            $cantidad = $producto['cantidad'];
            $precio = $producto['precio'];
            $subtotal = $producto['subtotal'];

            $insertDetalle->bind_param(
                "iiidd",
                $pedidoId,
                $productoId,
                $cantidad,
                $precio,
                $subtotal
            );

            $insertDetalle->execute();

            $actualizarStock->bind_param(
                "iii",
                $cantidad,
                $productoId,
                $cantidad
            );

            $actualizarStock->execute();

            if ($actualizarStock->affected_rows !== 1) {
                throw new Exception('No se pudo actualizar el stock.');
            }
        }

        $insertDetalle->close();
        $actualizarStock->close();

        // Confirmar toda la operación
        $conexion->commit();

        // Vaciar carrito solamente si la compra fue exitosa
        unset($_SESSION['cart']);

    } catch (Throwable $e) {
        $conexion->rollback();

        $error = $e instanceof mysqli_sql_exception
            ? 'No se pudo registrar la compra. Revisá la base de datos e intentá nuevamente.'
            : $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra | VELUNE</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
</head>
<body>

<main style="max-width:650px; margin:100px auto; padding:40px 25px; text-align:center; background:#F7F2EC; border-radius:16px;">

    <?php if ($error === ''): ?>

        <h1 style="font-family:'Playfair Display',serif;">¡Gracias por tu compra!</h1>

        <p>Tu pedido fue registrado correctamente en VELUNE.</p>

        <p><strong>Número de pedido:</strong> #<?php echo $pedidoId; ?></p>

        <p><strong>Total:</strong> $<?php echo number_format($total, 2, ',', '.'); ?></p>

        <p>Pronto podrás consultar el estado de tu pedido.</p>

        <a href="index.php">Volver al inicio</a>

    <?php else: ?>

        <h1 style="font-family:'Playfair Display',serif;">No se pudo completar la compra</h1>

        <p><?php echo htmlspecialchars($error); ?></p>

        <a href="cart.php">Volver al carrito</a>

    <?php endif; ?>

</main>

</body>
</html>
