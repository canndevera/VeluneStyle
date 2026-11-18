<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) $cartCount += $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - VELUNE</title>
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
        <a href="contacto.php">Contacto</a>
        <a href="logout.php">Cerrar sesión</a>
        <a href="cart.php">Carrito <span id="cart-badge"><?php echo $cartCount; ?></span></a>
    </nav>
</header>

<main class="cart-page">
    <h2>Contacto</h2>
    <p>Escribinos y te respondemos en breve.</p>
    <form class="contact-form" action="mailto:info@velune.com" method="post" enctype="text/plain">
        <label>Nombre</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Mensaje</label>
        <textarea name="message" rows="6" required></textarea>
        <button type="submit">Enviar</button>
    </form>
</main>

<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>VELUNE</h3>
            <p>Moda contemporánea, piezas seleccionadas y estilo atemporal para quienes buscan destacar con naturalidad.</p>
        </div>
        <div class="footer-section">
            <h4>Ayuda</h4>
            <ul>
                <li><a href="contacto.php">Contacto</a></li>
                <li><a href="catalogo.php">Catálogo</a></li>
                <li><a href="cart.php">Carrito</a></li>
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

</body>
</html>
