
<?php
session_start();
include("conexion.php");

$error = '';
$mensaje = '';

$modo = $_GET['modo'] ?? $_POST['modo'] ?? 'login';

if (!in_array($modo, ['login', 'registro'], true)) {
    $modo = 'login';
}

// Mensaje de registro exitoso
if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $mensaje = '¡Tu cuenta fue creada correctamente! Ahora podés iniciar sesión.';
    $modo = 'login';
}

// PROCESAR FORMULARIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // REGISTRO
    if ($modo === 'registro') {

        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($usuario === '' || $password === '' || $confirmPassword === '') {
            $error = 'Completá todos los campos.';
        } elseif (strlen($password) < 4) {
            $error = 'La contraseña debe tener al menos 4 caracteres.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Las contraseñas no coinciden.';
        } else {

            $check = $conexion->prepare(
                "SELECT id FROM usuarios WHERE usuario = ?"
            );

            $check->bind_param("s", $usuario);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {

                $error = 'Ese nombre de usuario ya existe.';

            } else {

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert = $conexion->prepare(
                    "INSERT INTO usuarios (usuario, contraseña) VALUES (?, ?)"
                );

                $insert->bind_param("ss", $usuario, $passwordHash);

                if ($insert->execute()) {
                    header('Location: login.php?registro=ok');
                    exit;
                } else {
                    $error = 'No se pudo crear la cuenta.';
                }

                $insert->close();
            }

            $check->close();
        }
    }

    // INICIO DE SESIÓN
    else {

        if ($usuario === '' || $password === '') {
            $error = 'Completá tu usuario y contraseña.';
        } else {

            $stmt = $conexion->prepare(
                "SELECT id, contraseña FROM usuarios WHERE usuario = ?"
            );

            $stmt->bind_param("s", $usuario);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {

                if (password_verify($password, $row['contraseña'])) {

                    session_regenerate_id(true);

                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $usuario;
                    $_SESSION['user_id'] = (int)$row['id'];

                    header('Location: index.php');
                    exit;

                } else {
                    $error = 'El usuario o la contraseña son incorrectos.';
                }

            } else {
                $error = 'El usuario o la contraseña son incorrectos.';
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $modo === 'registro' ? 'Crear cuenta' : 'Iniciar sesión'; ?> | VELUNE
    </title>

    <meta name="description" content="Ingresá o creá tu cuenta en Velune Moda. Descubrí nuestra colección de moda contemporánea.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>

<body class="login-body">

<main class="login-layout">

    <section class="login-visual">

        <div class="login-brand">
            VELUNE
            <span>STYLE STUDIO</span>
        </div>

        <div class="login-visual-text">
            <p>MODA · ESTILO · IDENTIDAD</p>
            <h1>Tu estilo,<br>tu historia.</h1>
            <span>Vestite como querés sentirte.</span>
        </div>

        <div class="login-visual-footer">
            COLECCIÓN CONTEMPORÁNEA — 2026
        </div>

    </section>

    <section class="login-side">

        <div class="login-card">

            <div class="login-monogram">V.</div>

            <p class="login-eyebrow">BIENVENIDA A VELUNE</p>

            <?php if ($modo === 'login'): ?>

                <h2>Iniciá sesión</h2>

                <p class="login-description">
                    Ingresá a tu cuenta y descubrí tu próximo estilo favorito.
                </p>

            <?php else: ?>

                <h2>Crear tu cuenta</h2>

                <p class="login-description">
                    Unite a Velune y empezá a descubrir tu propio estilo.
                </p>

            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="login-success">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="login-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form login-form-new">

                <input type="hidden" name="modo"
                       value="<?php echo htmlspecialchars($modo); ?>">

                <label for="username">Nombre de usuario</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Ingresá tu usuario"
                    autocomplete="username"
                    required
                >

                <label for="password">Contraseña</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingresá tu contraseña"
                    autocomplete="<?php echo $modo === 'registro' ? 'new-password' : 'current-password'; ?>"
                    required
                >

                <?php if ($modo === 'registro'): ?>

                    <label for="confirm_password">Repetir contraseña</label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirmá tu contraseña"
                        autocomplete="new-password"
                        required
                    >

                <?php endif; ?>

                <button type="submit">

                    <?php if ($modo === 'login'): ?>
                        INGRESAR A MI CUENTA
                    <?php else: ?>
                        CREAR MI CUENTA
                    <?php endif; ?>

                </button>

            </form>

            <div class="login-register">

                <?php if ($modo === 'login'): ?>

                    <span>¿Todavía no tenés una cuenta?</span>

                    <a href="login.php?modo=registro">
                        Registrarme
                    </a>

                <?php else: ?>

                    <span>¿Ya tenés una cuenta?</span>

                    <a href="login.php">
                        Iniciar sesión
                    </a>

                <?php endif; ?>

            </div>

            <div class="login-back">
                <a href="catalogo.php">Explorar la colección</a>
            </div>

        </div>

        <p class="login-copyright">
            © 2026 VELUNE — TODOS LOS DERECHOS RESERVADOS
        </p>

    </section>

</main>

</body>
</html>
