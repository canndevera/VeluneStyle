
<?php
session_start();
require_once "conexion.php";

// Protección: solamente puede ingresar el administrador
if (
    !isset($_SESSION["es_admin"]) ||
    $_SESSION["es_admin"] !== true ||
    !isset($_SESSION["admin_id"])
) {
    header("Location: admin_login.php");
    exit;
}

$estados = [
    "Pendiente",
    "En preparación",
    "Enviado",
    "Entregado"
];

$mensaje = "";
$error = "";

// Cerrar sesión de administración
if (isset($_POST["cerrar_sesion"])) {
    unset($_SESSION["admin_id"], $_SESSION["es_admin"]);
    session_regenerate_id(true);
    header("Location: admin_login.php");
    exit;
}

// Actualizar estado de un pedido
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["actualizar_estado"])) {

    $pedido_id = (int)($_POST["pedido_id"] ?? 0);
    $nuevo_estado = $_POST["estado"] ?? "";

    if ($pedido_id > 0 && in_array($nuevo_estado, $estados, true)) {

        $stmt = $conexion->prepare(
            "UPDATE pedidos SET estado = ? WHERE id = ?"
        );

        $stmt->bind_param("si", $nuevo_estado, $pedido_id);

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $mensaje = "El estado del pedido #$pedido_id fue actualizado.";
        } else {
            $error = "No se pudo actualizar el pedido.";
        }

        $stmt->close();

    } else {
        $error = "Los datos ingresados no son válidos.";
    }
}

// Obtener todos los pedidos y sus clientes
$sql = "SELECT
            p.id,
            p.usuario_id,
            p.fecha,
            p.total,
            p.estado,
            u.usuario AS cliente
        FROM pedidos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha DESC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar los pedidos: " . htmlspecialchars($conexion->error));
}

$pedidos = [];

while ($fila = $resultado->fetch_assoc()) {
    $pedidos[] = $fila;
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function formatoPrecio($precio) {
    return "$" . number_format((float)$precio, 2, ",", ".");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Gestión de pedidos | VELUNE</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #EDE5DA;
    color: #292725;
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
}

header {
    background: #F7F2EC;
    padding: 24px 7%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #DED5CA;
    gap: 20px;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    letter-spacing: 6px;
    font-weight: 400;
}

.header-subtitle {
    color: #8C8177;
    font-size: 10px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-top: 5px;
}

.logout {
    background: transparent;
    border: 1px solid #B8AEA3;
    color: #292725;
    padding: 11px 18px;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
}

.logout:hover {
    background: #292725;
    color: #F7F2EC;
}

main {
    max-width: 1150px;
    margin: 55px auto;
    padding: 0 25px;
}

.intro {
    margin-bottom: 35px;
}

.intro span {
    color: #8C8177;
    font-size: 10px;
    letter-spacing: 4px;
    text-transform: uppercase;
}

h1 {
    font-family: 'Playfair Display', serif;
    font-size: 39px;
    font-weight: 400;
    margin: 12px 0;
}

.intro p {
    color: #81776E;
    font-size: 13px;
    line-height: 1.7;
}

.message {
    padding: 15px 18px;
    background: #E4E9DE;
    color: #53604B;
    font-size: 13px;
    margin-bottom: 22px;
}

.error {
    padding: 15px 18px;
    background: #E9DDD5;
    color: #754D43;
    font-size: 13px;
    margin-bottom: 22px;
}

.order-card {
    background: #F7F2EC;
    padding: 27px;
    margin-bottom: 22px;
    border: 1px solid rgba(41,39,37,0.07);
    box-shadow: 0 5px 20px rgba(41,39,37,0.035);
}

.order-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    flex-wrap: wrap;
    border-bottom: 1px solid #E2D9CF;
    padding-bottom: 19px;
    margin-bottom: 20px;
}

.order-number {
    font-family: 'Playfair Display', serif;
    font-size: 23px;
}

.order-date {
    color: #8C8177;
    font-size: 11px;
    margin-top: 7px;
}

