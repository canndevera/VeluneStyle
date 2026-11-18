
<?php
session_start();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'conexion.php';

$usuario_id = (int) $_SESSION['user_id'];

$sql = "SELECT id, fecha, total, estado
        FROM pedidos
        WHERE usuario_id = ?
        ORDER BY fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$pedidos = [];

while ($fila = $resultado->fetch_assoc()) {
    $pedidos[] = $fila;
}

$stmt->close();

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function obtenerPaso($estado) {
    switch ($estado) {
        case 'Pendiente':
            return 1;
        case 'En preparación':
            return 2;
        case 'Enviado':
            return 3;
        case 'Entregado':
            return 4;
        default:
            return 1;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Mis pedidos | VELUNE</title>

<link rel="stylesheet" href="style.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<style>
.pedidos-container {
    max-width: 1000px;
    margin: 60px auto;
    padding: 30px;
}

.pedidos-titulo {
    text-align: center;
    font-family: 'Playfair Display', serif;
    font-size: 38px;
    margin-bottom: 10px;
    font-weight: 400;
}

.pedidos-subtitulo {
    text-align: center;
    margin-bottom: 40px;
    color: #777;
}

.pedido-card {
    background: #F7F2EC;
    padding: 28px;
    border-radius: 14px;
    margin-bottom: 25px;
    border: 1px solid #ded5ca;
}

.pedido-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 17px;
    border-bottom: 1px solid #ded5ca;
}

.pedido-numero {
    font-family: 'Playfair Display', serif;
    font-size: 23px;
}

.pedido-info {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
    font-size: 13px;
}

.pedido-estado {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 20px;
    background: #e8e0d5;
    color: #51483e;
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.estado-entregado {
    background: #E2E8DC;
    color: #53604B;
}

.estado-enviado {
    background: #E1E7E5;
    color: #53645D;
}

.estado-preparacion {
    background: #E8E5DA;
    color: #756B52;
}

.pedido-total {
    font-size: 20px;
    font-weight: 500;
}

.seguimiento-titulo {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 400;
    margin-top: 30px;
    margin-bottom: 25px;
}

.seguimiento {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 0 5px 20px;
}

.seguimiento::before {
    content: "";
    position: absolute;
    top: 12px;
    left: 12px;
    right: 12px;
    height: 2px;
    background: #D9CCBC;
}

.paso {
    position: relative;
    z-index: 1;
    text-align: center;
    width: 25%;
}

.circulo {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background: #DED5CA;
    border: 3px solid #F7F2EC;
    margin: 0 auto 10px;
}

.paso.completado .circulo {
    background: #8C8177;
}

.paso.actual .circulo {
    background: #292725;
    box-shadow: 0 0 0 4px #D9CCBC;
}

.paso-texto {
    font-size: 10px;
    color: #958A7F;
    line-height: 1.5;
}

.paso.completado .paso-texto,
.paso.actual .paso-texto {
    color: #292725;
    font-weight: 600;
}

.mensaje-estado {
    margin-top: 22px;
    padding: 15px;
    background: #EDE5DA;
    color: #62584F;
    font-size: 12px;
    line-height: 1.7;
    text-align: center;
}

.boton-detalle {
    display: block;
    width: fit-content;
    margin: 22px auto 0;
    padding: 13px 25px;
    background: #292725;
    color: #F7F2EC;
    text-decoration: none;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    transition: .3s;
}

.boton-detalle:hover {
    background: #8C8177;
}

.sin-pedidos {
    text-align: center;
    background: #F7F2EC;
    padding: 45px 20px;
    border-radius: 14px;
}

.volver {
    display: block;
    width: fit-content;
    margin: 35px auto;
    padding: 12px 25px;
    background: #292725;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 12px;
    letter-spacing: 1px;
}

.volver:hover {
    opacity: .85;
}

@media(max-width: 600px) {
    .pedidos-container {
        padding: 18px;
        margin: 35px auto;
    }

    .pedidos-titulo {
        font-size: 30px;
    }

    .pedido-card {
        padding: 20px 15px;
    }

    .paso-texto {
        font-size: 9px;
    }

    .seguimiento {
        margin-left: 0;
        margin-right: 0;
    }
}
</style>
</head>

<body>

<header>
    <nav>
        <a href="index.php">VELUNE</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="cart.php">Carrito</a>
        <a href="mis_pedidos.php">Mis pedidos</a>
        <a href="mi_cuenta.php">Mi cuenta</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<main class="pedidos-container">

    <h1 class="pedidos-titulo">Mis pedidos</h1>

    <p class="pedidos-subtitulo">
        Un espacio para consultar tus compras y seguir cada etapa de tu pedido.
    </p>

    <?php if (count($pedidos) > 0): ?>

        <?php foreach ($pedidos as $pedido): ?>

            <?php
                $estado = $pedido['estado'] ?: 'Pendiente';
                $pasoActual = obtenerPaso($estado);

                $claseEstado = '';

                if ($estado === 'En preparación') {
                    $claseEstado = 'estado-preparacion';
                } elseif ($estado === 'Enviado') {
                    $claseEstado = 'estado-enviado';
                } elseif ($estado === 'Entregado') {
                    $claseEstado = 'estado-entregado';
                }
            ?>

            <div class="pedido-card">

                <div class="pedido-header">

                    <span class="pedido-numero">
                        Pedido #<?= (int)$pedido['id']; ?>
                    </span>

                    <span class="pedido-estado <?= $claseEstado ?>">
                        <?= escapar($estado); ?>
                    </span>

                </div>

                <div class="pedido-info">

                    <div>
                        <strong>Fecha:</strong>
                        <?= date('d/m/Y H:i', strtotime($pedido['fecha'])); ?>
                    </div>

                    <div class="pedido-total">
                        Total: $<?= number_format((float)$pedido['total'], 2, ',', '.'); ?>
                    </div>

                </div>

                <h2 class="seguimiento-titulo">
                    Seguimiento de tu compra
                </h2>

                <div class="seguimiento">

                    <div class="paso <?= $pasoActual >= 1 ? 'completado' : '' ?> <?= $pasoActual === 1 ? 'actual' : '' ?>">
                        <div class="circulo"></div>
                        <div class="paso-texto">Pendiente</div>
                    </div>

                    <div class="paso <?= $pasoActual >= 2 ? 'completado' : '' ?> <?= $pasoActual === 2 ? 'actual' : '' ?>">
                        <div class="circulo"></div>
                        <div class="paso-texto">En preparación</div>
                    </div>

                    <div class="paso <?= $pasoActual >= 3 ? 'completado' : '' ?> <?= $pasoActual === 3 ? 'actual' : '' ?>">
                        <div class="circulo"></div>
                        <div class="paso-texto">Enviado</div>
                    </div>

                    <div class="paso <?= $pasoActual >= 4 ? 'completado' : '' ?> <?= $pasoActual === 4 ? 'actual' : '' ?>">
                        <div class="circulo"></div>
                        <div class="paso-texto">Entregado</div>
                    </div>

                </div>

                <div class="mensaje-estado">
                    <?php if ($pasoActual === 1): ?>
                        Recibimos tu pedido. Pronto comenzaremos a prepararlo.
                    <?php elseif ($pasoActual === 2): ?>
                        ¡Estamos preparando tu compra con mucho cuidado!
                    <?php elseif ($pasoActual === 3): ?>
                        ¡Tu pedido está en camino!
                    <?php else: ?>
                        ¡Tu pedido fue entregado! Gracias por elegir VELUNE.
                    <?php endif; ?>
                </div>

                <a
                    href="detalle_pedido.php?id=<?= (int)$pedido['id'] ?>"
                    class="boton-detalle"
                >
                    Ver detalle del pedido
                </a>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="sin-pedidos">
            <h2>Todavía no tenés pedidos</h2>
            <p>Cuando realices una compra, vas a poder verla acá.</p>
        </div>

    <?php endif; ?>

    <a href="index.php" class="volver">Volver al inicio</a>

</main>

</body>
</html>
