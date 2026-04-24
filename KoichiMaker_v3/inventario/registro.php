<?php
// registro.php - Crear cuenta nueva
// Al registrarse crea su propia fila de configuración.
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['usuario_id'])) { header('Location: dashboard.php'); exit; }

require_once 'config/db.php';
$error = ''; $usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirma = trim($_POST['confirma'] ?? '');

    if (!$usuario || !$password || !$confirma)   $error = 'Todos los campos son obligatorios.';
    elseif (strlen($usuario) < 3)                $error = 'El usuario debe tener al menos 3 caracteres.';
    elseif (!preg_match('/^\w+$/', $usuario))     $error = 'Solo letras, números y guión bajo.';
    elseif (strlen($password) < 6)               $error = 'La contraseña debe tener al menos 6 caracteres.';
    elseif ($password !== $confirma)             $error = 'Las contraseñas no coinciden.';
    else {
        $chk = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? LIMIT 1");
        $chk->execute([$usuario]);
        if ($chk->fetch()) {
            $error = 'Ese nombre de usuario ya está en uso.';
        } else {
            $pdo->beginTransaction();
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)")->execute([$usuario, $hash]);
                $uid = $pdo->lastInsertId();
                // Crear configuración vacía para este usuario
                $pdo->prepare("INSERT INTO configuracion (usuario_id, nombre_negocio) VALUES (?, ?)")->execute([$uid, $usuario]);
                $pdo->commit();
                $_SESSION['usuario_id'] = $uid;
                $_SESSION['usuario']    = $usuario;
                header('Location: dashboard.php');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Error al crear la cuenta. Intenta de nuevo.';
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
    <title>Crear cuenta – KoichiMaker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background:var(--sidebar-bg); display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .login-card { background:var(--card-bg); border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.3); padding:42px 44px; width:100%; max-width:400px; text-align:center; }
        .login-logo { width:60px;height:60px;background:var(--primary);border-radius:14px;display:grid;place-items:center;margin:0 auto 18px;color:#fff; }
        .login-title { font-size:1.3rem;font-weight:700;margin-bottom:4px; }
        .login-sub   { font-size:.85rem;color:var(--text-muted);margin-bottom:28px; }
        .login-card .form-control { text-align:left; }
        .login-card .btn { width:100%;justify-content:center;padding:11px;font-size:.9rem; }
        .login-footer { margin-top:18px;font-size:.82rem;color:var(--text-muted); }
        .login-footer a { color:var(--primary);text-decoration:none;font-weight:500; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
    </div>
    <div class="login-title">Crear cuenta</div>
    <div class="login-sub">Regístrate para usar KoichiMaker</div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="form-group" style="text-align:left">
            <label class="form-label">Nombre de usuario</label>
            <input class="form-control" type="text" name="usuario" placeholder="Ej. mi_tienda"
                   value="<?= htmlspecialchars($usuario) ?>" required autofocus>
            <span class="form-hint">Letras, números y guión bajo. Mín. 3 caracteres.</span>
        </div>
        <div class="form-group" style="text-align:left">
            <label class="form-label">Contraseña</label>
            <input class="form-control" type="password" name="password" placeholder="Mínimo 6 caracteres" required>
        </div>
        <div class="form-group" style="text-align:left">
            <label class="form-label">Confirmar contraseña</label>
            <input class="form-control" type="password" name="confirma" placeholder="Repite la contraseña" required>
        </div>
        <button class="btn btn-primary" type="submit">Crear mi cuenta</button>
    </form>
    <div class="login-footer">¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a></div>
</div>
</body>
</html>
