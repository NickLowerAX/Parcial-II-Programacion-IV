<?php
// ============================================
// cambiar_password.php - Cambiar contraseña
// Verifica la contraseña actual y actualiza
// la nueva contraseña con hash seguro (bcrypt).
// ============================================

require_once 'includes/session.php';
require_once 'config/db.php';

$errores = [];
$guardado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual   = $_POST['actual']   ?? '';
    $nueva    = $_POST['nueva']    ?? '';
    $confirma = $_POST['confirma'] ?? '';

    // Validaciones
    if (!$actual || !$nueva || !$confirma) {
        $errores[] = 'Todos los campos son obligatorios.';
    } elseif (strlen($nueva) < 6) {
        $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirma) {
        $errores[] = 'Las contraseñas nuevas no coinciden.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($actual, $user['password'])) {
            $errores[] = 'La contraseña actual es incorrecta.';
        } else {
            $hash = password_hash($nueva, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['usuario_id']]);
            $guardado = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña – KoichiMaker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <a href="configuracion.php" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver a configuración
        </a>

        <div class="page-header">
            <div class="page-title">Cambiar contraseña</div>
        </div>

        <?php if ($guardado): ?>
            <div class="alert alert-success">✅ Contraseña actualizada correctamente.</div>
        <?php endif; ?>
        <?php if ($errores): ?>
            <div class="alert alert-danger">
                <?php foreach ($errores as $e): ?>
                    <div>• <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width:420px">
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="form-label" for="actual">Contraseña actual</label>
                    <input class="form-control" type="password" id="actual" name="actual" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nueva">Nueva contraseña</label>
                    <input class="form-control" type="password" id="nueva" name="nueva"
                           minlength="6" required placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirma">Confirmar nueva contraseña</label>
                    <input class="form-control" type="password" id="confirma" name="confirma" required>
                </div>
                <button class="btn btn-primary" type="submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Actualizar contraseña
                </button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
