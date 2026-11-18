
<?php
session_start();

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "conexion.php";

$usuario_id = (int)$_SESSION['user_id'];
$error = "";
$mensaje = "";

// Obtener los datos actuales
$stmt = $conexion->prepare(
    "SELECT usuario, contraseña FROM usuarios WHERE id = ?"
);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$datos) {
    die("No se encontraron los datos de la cuenta.");
}

$nombre_actual = $datos['usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nuevo_usuario = trim($_POST['usuario'] ?? '');
    $actual = $_POST['contraseña_actual'] ?? '';
    $nueva = $_POST['nueva_contraseña'] ?? '';
    $confirmar = $_POST['confirmar_contraseña'] ?? '';

    if ($nuevo_usuario === '' || $actual === '') {
        $error = "Completá tu nombre de usuario y contraseña actual.";
    } elseif (!password_verify($actual, $datos['contraseña'])) {
        $error = "La contraseña actual es incorrecta.";
    } elseif (strlen($nuevo_usuario) > 50) {
        $error = "El nombre de usuario no puede superar los 50 caracteres.";
    } else {

        // Comprobar que el nombre no esté utilizado por otra cuenta
        $stmt = $conexion->prepare(
            "SELECT id FROM usuarios WHERE usuario = ? AND id != ?"
        );
        $stmt->bind_param("si", $nuevo_usuario, $usuario_id);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($existe) {
            $error = "Ese nombre de usuario ya está en uso.";
        } elseif ($nueva !== '' && strlen($nueva) < 4) {
            $error = "La nueva contraseña debe tener al menos 4 caracteres.";
        } elseif ($nueva !== '' && $nueva !== $confirmar) {
            $error = "Las nuevas contraseñas no coinciden.";
        } else {

            if ($nueva !== '') {
                $hash = password_hash($nueva, PASSWORD_DEFAULT);

                $stmt = $conexion->prepare(
                    "UPDATE usuarios SET usuario = ?, contraseña = ? WHERE id = ?"
                );
                $stmt->bind_param("ssi", $nuevo_usuario, $hash, $usuario_id);
            } else {
                $stmt = $conexion->prepare(
                    "UPDATE usuarios SET usuario = ? WHERE id = ?"
                );
                $stmt->bind_param("si", $nuevo_usuario, $usuario_id);
            }

            if ($stmt->execute()) {
                $_SESSION['username'] = $nuevo_usuario;
                $mensaje = "Tus datos se actualizaron correctamente.";
                $nombre_actual = $nuevo_usuario;
            } else {
                $error = "No se pudieron guardar los cambios.";
            }

            $stmt->close();
        }
    }
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar cuenta | VELUNE</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="style.css">

<style>
.editar-container {
    max-width: 600px;
    margin: 65px auto;
    padding: 25px;
}

.editar-titulo {
    text-align: center;
    margin-bottom: 35px;
}

.editar-titulo span {
    color: #8C8177;
    font-size: 10px;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.editar-titulo h1 {
    font-family: 'Playfair Display', serif;
    font-size: 38px;
    font-weight: 400;
    margin: 13px 0;
}

.editar-titulo p {
    color: #81776E;
    font-size: 13px;
}

.editar-card {
    background: #F7F2EC;
    border: 1px solid #DED5CA;
    padding: 35px;
}

.campo {
    margin-bottom: 22px;
}

.campo label {
    display: block;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #756B62;
    margin-bottom: 10px;
}

.campo input {
    width: 100%;
    box-sizing: border-box;
    padding: 13px 5px;
    border: none;
    border-bottom: 1px solid #B8AEA3;
    background: transparent;
    outline: none;
    color: #292725;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
}

.campo input:focus {
    border-bottom: 1px solid #292725;
}

.separador {
    border: none;
    border-top: 1px solid #DED5CA;
    margin: 30px 0;
}

.nota {
    color: #8C8177;
    font-size: 11px;
    line-height: 1.7;
    margin-bottom: 20px;
}

.boton {
    width: 100%;
    padding: 16px;
    border: none;
    background: #292725;
    color: #F7F2EC;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: .3s;
}

.boton:hover {
    background: #8C8177;
}

.volver {
    display: block;
    text-align: center;
    margin-top: 22px;
    color: #756B62;
    font-size: 11px;
    text-decoration: none;
}

.volver:hover {
    color: #292725;
}

.mensaje {
    padding: 13px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 12px;
    background: #E5EBDD;
    color: #40513A;
}

.error {
    padding: 13px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 12px;
    background: #F2E3DF;
    color: #81483E;
}

@media(max-width: 600px) {
    .editar-container {
        margin: 35px auto;
        padding: 15px;
    }

    .editar-card {
        padding: 25px 20px;
    }
}
</style>
</head>

<body>

<main class="editar-container">

    <section class="editar-titulo">
        <span>Tu espacio personal</span>
        <h1>Editar mi cuenta</h1>
        <p>Actualizá tus datos personales de acceso.</p>
    </section>

    <section class="editar-card">

        <?php if ($mensaje): ?>
            <div class="mensaje"><?= escapar($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= escapar($error) ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="campo">
                <label>Nombre de usuario</label>
                <input
                    type="text"
                    name="usuario"
                    maxlength="50"
                    value="<?= escapar($nombre_actual) ?>"
                    required
                >
            </div>

            <hr class="separador">

            <p class="nota">
                Para guardar cualquier cambio, ingresá tu contraseña actual.
                Si no querés cambiar tu contraseña, dejá vacíos los dos campos siguientes.
            </p>

            <div class="campo">
                <label>Contraseña actual</label>
                <input
                    type="password"
                    name="contraseña_actual"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="campo">
                <label>Nueva contraseña</label>
                <input
                    type="password"
                    name="nueva_contraseña"
                    autocomplete="new-password"
                >
            </div>

            <div class="campo">
                <label>Confirmar nueva contraseña</label>
                <input
                    type="password"
                    name="confirmar_contraseña"
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="boton">
                Guardar cambios
            </button>

        </form>

        <a href="mi_cuenta.php" class="volver">
            Volver a mi cuenta
        </a>

    </section>

</main>

</body>
</html>
