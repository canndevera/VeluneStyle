
<?php
session_start();

if (
    !isset($_SESSION["es_admin"]) ||
    $_SESSION["es_admin"] !== true ||
    !isset($_SESSION["admin_id"])
) {
    header("Location: admin_login.php");
    exit;
}

require_once "conexion.php";

$mensaje = "";
$error = "";

$categorias = [
    "Blusas y tops",
    "Faldas",
    "Abrigos",
    "Accesorios",
    "Blazers"
];

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

// CERRAR SESIÓN
if (isset($_POST["cerrar_sesion"])) {
    unset($_SESSION["admin_id"]);
    unset($_SESSION["es_admin"]);
    header("Location: admin_login.php");
    exit;
}

// AGREGAR PRODUCTO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["agregar"])) {

    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = (float)($_POST["precio"] ?? 0);
    $imagen = trim($_POST["imagen"] ?? "");
    $categoria = $_POST["categoria"] ?? "";
    $stock = filter_var($_POST["stock"] ?? null, FILTER_VALIDATE_INT);

    if (
        $nombre === "" ||
        $precio <= 0 ||
        $stock === false ||
        $stock === null ||
        $stock < 0 ||
        !in_array($categoria, $categorias, true)
    ) {
        $error = "Completá correctamente todos los campos obligatorios.";
    } else {
        $stmt = $conexion->prepare(
            "INSERT INTO productos
            (nombre, descripcion, precio, imagen, categoria, stock, activo)
            VALUES (?, ?, ?, ?, ?, ?, 1)"
        );

        $stmt->bind_param(
            "ssdssi",
            $nombre,
            $descripcion,
            $precio,
            $imagen,
            $categoria,
            $stock
        );

        if ($stmt->execute()) {
            $mensaje = "Producto agregado correctamente.";
        } else {
            $error = "No se pudo agregar el producto.";
        }

        $stmt->close();
    }
}

// MODIFICAR PRODUCTO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["modificar"])) {

    $id = (int)($_POST["id"] ?? 0);
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = (float)($_POST["precio"] ?? 0);
    $imagen = trim($_POST["imagen"] ?? "");
    $categoria = $_POST["categoria"] ?? "";
    $stock = filter_var($_POST["stock"] ?? null, FILTER_VALIDATE_INT);

    if (
        $id <= 0 ||
        $nombre === "" ||
        $precio <= 0 ||
        $stock === false ||
        $stock === null ||
        $stock < 0 ||
        !in_array($categoria, $categorias, true)
    ) {
        $error = "Revisá los datos del producto.";
    } else {
        $stmt = $conexion->prepare(
            "UPDATE productos
             SET nombre = ?, descripcion = ?, precio = ?,
                 imagen = ?, categoria = ?, stock = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssdssii",
            $nombre,
            $descripcion,
            $precio,
            $imagen,
            $categoria,
            $stock,
            $id
        );

        if ($stmt->execute()) {
            $mensaje = "Producto actualizado correctamente.";
        } else {
            $error = "No se pudo modificar el producto.";
        }

        $stmt->close();
    }
}

// DESACTIVAR PRODUCTO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["desactivar"])) {

    $id = (int)($_POST["id"] ?? 0);

    $stmt = $conexion->prepare(
        "UPDATE productos SET activo = 0 WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $mensaje = "Producto desactivado correctamente.";
    } else {
        $error = "No se pudo desactivar el producto.";
    }

    $stmt->close();
}

// ACTIVAR PRODUCTO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["activar"])) {

    $id = (int)($_POST["id"] ?? 0);

    $stmt = $conexion->prepare(
        "UPDATE productos SET activo = 1 WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $mensaje = "Producto activado correctamente.";
    } else {
        $error = "No se pudo activar el producto.";
    }

    $stmt->close();
}

// BUSCAR PRODUCTO PARA EDITAR
$productoEditar = null;

if (isset($_GET["editar"])) {

    $idEditar = (int)$_GET["editar"];

    $stmt = $conexion->prepare(
        "SELECT * FROM productos WHERE id = ?"
    );

    $stmt->bind_param("i", $idEditar);
    $stmt->execute();

    $resultadoEditar = $stmt->get_result();
    $productoEditar = $resultadoEditar->fetch_assoc();

    $stmt->close();
}

