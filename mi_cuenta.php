
<?php
session_start();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "conexion.php";

$usuario_id = (int)$_SESSION['user_id'];
$nombre = $_SESSION['username'] ?? 'Cliente';

// Obtener estadísticas de compras
$sql = "SELECT
            COUNT(*) AS cantidad_pedidos,
            COALESCE(SUM(total), 0) AS total_comprado,
            MAX(fecha) AS ultima_compra
        FROM pedidos
        WHERE usuario_id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$estadisticas = $stmt->get_result()->fetch_assoc();

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

<title>Mi cuenta | VELUNE</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="style.css">

<style>
.cuenta-container {
    max-width: 950px;
    margin: 65px auto;
    padding: 25px;
}

.cuenta-intro {
    text-align: center;
    margin-bottom: 40px;
}

.cuenta-intro span {
    color: #8C8177;
    font-size: 10px;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.cuenta-intro h1 {
    font-family: 'Playfair Display', serif;
    font-size: 40px;
    font-weight: 400;
    margin: 13px 0;
}

.cuenta-intro p {
    color: #81776E;
    font-size: 13px;
}

.perfil-card {
    background: #F7F2EC;
    padding: 40px;
    border: 1px solid #DED5CA;
    text-align: center;
    margin-bottom: 25px;
}

.avatar {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background: #EDE5DA;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    color: #756B62;
}

.perfil-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 27px;
    font-weight: 400;
    margin-bottom: 8px;
}

.perfil-card p {
    color: #8C8177;
    font-size: 12px;
}

.seccion-titulo {
    font-family: 'Playfair Display', serif;
    font-weight: 400;
    font-size: 25px;
    margin: 35px 0 20px;
}

.estadisticas {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.estadistica {
    background: #F7F2EC;
    border: 1px solid #DED5CA;
    padding: 27px 15px;
    text-align: center;
}

.estadistica span {
    display: block;
    color: #8C8177;
    font-size: 9px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 13px;
}

.estadistica strong {
    font-family: 'Playfair Display', serif;
    font-size: 25px;
    font-weight: 400;
}

.acciones {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-top: 30px;
}

.accion {
    display: block;
    text-align: center;
    text-decoration: none;
    padding: 19px;
    background: #292725;
    color: #F7F2EC;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    transition: .3s;
}

.accion:hover {
    background: #8C8177;
}

.accion.secundaria {
    background: transparent;
    color: #292725;
    border: 1px solid #B8AEA3;
}

.accion.secundaria:hover {
    background: #292725;
    color: #F7F2EC;
}

@media(max-width: 650px) {
    .cuenta-container {
        margin: 35px auto;
        padding: 18px;
    }

    .cuenta-intro h1 {
        font-size: 32px;
    }

    .perfil-card {
        padding: 30px 15px;
    }

    .estadisticas {
        grid-template-columns: 1fr;
    }

    .acciones {
        grid-template-columns: 1fr;
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

<main class="cuenta-container">

    <section class="cuenta-intro">
        <span>Tu espacio personal</span>
        <h1>Mi cuenta</h1>
        <p>Bienvenida a tu espacio dentro de VELUNE.</p>
    </section>

    <section class="perfil-card">

        <div class="avatar">
            <?= strtoupper(substr($nombre, 0, 1)) ?>
        </div>

        <h2><?= escapar($nombre) ?></h2>

        <p>Miembro de VELUNE</p>

    </section>

    <h2 class="seccion-titulo">Resumen de tu actividad</h2>

    <section class="estadisticas">

        <div class="estadistica">
            <span>Mis pedidos</span>
            <strong>
                <?= (int)$estadisticas['cantidad_pedidos'] ?>
            </strong>
        </div>

        <div class="estadistica">
            <span>Total comprado</span>
            <strong>
                $<?= number_format((float)$estadisticas['total_comprado'], 2, ',', '.') ?>
            </strong>
        </div>

        <div class="estadistica">
            <span>Última compra</span>
            <strong style="font-size:18px;">
                <?php
                if ($estadisticas['ultima_compra']) {
                    echo date('d/m/Y', strtotime($estadisticas['ultima_compra']));
                } else {
                    echo 'Sin compras';
                }
                ?>
            </strong>
        </div>

    </section>

    <div class="acciones">

        <a href="mis_pedidos.php" class="accion">
            Consultar mis pedidos
        </a>

        <a href="catalogo.php" class="accion secundaria">
            Seguir comprando
        </a>

    </div>

</main>

</body>
</html>