.status {
    display: inline-block;
    padding: 8px 13px;
    border: 1px solid #D3C7B9;
    color: #756B62;
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.status-pendiente {
    background: #F0E6D8;
}

.status-preparacion {
    background: #E8E5DA;
}

.status-enviado {
    background: #E1E7E5;
}

.status-entregado {
    background: #E2E8DC;
}

.info {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 22px;
}

.info-label {
    color: #968A7E;
    font-size: 9px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 7px;
}

.info-value {
    font-size: 13px;
}

.products-title {
    font-family: 'Playfair Display', serif;
    font-size: 19px;
    font-weight: 400;
    margin-bottom: 13px;
}

.product-line {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 10px 0;
    border-bottom: 1px solid #E7DFD6;
    font-size: 12px;
    color: #625B54;
}

.total {
    text-align: right;
    font-family: 'Playfair Display', serif;
    font-size: 21px;
    margin: 20px 0;
}

.update-form {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    border-top: 1px solid #E2D9CF;
    padding-top: 20px;
}

.update-form label {
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #756B62;
}

select {
    padding: 12px;
    background: #F7F2EC;
    border: 1px solid #CFC5B9;
    color: #292725;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
}

.update-button {
    padding: 12px 18px;
    background: #292725;
    border: 1px solid #292725;
    color: #F7F2EC;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
}

.update-button:hover {
    background: transparent;
    color: #292725;
}

.empty {
    background: #F7F2EC;
    padding: 55px 20px;
    text-align: center;
    color: #81776E;
}

.empty h2 {
    font-family: 'Playfair Display', serif;
    font-weight: 400;
    font-size: 25px;
    margin-bottom: 12px;
}

footer {
    background: #292725;
    color: #D9CCBC;
    text-align: center;
    padding: 27px;
    font-size: 10px;
    letter-spacing: 2px;
    margin-top: 70px;
}

@media (max-width: 650px) {
    header {
        padding: 20px;
    }

    .logo {
        font-size: 23px;
    }

    main {
        margin-top: 35px;
        padding: 0 15px;
    }

    h1 {
        font-size: 31px;
    }

    .order-card {
        padding: 20px;
    }

    .info {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .update-form {
        align-items: stretch;
        flex-direction: column;
    }

    .update-form select,
    .update-button {
        width: 100%;
    }
}
</style>
</head>

<body>

<header>
    <div>
        <div class="logo">VELUNE</div>
        <div class="header-subtitle">Administración de la tienda</div>
    </div>

    <form method="POST">
        <button type="submit" name="cerrar_sesion" class="logout">
            Cerrar sesión
        </button>
    </form>
</header>

<main>

    <section class="intro">
        <span>Área privada</span>
        <h1>Gestión de pedidos</h1>
        <p>Administrá las compras de tus clientes y mantené actualizado el seguimiento de cada pedido.</p>
    </section>

    <?php if ($mensaje): ?>
        <div class="message"><?= escapar($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= escapar($error) ?></div>
    <?php endif; ?>

    <?php if (count($pedidos) > 0): ?>

        <?php foreach ($pedidos as $pedido): ?>

            <?php
            $pedido_id = (int)$pedido["id"];

            $detalle = $conexion->prepare(
                "SELECT
                    p.nombre,
                    dp.cantidad,
                    dp.precio,
                    dp.subtotal
                 FROM detalle_pedido dp
                 INNER JOIN productos p ON dp.producto_id = p.id
                 WHERE dp.pedido_id = ?"
            );

            $detalle->bind_param("i", $pedido_id);
            $detalle->execute();
            $productos = $detalle->get_result();

            $estado_clase = "status-pendiente";

            if ($pedido["estado"] === "En preparación") {
                $estado_clase = "status-preparacion";
            } elseif ($pedido["estado"] === "Enviado") {
                $estado_clase = "status-enviado";
            } elseif ($pedido["estado"] === "Entregado") {
                $estado_clase = "status-entregado";
            }
            ?>

            <article class="order-card">

                <div class="order-top">
                    <div>
                        <div class="order-number">
                            Pedido #<?= $pedido_id ?>
                        </div>

                        <div class="order-date">
                            <?= escapar(date("d/m/Y H:i", strtotime($pedido["fecha"]))) ?>
                        </div>
                    </div>

                    <span class="status <?= $estado_clase ?>">
                        <?= escapar($pedido["estado"]) ?>
                    </span>
                </div>

                <div class="info">

                    <div>
                        <div class="info-label">Cliente</div>
                        <div class="info-value">
                            <?= escapar($pedido["cliente"]) ?>
                        </div>
                    </div>

                    <div>
                        <div class="info-label">Número de pedido</div>
                        <div class="info-value">
                            #<?= $pedido_id ?>
                        </div>
                    </div>

                    <div>
                        <div class="info-label">Estado actual</div>
                        <div class="info-value">
                            <?= escapar($pedido["estado"]) ?>
                        </div>
                    </div>

                </div>

                <h3 class="products-title">Productos comprados</h3>

                <?php if ($productos->num_rows > 0): ?>

                    <?php while ($producto = $productos->fetch_assoc()): ?>
                        <div class="product-line">
                            <span>
                                <?= escapar($producto["nombre"]) ?>
                                × <?= (int)$producto["cantidad"] ?>
                            </span>

                            <span>
                                <?= formatoPrecio($producto["subtotal"]) ?>
                            </span>
                        </div>
                    <?php endwhile; ?>

                <?php else: ?>
                    <p>No se encontraron productos para este pedido.</p>
                <?php endif; ?>

                <?php $detalle->close(); ?>

                <div class="total">
                    Total: <?= formatoPrecio($pedido["total"]) ?>
                </div>

                <form method="POST" class="update-form">

                    <input type="hidden" name="pedido_id" value="<?= $pedido_id ?>">

                    <label for="estado-<?= $pedido_id ?>">
                        Actualizar estado
                    </label>

                    <select name="estado" id="estado-<?= $pedido_id ?>" required>

                        <?php foreach ($estados as $estado): ?>
                            <option
                                value="<?= escapar($estado) ?>"
                                <?= $pedido["estado"] === $estado ? "selected" : "" ?>
                            >
                                <?= escapar($estado) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <button
                        type="submit"
                        name="actualizar_estado"
                        class="update-button"
                    >
                        Guardar cambios
                    </button>

                </form>

            </article>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">
            <h2>Todavía no hay pedidos</h2>
            <p>Cuando tus clientes realicen compras, aparecerán acá.</p>
        </div>

    <?php endif; ?>

</main>

<footer>
    VELUNE · MODA CON IDENTIDAD
</footer>

</body>
</html>
