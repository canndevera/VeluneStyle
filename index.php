
<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once 'products.php';

$cartCount = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Velune Style Studio</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>VELUNE</h1>

    <nav>
        <a href="index.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="tendencias.php">Tendencias</a>
        <a href="contacto.php">Contacto</a>
<a href="mi_cuenta.php">Mi cuenta</a>

        <a href="mis_pedidos.php">Mis pedidos</a>

        <a href="cart.php">
            Carrito <span id="cart-badge"><?php echo $cartCount; ?></span>
        </a>

        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<div class="hero">

    <div class="hero-text">
        <p class="subtitulo">NUEVA TEMPORADA</p>

        <h1>
            Vestite la historia
            que querés contar
        </h1>

        <div class="hero-actions">
            <button onclick="window.location.href='catalogo.php'">
                VER CATÁLOGO
            </button>

            <button onclick="window.location.href='mis_pedidos.php'" class="secondary-btn">
                MIS PEDIDOS
            </button>
        </div>
    </div>

    <div class="hero-image">
        <div class="hero-carousel">

            <div class="hero-slide active">
                <img src="img/carrusel1.jpeg" alt="Velune">
            </div>

            <div class="hero-slide">
                <img src="img/carrusel2.jpeg" alt="Velune">
            </div>

            <div class="hero-slide">
                <img src="img/carrusel3.jpeg" alt="Velune">
            </div>

            <div class="hero-slide">
                <img src="img/carrusel4.jpeg" alt="Velune">
            </div>

            <div class="hero-slide">
                <img src="img/carrusel5.jpeg" alt="Velune">
            </div>

            <div class="hero-slide">
                <img src="img/carrusel6.jpeg" alt="Velune">
            </div>

            <div class="hero-slide">
                <img src="img/carrusel7.jpeg" alt="Velune">
            </div>

            <div class="hero-slide">
                <img src="img/carrusel8.jpeg" alt="Velune">
            </div>

            <button class="hero-control prev" type="button" aria-label="Anterior">
                &#10094;
            </button>

            <button class="hero-control next" type="button" aria-label="Siguiente">
                &#10095;
            </button>

        </div>
    </div>

</div>

<footer class="site-footer">

    <div class="footer-content">

        <div class="footer-section">
            <h3>VELUNE</h3>
            <p>
                Moda contemporánea, piezas seleccionadas y estilo atemporal
                para quienes buscan destacar con naturalidad.
            </p>
        </div>

        <div class="footer-section">
            <h4>Ayuda</h4>
            <ul>
                <li><a href="contacto.php">Contacto</a></li>
                <li><a href="catalogo.php">Catálogo</a></li>
                <li><a href="cart.php">Carrito</a></li>
                <li><a href="mis_pedidos.php">Mis pedidos</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Información</h4>
            <ul>
                <li>Envíos a todo el país</li>
                <li>Devoluciones dentro de 10 días</li>
                <li>Pagos seguros</li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Contacto</h4>
            <ul>
                <li>info@velune.com</li>
                <li>+54 11 5555-4444</li>
                <li>Buenos Aires, Argentina</li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© 2026 VELUNE. Todos los derechos reservados.</p>
    </div>

</footer>

<script src="script.js"></script>
<script src="carousel.js"></script>

</body>
</html>
