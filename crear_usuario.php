<?php
session_start();
include("conexion.php");

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($usuario === '' || $password === '' || $confirmPassword === '') {
        $error = 'Completá todos los campos.';
    } elseif (strlen($password) < 4) {
        $error = 'La contraseña debe tener al menos 4 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $check = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $check->bind_param("s", $usuario);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Ese usuario ya existe.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conexion->prepare("INSERT INTO usuarios (usuario, contraseña) VALUES (?, ?)");
            $insert->bind_param("ss", $usuario, $passwordHash);

            if ($insert->execute()) {
                $message = 'Usuario creado correctamente.';
                $_SESSION['message'] = $message;
                header('Location: login.php');
                exit;
            } else {
                $error = 'No se pudo crear el usuario.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario - VELUNE</title>
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
        <h2>Crear usuario</h2>
        <form method="post" class="login-form">
            <label for="username">Usuario</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Repetir contraseña</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Crear cuenta</button>
        </form>

        <?php if ($error): ?>
            <p class="login-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <p class="login-help"><a href="login.php">Volver al login</a></p>
    </main>
</body>
</html>