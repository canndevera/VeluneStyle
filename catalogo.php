
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
        $cartCount += (int)$item['qty'];
    }
}

$categorias = [];

foreach ($products as $producto) {
    $categoria = trim($producto['category'] ?? '');

    if ($categoria !== '' && !in_array($categoria, $categorias, true)) {
        $categorias[] = $categoria;
    }
}

sort($categorias);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catálogo | VELUNE</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

    <style>
        .catalogo {
            max-width: 1250px;
            margin: 55px auto;
            padding: 0 25px;
        }

        .catalogo-intro {
            text-align: center;
            margin-bottom: 35px;
        }

        .catalogo-intro .eyebrow {
            font-size: 11px;
            letter-spacing: 3px;
            color: #8C8177;
            margin-bottom: 12px;
        }

        .catalogo-intro h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 500;
            color: #292725;
            margin-bottom: 12px;
        }

        .catalogo-intro p {
            color: #77716A;
            font-size: 14px;
            line-height: 1.7;
        }

        .catalogo-filtros {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin: 35px 0;
        }

        .catalogo-busqueda,
        .catalogo-select {
            background: #F7F2EC;
            border: 1px solid #D9CCBC;
            padding: 13px 16px;
            border-radius: 5px;
            color: #292725;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            outline: none;
        }

        .catalogo-busqueda {
            width: 250px;
        }

        .catalogo-busqueda:focus,
        .catalogo-select:focus {
            border-color: #8C8177;
        }

        .btn-guia,
        .btn-ver-favoritos {
            padding: 13px 17px;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            cursor: pointer;
            transition: .2s;
        }

        .btn-guia {
            background: #292725;
            color: #F7F2EC;
            border: 1px solid #292725;
        }

        .btn-guia:hover {
            background: #8C8177;
            border-color: #8C8177;
        }

        .btn-ver-favoritos {
            background: #EDE5DA;
            color: #292725;
            border: 1px solid #D9CCBC;
        }

        .btn-ver-favoritos:hover,
        .btn-ver-favoritos.activo {
            background: #292725;
            color: white;
        }

        .catalogo-resultados {
            color: #8C8177;
            font-size: 12px;
            text-align: center;
            margin-bottom: 25px;
        }

        .catalogo-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .catalog-card {
            background: #F7F2EC;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #E5DDD3;
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .catalog-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(41, 39, 37, .08);
        }

        .catalog-card-image {
            width: 100%;
            aspect-ratio: 3 / 4;
            overflow: hidden;
            background: #EDE5DA;
            position: relative;
        }

        .catalog-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .4s ease;
        }

        .catalog-card:hover .catalog-card-img {
            transform: scale(1.04);
        }

        .btn-favorito {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 50%;
            background: #F7F2EC;
            color: #8C8177;
            font-size: 22px;
            cursor: pointer;
            z-index: 2;
            transition: .2s;
        }

        .btn-favorito:hover {
            transform: scale(1.12);
        }

        .btn-favorito.activo {
            color: #A65D65;
        }

        .catalog-card-body {
            padding: 17px;
        }

        .catalog-card-body h3 {
            font-family: 'Playfair Display', serif;
            font-size: 19px;
            font-weight: 500;
            color: #292725;
            margin-bottom: 8px;
        }

        .catalog-card-body p {
            font-size: 12px;
            line-height: 1.6;
            color: #77716A;
        }

        .catalog-card-body .categoria {
            color: #8C8177;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .catalog-card-body .price {
            color: #292725;
            font-size: 17px;
            font-weight: 600;
            margin: 13px 0;
        }

        .catalog-stock {
            font-size: 11px;
            color: #77716A;
            margin-bottom: 12px;
        }

        .catalog-card .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .catalog-card .qty {
            width: 55px;
            min-width: 0;
            padding: 10px 5px;
            border: 1px solid #D9CCBC;
            background: #F8F7F4;
            border-radius: 4px;
            color: #292725;
        }

        .catalog-card .actions button {
            flex: 1;
            padding: 11px 8px;
            border: none;
            border-radius: 4px;
            background: #292725;
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            cursor: pointer;
            transition: opacity .2s;
        }

        .catalog-card .actions button:hover {
            opacity: .8;
        }

        .catalog-card .actions button:disabled {
            background: #aaa39b;
            cursor: not-allowed;
        }

        .catalogo-vacio {
            display: none;
            text-align: center;
            padding: 55px 20px;
            background: #F7F2EC;
            border-radius: 10px;
            color: #77716A;
        }

        .catalogo-vacio h3 {
            font-family: 'Playfair Display', serif;
            font-size: 25px;
            color: #292725;
            margin-bottom: 10px;
        }

        /* GUIA DE TALLES */

        .guia-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(41, 39, 37, .60);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .guia-overlay.activa {
            display: flex;
        }

        .guia-modal {
            background: #F7F2EC;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 35px;
            border-radius: 10px;
            position: relative;
            animation: aparecer .25s ease;
        }

        @keyframes aparecer {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .guia-cerrar {
            position: absolute;
            top: 15px;
            right: 18px;
            background: none;
            border: none;
            font-size: 25px;
            color: #77716A;
            cursor: pointer;
        }

        .guia-modal .guia-eyebrow {
            color: #8C8177;
            font-size: 10px;
            letter-spacing: 3px;
            text-align: center;
            margin-bottom: 10px;
        }

        .guia-modal h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 500;
            text-align: center;
            color: #292725;
            margin-bottom: 12px;
        }

        .guia-descripcion {
            text-align: center;
            color: #77716A;
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .guia-tabla-contenedor {
            overflow-x: auto;
        }

        .guia-tabla {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
        }

        .guia-tabla th {
            background: #292725;
            color: #F7F2EC;
            padding: 13px 9px;
            font-weight: 500;
        }

        .guia-tabla td {
            padding: 13px 9px;
            border-bottom: 1px solid #DED4C9;
            color: #55504A;
        }

        .guia-tabla tr:nth-child(even) {
            background: #EDE5DA;
        }

        .guia-nota {
            margin-top: 22px;
            padding: 17px;
            background: #EDE5DA;
            border-radius: 6px;
        }

        .guia-nota h3 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 500;
            color: #292725;
            margin-bottom: 10px;
        }

        .guia-nota p {
            font-size: 12px;
            line-height: 1.8;
            color: #77716A;
            margin-bottom: 5px;
        }

        .guia-aviso {
            margin-top: 17px;
            font-size: 11px;
            color: #8C8177;
            line-height: 1.6;
            text-align: center;
        }

        @media (max-width: 950px) {
            .catalogo-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .catalogo-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 13px;
            }

            .catalogo-intro h2 {
                font-size: 32px;
            }

            .catalogo-filtros {
                flex-direction: column;
            }

            .catalogo-busqueda,
            .catalogo-select,
            .btn-guia,
            .btn-ver-favoritos {
                width: 100%;
            }

            .catalog-card-body {
                padding: 12px;
            }

            .catalog-card-body h3 {
                font-size: 16px;
            }

            .guia-modal {
                padding: 30px 17px;
            }

            .guia-modal h2 {
                font-size: 27px;
            }
        }

        @media (max-width: 380px) {
            .catalogo-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>VELUNE</h1>

    <nav>
        <a href="index.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="contacto.php">Contacto</a>
        <a href="mis_pedidos.php">Mis pedidos</a>
        <a href="cart.php">
            Carrito <span id="cart-badge"><?php echo $cartCount; ?></span>
        </a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>

    <section class="catalogo">

        <div class="catalogo-intro">
            <p class="eyebrow">VELUNE · STYLE STUDIO</p>

            <h2>Descubrí tu estilo</h2>

            <p>
                Explorá nuestra selección de moda y accesorios.
                Encontrá piezas que se adapten a vos y a tu forma de expresarte.
            </p>
        </div>

        <div class="catalogo-filtros">

            <input
                type="search"
                id="buscarProducto"
                class="catalogo-busqueda"
                placeholder="Buscar productos..."
            >

            <select id="filtrarCategoria" class="catalogo-select">
                <option value="">Todas las categorías</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($categoria); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="btn-guia" onclick="abrirGuia()">
                Guía de talles
            </button>

            <button
                type="button"
                class="btn-ver-favoritos"
                id="verFavoritos"
            >
                ♡ Ver favoritos
            </button>

        </div>

        <p class="catalogo-resultados" id="cantidadResultados">
            <?php echo count($products); ?> productos disponibles
        </p>

        <div class="catalogo-grid" id="catalogoProductos">

            <?php foreach ($products as $p): ?>

                <?php
                    $stock = (int)($p['stock'] ?? 0);
                    $categoria = trim($p['category'] ?? '');
                ?>

                <article
                    class="catalog-card"
                    data-nombre="<?php echo htmlspecialchars(mb_strtolower($p['name']), ENT_QUOTES, 'UTF-8'); ?>"
                    data-categoria="<?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>"
                >

                    <div class="catalog-card-image">

                        <button
                            type="button"
                            class="btn-favorito"
                            data-id="<?php echo (int)$p['id']; ?>"
                            aria-label="Agregar a favoritos"
                        >
                            ♡
                        </button>

                        <img
                            src="<?php echo htmlspecialchars($p['img']); ?>"
                            alt="<?php echo htmlspecialchars($p['name']); ?>"
                            class="catalog-card-img"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='img/chicas.jpeg';"
                        >

                    </div>

                    <div class="catalog-card-body">

                        <p class="categoria">
                            <?php echo htmlspecialchars($categoria ?: 'Moda'); ?>
                        </p>

                        <h3>
                            <?php echo htmlspecialchars($p['name'] ?: 'Producto'); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($p['description'] ?? ''); ?>
                        </p>

                        <p class="price">
                            $<?php echo number_format((float)$p['price'], 2, ',', '.'); ?>
                        </p>

                        <p class="catalog-stock">
                            <?php if ($stock > 0): ?>
                                <?php echo $stock; ?> unidades disponibles
                            <?php else: ?>
                                Sin stock disponible
                            <?php endif; ?>
                        </p>

                        <div class="actions">

                            <input
                                type="number"
                                value="1"
                                min="1"
                                max="<?php echo $stock; ?>"
                                class="qty"
                                id="qty-<?php echo (int)$p['id']; ?>"
                                <?php echo $stock <= 0 ? 'disabled' : ''; ?>
                            >

                            <button
                                onclick="addToCart(<?php echo (int)$p['id']; ?>)"
                                <?php echo $stock <= 0 ? 'disabled' : ''; ?>
                            >
                                <?php echo $stock > 0 ? 'Agregar al carrito' : 'Agotado'; ?>
                            </button>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

        <div class="catalogo-vacio" id="catalogoVacio">
            <h3 id="mensajeVacio">No encontramos productos</h3>
            <p>Probá con otra búsqueda o seleccioná una categoría diferente.</p>
        </div>

    </section>

</main>

<!-- VENTANA DE GUIA DE TALLES -->

<div class="guia-overlay" id="guiaOverlay" onclick="cerrarGuiaFondo(event)">

    <div class="guia-modal">

        <button
            type="button"
            class="guia-cerrar"
            onclick="cerrarGuia()"
            aria-label="Cerrar"
        >
            &times;
        </button>

        <p class="guia-eyebrow">VELUNE · ENCONTRÁ TU TALLE</p>

        <h2>Guía de talles</h2>

        <p class="guia-descripcion">
            Queremos ayudarte a encontrar el talle que mejor se adapte a vos.
            Consultá nuestra tabla orientativa de medidas.
        </p>

        <div class="guia-tabla-contenedor">

            <table class="guia-tabla">

                <thead>
                    <tr>
                        <th>Talle</th>
                        <th>Busto</th>
                        <th>Cintura</th>
                        <th>Cadera</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>XS</td>
                        <td>80-84 cm</td>
                        <td>60-64 cm</td>
                        <td>86-90 cm</td>
                    </tr>

                    <tr>
                        <td>S</td>
                        <td>85-89 cm</td>
                        <td>65-69 cm</td>
                        <td>91-95 cm</td>
                    </tr>

                    <tr>
                        <td>M</td>
                        <td>90-94 cm</td>
                        <td>70-74 cm</td>
                        <td>96-100 cm</td>
                    </tr>

                    <tr>
                        <td>L</td>
                        <td>95-101 cm</td>
                        <td>75-81 cm</td>
                        <td>101-107 cm</td>
                    </tr>

                    <tr>
                        <td>XL</td>
                        <td>102-108 cm</td>
                        <td>82-88 cm</td>
                        <td>108-114 cm</td>
                    </tr>
                </tbody>

            </table>

        </div>

        <div class="guia-nota">

            <h3>¿Cómo tomar tus medidas?</h3>

            <p>
                <strong>Busto:</strong>
                Medí alrededor de la parte más amplia del pecho.
            </p>

            <p>
                <strong>Cintura:</strong>
                Medí la parte más estrecha del torso.
            </p>

            <p>
                <strong>Cadera:</strong>
                Medí alrededor de la parte más ancha de la cadera.
            </p>

            <p>
                <strong>Consejo:</strong>
                Usá una cinta métrica flexible y mantenela recta,
                sin apretar demasiado.
            </p>

        </div>

        <p class="guia-aviso">
            Las medidas son orientativas y pueden variar según el diseño,
            el corte y la tela de cada prenda.
        </p>

    </div>

</div>

<footer class="site-footer">

    <div class="footer-content">

        <div class="footer-section">
            <h3>VELUNE</h3>
            <p>
                Moda, estilo e identidad. Una selección pensada para
                acompañar distintas formas de expresarse.
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

<script>
document.addEventListener('DOMContentLoaded', function() {

    const buscador = document.getElementById('buscarProducto');
    const filtro = document.getElementById('filtrarCategoria');
    const tarjetas = document.querySelectorAll('.catalog-card');
    const vacio = document.getElementById('catalogoVacio');
    const cantidad = document.getElementById('cantidadResultados');
    const botonFavoritos = document.getElementById('verFavoritos');

    let favoritos = [];

    try {
        favoritos = JSON.parse(
            localStorage.getItem('veluneFavoritos') || '[]'
        );

        if (!Array.isArray(favoritos)) {
            favoritos = [];
        }

        favoritos = favoritos.map(Number);

    } catch (error) {
        favoritos = [];
    }

    let mostrandoFavoritos = false;

    function guardarFavoritos() {
        localStorage.setItem(
            'veluneFavoritos',
            JSON.stringify(favoritos)
        );
    }

    function actualizarFavoritos() {

        document.querySelectorAll('.btn-favorito').forEach(function(boton) {

            const id = Number(boton.dataset.id);
            const activo = favoritos.includes(id);

            boton.classList.toggle('activo', activo);
            boton.textContent = activo ? '♥' : '♡';

            boton.setAttribute(
                'aria-label',
                activo ? 'Quitar de favoritos' : 'Agregar a favoritos'
            );

        });

        botonFavoritos.textContent = mostrandoFavoritos
            ? '♥ Ver todos'
            : '♡ Ver favoritos';

        botonFavoritos.classList.toggle(
            'activo',
            mostrandoFavoritos
        );
    }

    function filtrarProductos() {

        const texto = buscador.value.toLowerCase().trim();
        const categoria = filtro.value.toLowerCase();

        let visibles = 0;

        tarjetas.forEach(function(tarjeta) {

            const nombre = tarjeta.dataset.nombre.toLowerCase();
            const cat = tarjeta.dataset.categoria.toLowerCase();

            const id = Number(
                tarjeta.querySelector('.btn-favorito').dataset.id
            );

            const coincideTexto = nombre.includes(texto);

            const coincideCategoria =
                categoria === '' || cat === categoria;

            const coincideFavoritos =
                !mostrandoFavoritos || favoritos.includes(id);

            if (
                coincideTexto &&
                coincideCategoria &&
                coincideFavoritos
            ) {
                tarjeta.style.display = '';
                visibles++;
            } else {
                tarjeta.style.display = 'none';
            }

        });

        cantidad.textContent = visibles + (
            visibles === 1
                ? ' producto disponible'
                : ' productos disponibles'
        );

        vacio.style.display = visibles === 0 ? 'block' : 'none';

        const mensajeVacio = document.getElementById('mensajeVacio');

        if (mostrandoFavoritos && favoritos.length === 0) {
            mensajeVacio.textContent = 'Todavía no guardaste favoritos';
        } else if (mostrandoFavoritos && visibles === 0) {
            mensajeVacio.textContent = 'No encontramos favoritos con esa búsqueda';
        } else {
            mensajeVacio.textContent = 'No encontramos productos';
        }
    }

    document.querySelectorAll('.btn-favorito').forEach(function(boton) {

        boton.addEventListener('click', function() {

            const id = Number(boton.dataset.id);

            if (favoritos.includes(id)) {
                favoritos = favoritos.filter(function(item) {
                    return item !== id;
                });
            } else {
                favoritos.push(id);
            }

            guardarFavoritos();
            actualizarFavoritos();
            filtrarProductos();

        });

    });

    botonFavoritos.addEventListener('click', function() {

        mostrandoFavoritos = !mostrandoFavoritos;

        actualizarFavoritos();
        filtrarProductos();

    });

    buscador.addEventListener('input', filtrarProductos);
    filtro.addEventListener('change', filtrarProductos);

    actualizarFavoritos();
    filtrarProductos();

});

// ABRIR Y CERRAR GUIA DE TALLES

function abrirGuia() {
    document.getElementById('guiaOverlay').classList.add('activa');
    document.body.style.overflow = 'hidden';
}

function cerrarGuia() {
    document.getElementById('guiaOverlay').classList.remove('activa');
    document.body.style.overflow = '';
}

function cerrarGuiaFondo(event) {
    if (event.target.id === 'guiaOverlay') {
        cerrarGuia();
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarGuia();
    }
});
</script>

</body>
</html>
