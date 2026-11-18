
<?php
session_start();
require_once "conexion.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conexion->prepare(
        "SELECT id, contraseña FROM administradores WHERE usuario = ?"
    );
    $stmt->bind_param("s", $usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($admin = $resultado->fetch_assoc()) {
        if (password_verify($password, $admin["contraseña"])) {
            session_regenerate_id(true);
            $_SESSION["admin_id"] = (int)$admin["id"];
            $_SESSION["es_admin"] = true;

            header("Location: gestionar_pedidos.php");
            exit;
        }
    }

    $error = "Los datos ingresados no son correctos.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administración | VELUNE</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    background: #EDE5DA;
    font-family: 'Inter', sans-serif;
    color: #292725;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 25px;
}

.login-container {
    width: 100%;
    max-width: 440px;
    background: #F7F2EC;
    padding: 55px 45px;
    border: 1px solid rgba(41,39,37,0.08);
    box-shadow: 0 15px 45px rgba(41,39,37,0.07);
    text-align: center;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 39px;
    letter-spacing: 8px;
    font-weight: 400;
    margin-bottom: 8px;
}

.line {
    width: 45px;
    height: 1px;
    background: #A99B8A;
    margin: 22px auto;
}

.subtitle {
    font-size: 10px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #8C8177;
    margin-bottom: 12px;
}

h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 400;
    margin-bottom: 35px;
}

.form-group {
    text-align: left;
    margin-bottom: 23px;
}

label {
    display: block;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #756B62;
    margin-bottom: 10px;
}

input {
    width: 100%;
    padding: 14px 12px;
    background: transparent;
    border: none;
    border-bottom: 1px solid #CFC5B9;
    outline: none;
    color: #292725;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    transition: border-color .3s;
}

input:focus {
    border-color: #292725;
}

button {
    width: 100%;
    padding: 16px;
    margin-top: 12px;
    background: #292725;
    color: #F7F2EC;
    border: 1px solid #292725;
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    letter-spacing: 3px;
    text-transform: uppercase;
    cursor: pointer;
    transition: .3s;
}

button:hover {
    background: transparent;
    color: #292725;
}

.error {
    background: #E9DDD5;
    color: #754D43;
    padding: 12px;
    font-size: 12px;
    margin-bottom: 22px;
}

.back {
    display: inline-block;
    margin-top: 28px;
    color: #8C8177;
    font-size: 11px;
    text-decoration: none;
    letter-spacing: 1px;
}

.back:hover {
    color: #292725;
}

@media (max-width: 480px) {
    .login-container {
        padding: 40px 25px;
    }
}
</style>
</head>

<body>

<div class="login-container">

    <div class="logo">VELUNE</div>

    <div class="line"></div>

    <p class="subtitle">Área privada</p>

    <h2>Bienvenida</h2>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>Usuario</label>
            <input type="text" name="usuario" placeholder="Tu usuario" required>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Tu contraseña" required>
        </div>

        <button type="submit">Ingresar</button>

    </form>

    <a href="index.php" class="back">Volver a la tienda</a>

</div>

</body>
</html>
