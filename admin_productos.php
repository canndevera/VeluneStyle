<?php
session_start();

$productsFile = __DIR__ . '/products.json';

function loadProducts() {
    global $productsFile;
    if (!file_exists($productsFile)) {
        return [];
    }
    $json = file_get_contents($productsFile);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveProducts($products) {
    global $productsFile;
    file_put_contents($productsFile, json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$products = loadProducts();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $name = trim($_POST['name'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $img = trim($_POST['img'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($name === '' || $price <= 0) {
                $error = 'Completa nombre y precio válido.';
            } else {
                $newId = 1;
                if (!empty($products)) {
                    $newId = max(array_column($products, 'id')) + 1;
                }
                $products[] = ['id' => $newId, 'name' => $name, 'price' => $price, 'img' => $img, 'description' => $description];
                saveProducts($products);
                $message = 'Producto agregado correctamente.';
            }
        } elseif ($_POST['action'] === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $img = trim($_POST['img'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($id <= 0 || $name === '' || $price <= 0) {
                $error = 'Datos inválidos.';
            } else {
                foreach ($products as &$p) {
                    if ((int)$p['id'] === $id) {
                        $p['name'] = $name;
                        $p['price'] = $price;
                        $p['img'] = $img;
                        $p['description'] = $description;
                        break;
                    }
                }
                unset($p);
                saveProducts($products);
                $message = 'Producto actualizado.';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $products = array_values(array_filter($products, function ($p) use ($id) {
                return (int)$p['id'] !== $id;
            }));
            saveProducts($products);
            $message = 'Producto eliminado.';
        }
    }
}

$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($products as $p) {
        if ((int)$p['id'] === $editId) {
            $editProduct = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar productos - VELUNE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>VELUNE</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="contacto.php">Contacto</a>
    </nav>
</header>

<main class="cart-page">
    <h2>Administrar productos</h2>

    <?php if ($message): ?><p style="color:green"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <form method="post" class="login-form" style="margin-bottom:30px;">
        <input type="hidden" name="action" value="<?php echo $editProduct ? 'update' : 'create'; ?>">
        <?php if ($editProduct): ?><input type="hidden" name="id" value="<?php echo (int)$editProduct['id']; ?>">
        <?php endif; ?>

        <label for="name">Nombre</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>" required>

        <label for="price">Precio</label>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars((string)($editProduct['price'] ?? '')); ?>" required>

        <label for="img">Imagen</label>
        <input type="text" id="img" name="img" value="<?php echo htmlspecialchars($editProduct['img'] ?? ''); ?>">

        <label for="description">Descripción</label>
        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>

        <button type="submit"><?php echo $editProduct ? 'Guardar cambios' : 'Agregar producto'; ?></button>
    </form>

    <table class="cart-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?php echo (int)$p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td>$<?php echo number_format((float)$p['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($p['img']); ?></td>
                <td>
                    <a href="admin_productos.php?edit=<?php echo (int)$p['id']; ?>">Editar</a> |
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
