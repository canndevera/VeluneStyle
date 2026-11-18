
<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once 'products.php';

$cart = $_SESSION['cart'] ?? [];

$cartCount = 0;
$total = 0;

if (!empty($cart)) {
    foreach ($cart as $item) {
        $cartCount += (int)$item['qty'];
        $total += (float)$item['price'] * (int)$item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Carrito - VELUNE</title>

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
        <a href="cart.php">
            Carrito <span id="cart-badge"><?php echo $cartCount; ?></span>
        </a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<main class="cart-page">

    <h2>Tu Carrito</h2>

    <?php if (empty($cart)): ?>

        <p>
            Tu carrito está vacío.
            <a href="catalogo.php">Seguir comprando</a>
        </p>

    <?php else: ?>

        <table class="cart-table">

            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($cart as $it):

                    $subtotal = (float)$it['price'] * (int)$it['qty'];

                ?>

                    <tr data-id="<?php echo (int)$it['id']; ?>">

                        <td class="prod">

                            <img
                                src="<?php echo htmlspecialchars($it['img']); ?>"
                                alt="<?php echo htmlspecialchars($it['name']); ?>"
                            >

                            <div>
                                <?php echo htmlspecialchars($it['name']); ?>
                            </div>

                        </td>

                        <td
                            class="unit-price"
                            data-price="<?php echo (float)$it['price']; ?>"
                        >
                            $<?php echo number_format($it['price'], 2, ',', '.'); ?>
                        </td>

                        <td>
                            <input
                                type="number"
                                min="1"
                                value="<?php echo (int)$it['qty']; ?>"
                                class="cart-qty"
                            >
                        </td>

                        <td class="subtotal">
                            $<?php echo number_format($subtotal, 2, ',', '.'); ?>
                        </td>

                        <td>
                            <button type="button" class="remove">
                                Eliminar
                            </button>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

        <div class="cart-summary">

            <p>
                Total: $<span id="cart-total"><?php echo number_format($total, 2, '.', ''); ?></span>
            </p>

            <form action="procesar_compra.php" method="POST" id="checkout-form">

                <button type="submit" id="checkout">
                    Comprar
                </button>

            </form>

        </div>

    <?php endif; ?>

</main>

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

<script>
document.addEventListener('DOMContentLoaded', () => {

    // CAMBIAR CANTIDADES
    document.querySelectorAll('.cart-qty').forEach(input => {

        input.addEventListener('change', async (e) => {

            const tr = e.target.closest('tr');
            const id = parseInt(tr.dataset.id);
            const qty = Math.max(1, parseInt(e.target.value) || 1);

            e.target.value = qty;

            try {

                const resultado = await updateCart(id, qty);

                if (!resultado.ok) {
                    alert('No se pudo actualizar el carrito.');
                    window.location.reload();
                    return;
                }

                const price = parseFloat(
                    tr.querySelector('.unit-price').dataset.price
                );

                tr.querySelector('.subtotal').textContent =
                    '$' + (price * qty).toFixed(2);

                const res = await fetchCart();

                document.getElementById('cart-total').textContent =
                    res.total.toFixed(2);

                document.getElementById('cart-badge').textContent =
                    res.count;

            } catch (error) {

                alert('Ocurrió un error al actualizar la cantidad.');
                window.location.reload();

            }

        });

    });


    // ELIMINAR PRODUCTOS
    document.querySelectorAll('.remove').forEach(button => {

        button.addEventListener('click', async (e) => {

            const tr = e.target.closest('tr');
            const id = parseInt(tr.dataset.id);

            try {

                const resultado = await updateCart(id, 0);

                if (!resultado.ok) {
                    alert('No se pudo eliminar el producto.');
                    return;
                }

                tr.remove();

                const res = await fetchCart();

                document.getElementById('cart-badge').textContent =
                    res.count;

                if (res.items.length === 0) {
                    window.location.reload();
                    return;
                }

                document.getElementById('cart-total').textContent =
                    res.total.toFixed(2);

            } catch (error) {

                alert('Ocurrió un error al eliminar el producto.');
                window.location.reload();

            }

        });

    });

});
</script>

</body>
</html>