// OBTENER TODOS LOS PRODUCTOS
$resultado = $conexion->query(
    "SELECT * FROM productos ORDER BY id DESC"
);

$productos = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Gestión de productos | VELUNE</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="style.css">

<style>

.productos-container {
    max-width: 1100px;
    margin: 50px auto;
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
    font-size: 13px;
}

.menu-admin {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 25px;
    padding: 20px;
}

.menu-admin a {
    color: #292725;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 1px;
}

.menu-admin a:hover {
    color: #8C8177;
}

.formulario {
    background: #F7F2EC;
    border: 1px solid #DED5CA;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 35px;
}

.formulario h2 {
    font-family: 'Playfair Display', serif;
    font-weight: 400;
    font-size: 25px;
    margin-top: 0;
}

.campos {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.campo {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.campo.completo {
    grid-column: 1 / -1;
}

.campo label {
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #756B62;
}

.campo input,
.campo textarea,
.campo select {
    padding: 13px;
    border: 1px solid #D9CCBC;
    background: #FBF8F4;
    color: #292725;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    width: 100%;
    box-sizing: border-box;
}

.campo textarea {
    min-height: 90px;
    resize: vertical;
}

.boton {
    display: inline-block;
    border: none;
    padding: 13px 22px;
    margin-top: 20px;
    background: #292725;
    color: #F7F2EC;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
}

.boton:hover {
    background: #8C8177;
}

.boton-secundario {
    display: inline-block;
    margin-top: 20px;
    margin-left: 10px;
    color: #756B62;
    font-size: 11px;
    text-decoration: none;
}

.mensaje,
.error {
    padding: 14px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 12px;
}

.mensaje {
    background: #E5EBDD;
    color: #40513A;
}

.error {
    background: #F2E3DF;
    color: #81483E;
}

.lista-titulo {
    font-family: 'Playfair Display', serif;
    font-size: 27px;
    font-weight: 400;
    margin: 35px 0 20px;
}

.producto-card {
    background: #F7F2EC;
    border: 1px solid #DED5CA;
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
}

.producto-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 21px;
    font-weight: 400;
    margin: 0 0 8px;
}

.producto-info p {
    font-size: 12px;
    color: #81776E;
    margin: 5px 0;
}

.producto-acciones {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.producto-acciones form {
    margin: 0;
}

.boton-pequeno {
    border: 1px solid #B8AEA3;
    background: transparent;
    color: #292725;
    padding: 10px 14px;
    font-size: 10px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.boton-pequeno:hover {
    background: #EDE5DA;
}

.estado-producto {
    font-size: 10px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #53604B;
}

.estado-inactivo {
    color: #81483E;
}

@media(max-width: 650px) {
    .productos-container {
        padding: 15px;
    }

    .encabezado h1 {
        font-size: 30px;
    }

    .campos {
        grid-template-columns: 1fr;
    }

    .campo.completo {
        grid-column: auto;
    }

    .formulario {
        padding: 20px;
    }

    .producto-card {
        align-items: flex-start;
    }
}

</style>

</head>

<body>

<header>
    <nav class="menu-admin">
        <a href="index.php">VELUNE</a>
        <a href="gestionar_pedidos.php">Pedidos</a>
        <a href="gestionar_productos.php">Productos</a>
        <a href="admin_login.php">Cerrar sesión</a>
    </nav>
</header>

<main class="productos-container">

    <section class="encabezado">
        <span>Administración</span>
        <h1>Gestión de productos</h1>
        <p>Agregá, modificá y administrá los productos de VELUNE.</p>
    </section>

    <?php if ($mensaje): ?>
        <div class="mensaje">
            <?= escapar($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?= escapar($error) ?>
        </div>
    <?php endif; ?>

    <section class="formulario">

        <?php if ($productoEditar): ?>

            <h2>Modificar producto</h2>

            <form method="POST">

                <input type="hidden" name="id" value="<?= (int)$productoEditar['id'] ?>">

                <div class="campos">

                    <div class="campo">
                        <label>Nombre del producto</label>
                        <input type="text" name="nombre" required value="<?= escapar($productoEditar['nombre']) ?>">
                    </div>

                    <div class="campo">
                        <label>Precio</label>
                        <input type="number" name="precio" step="0.01" min="0.01" required value="<?= escapar($productoEditar['precio']) ?>">
                    </div>

                    <div class="campo">
                        <label>Categoría</label>
                        <select name="categoria" required>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= escapar($categoria) ?>" <?= $productoEditar['categoria'] === $categoria ? 'selected' : '' ?>>
                                    <?= escapar($categoria) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Stock</label>
                        <input type="number" name="stock" min="0" required value="<?= (int)$productoEditar['stock'] ?>">
                    </div>

                    <div class="campo completo">
                        <label>Descripción</label>
                        <textarea name="descripcion"><?= escapar($productoEditar['descripcion']) ?></textarea>
                    </div>

                    <div class="campo completo">
                        <label>Ruta de imagen</label>
                        <input type="text" name="imagen" value="<?= escapar($productoEditar['imagen']) ?>" placeholder="img/img1.jpeg">
                    </div>

                </div>

                <button type="submit" name="modificar" class="boton">
                    Guardar cambios
                </button>

                <a href="gestionar_productos.php" class="boton-secundario">
                    Cancelar
                </a>

            </form>

        <?php else: ?>

            <h2>Agregar nuevo producto</h2>

            <form method="POST">

                <div class="campos">

                    <div class="campo">
                        <label>Nombre del producto</label>
                        <input type="text" name="nombre" required>
                    </div>

                    <div class="campo">
                        <label>Precio</label>
                        <input type="number" name="precio" step="0.01" min="0.01" required>
                    </div>

                    <div class="campo">
                        <label>Categoría</label>
                        <select name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= escapar($categoria) ?>">
                                    <?= escapar($categoria) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label>Stock</label>
                        <input type="number" name="stock" min="0" value="10" required>
                    </div>

                    <div class="campo completo">
                        <label>Descripción</label>
                        <textarea name="descripcion"></textarea>
                    </div>

                    <div class="campo completo">
                        <label>Ruta de imagen</label>
                        <input type="text" name="imagen" placeholder="img/img1.jpeg">
                    </div>

                </div>

                <button type="submit" name="agregar" class="boton">
                    Agregar producto
                </button>

            </form>

        <?php endif; ?>

    </section>

    <h2 class="lista-titulo">Productos registrados</h2>

    <?php foreach ($productos as $producto): ?>

        <article class="producto-card">

            <div class="producto-info">

                <h3><?= escapar($producto['nombre']) ?></h3>

                <p>
                    Categoría: <?= escapar($producto['categoria']) ?>
                </p>

                <p>
                    Precio: $<?= number_format((float)$producto['precio'], 2, ',', '.') ?>
                </p>

                <p>
                    Stock: <?= (int)$producto['stock'] ?>
                </p>

                <p>
                    <?= !empty($producto['activo'])
                        ? '<span class="estado-producto">Activo</span>'
                        : '<span class="estado-producto estado-inactivo">Inactivo</span>' ?>
                </p>

            </div>

            <div class="producto-acciones">

                <a
                    href="gestionar_productos.php?editar=<?= (int)$producto['id'] ?>"
                    class="boton-pequeno"
                >
                    Modificar
                </a>

                <?php if (!empty($producto['activo'])): ?>

                    <form method="POST" onsubmit="return confirm('¿Querés desactivar este producto?');">
                        <input type="hidden" name="id" value="<?= (int)$producto['id'] ?>">
                        <button type="submit" name="desactivar" class="boton-pequeno">
                            Desactivar
                        </button>
                    </form>

                <?php else: ?>

                    <form method="POST">
                        <input type="hidden" name="id" value="<?= (int)$producto['id'] ?>">
                        <button type="submit" name="activar" class="boton-pequeno">
                            Activar
                        </button>
                    </form>

                <?php endif; ?>

            </div>

        </article>

    <?php endforeach; ?>

    <?php if (count($productos) === 0): ?>
        <p>Todavía no hay productos registrados.</p>
    <?php endif; ?>

    <form method="POST" style="text-align:center; margin:35px 0;">
        <button type="submit" name="cerrar_sesion" class="boton">
            Cerrar sesión de administrador
        </button>
    </form>

</main>

</body>
</html>
