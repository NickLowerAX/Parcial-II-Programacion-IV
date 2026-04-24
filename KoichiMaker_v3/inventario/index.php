<?php
// ============================================
// index.php - Inicio de sesión
// Verifica usuario y contraseña contra la BD.
// Si es correcto, inicia sesión y redirige al dashboard.
// ============================================
require_once "config/db.php";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
$row = $stmt->fetch();

if ($row['total'] == 0) {
    header("Location: setup.php");
    exit;
}

session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usuario && $password) {
        $stmt = $pdo->prepare("SELECT id, usuario, password FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario']    = $user['usuario'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión – KoichiMaker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: var(--sidebar-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            padding: 42px 44px;
            width: 100%;
            max-width: 380px;
            text-align: center;
        }
        .login-logo {
            width: 60px; height: 60px;
            background: var(--primary);
            border-radius: 14px;
            display: grid;
            place-items: center;
            margin: 0 auto 18px;
            color: #fff;
        }
        .login-title  { font-size: 1.3rem; font-weight: 700; margin-bottom: 4px; }
        .login-sub    { font-size: .85rem; color: var(--text-muted); margin-bottom: 28px; }
        .login-card .form-control { text-align: left; }
        .login-card .btn { width: 100%; justify-content: center; padding: 11px; font-size: .9rem; }
        .login-footer { margin-top: 22px; font-size: .75rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
        </div>
        <div class="login-title">KoichiMaker</div>
        <div class="login-sub">Ingresa tus credenciales para continuar</div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group" style="text-align:left">
                <label class="form-label" for="usuario">Usuario</label>
                <input class="form-control" type="text" id="usuario" name="usuario"
                       placeholder="Ej. admin" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group" style="text-align:left">
                <label class="form-label" for="password">Contraseña</label>
                <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button class="btn btn-primary" type="submit">Iniciar sesión</button>
        </form>
        <div class="login-footer">© <?= date('Y') ?> KoichiMaker</div>
        <a href="registro.php">Crear cuenta</a>
    </div>
</body>
</html>
