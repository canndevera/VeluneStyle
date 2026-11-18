
<?php
session_start();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "conexion.php";

$usuario_id = (int)$_SESSION['user_id'];
$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$pedido_id || $pedido_id <= 0) {
    die("El pedido indicado no es válido.");
}

// Comprobar que el pedido pertenece al cliente
$stmt = $conexion->prepare(
    "SELECT id, fecha, total, estado
     FROM pedidos
     WHERE id = ? AND usuario_id = ?"
);

$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();

$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    die("No encontramos ese pedido en tu cuenta.");
}

// Obtener los productos del pedido
$stmt = $conexion->prepare(
    "SELECT
        p.nombre,
        p.imagen,
        d.cantidad,
        d.precio,
        d.subtotal
     FROM detalle_pedido d
     INNER JOIN productos p ON p.id = d.producto_id
     WHERE d.pedido_id = ?
     ORDER BY d.id ASC"
);

$stmt->bind_param("i", $pedido_id);
$stmt->execute();

$resultado = $stmt->get_result();

$productos = [];

while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}

$stmt->close();

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Detalle del pedido | VELUNE</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="style.css">

<style>
.detalle-container {
    max-width: 900px;
    margin: 60px auto;
    padding: 25px;
}

.encabezado {
    text-align: center;
    margin-bottom: 35px;
}

.encabezado span {
    color: #8C8177;
    font-size: 10px;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.encabezado h1 {
    font-family: 'Playfair Display', serif;
    font-size: 38px;
    font-weight: 400;
    margin: 12px 0;
}

.encabezado p {
    color: #81776E;
    font-size: 12px;
}

.info-pedido {
    background: #F7F2EC;
    border: 1px solid #DED5CA;
    padding: 25px;
    margin-bottom: 25px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.info-pedido span {
    display: block;
    color: #8C8177;
    font-size: 9px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 9px;
}

.info-pedido strong {
    font-size: 13px;
    font-weight: 500;
}

.productos-card {
    background: #F7F2EC;
    border: 1px solid #DED5CA;
    padding: 25px;
}

.producto {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 18px 0;
    border-bottom: 1px solid #DED5CA;
}

.producto:last-child {
    border-bottom: none;
}

.producto img {
    width: 85px;
    height: 105px;
    object-fit: cover;
    background: #EDE5DA;
}

.producto-info {
    flex: 1;
}

.producto-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 400;
    margin: 0 0 8px;
}

.producto-info p {
    color: #8C8177;
    font-size: 11px;
    margin: 4px 0;
}

.producto-precio {
    font-size: 13px;
    white-space: nowrap;
}

.total {
    display: flex;
    justify-content: space-between;
    border-top: 1px solid #B8AEA3;
    margin-top: 20px;
    padding-top: 22px;
    font-size: 13px;
}

.total strong {
    font-family: 'Playfair Display', serif;
    font-size: 23px;
    font-weight: 400;
}

.volver {
    display: inline-block;
    margin-top: 25px;
    color: #756B62;
    font-size: 11px;
    text-decoration: none;
}

.volver:hover {
    color: #292725;
}

@media(max-width: 600px) {
    .detalle-container {
        margin: 30px auto;
        padding: 15px;
    }

    .info-pedido {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .producto {
        gap: 12px;
    }

    .producto img {
        width: 65px;
        height: 85px;
    }

    .producto-info h3 {
        font-size: 15px;
    }

    .producto-precio {
        font-size: 11px;
    }
}
</style>
</head>

<body>

<main class="detalle-container">

    <section class="encabezado">
        <span>Tu historial de compras</span>
        <h1>Detalle del pedido</h1>
        <p>Gracias por elegir VELUNE.</p>
    </section>

    <section class="info-pedido">

        <div>
            <span>Número de pedido</span>
            <strong>#<?= (int)$pedido['id'] ?></strong>
        </div>

        <div>
            <span>Fecha</span>
            <strong><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></strong>
        </div>

        <div>
            <span>Estado</span>
            <strong><?= escapar($pedido['estado']) ?></strong>
        </div>

    </section>

    <section class="productos-card">

        <h2 style="font-family:'Playfair Display',serif;font-weight:400;">
            Productos adquiridos
        </h2>

        <?php if (count($productos) > 0): ?>

            <?php foreach ($productos as $producto): ?>

                <article class="producto">

                    <?php if (!empty($producto['imagen'])): ?>
                        <img
                            src="<?= escapar($producto['imagen']) ?>"
                            alt="<?= escapar($producto['nombre']) ?>"
                        >
                    <?php endif; ?>

                    <div class="producto-info">
                        <h3><?= escapar($producto['nombre']) ?></h3>

                        <p>
                            Cantidad: <?= (int)$producto['cantidad'] ?>
                        </p>

                        <p>
                            Precio unitario:
                            $<?= number_format((float)$producto['precio'], 2, ',', '.') ?>
                        </p>
                    </div>

                    <div class="producto-precio">
                        $<?= number_format((float)$producto['subtotal'], 2, ',', '.') ?>
                    </div>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No se encontraron productos para este pedido.</p>

        <?php endif; ?>

        <div class="total">
            <span>Total de la compra</span>

            <strong>
                $<?= number_format((float)$pedido['total'], 2, ',', '.') ?>
            </strong>
        </div>

    </section>

    <a href="mis_pedidos.php" class="volver">
        ← Volver a mis pedidos
    </a>

</main>

</body>
</html>
