<?php
// configuracion.php - Ajustes del usuario en sesión
// Problema 2 FIX: eliminado "Gestionar usuarios" completamente
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];
$guardado = false; $errores = [];

$config = $pdo->prepare("SELECT * FROM configuracion WHERE usuario_id=? LIMIT 1");
$config->execute([$uid]);
$config = $config->fetch();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre_negocio       = trim($_POST['nombre_negocio']       ?? '');
    $moneda               = trim($_POST['moneda']               ?? 'USD');
    $stock_minimo_defecto = (int)($_POST['stock_minimo_defecto'] ?? 5);

    if (!$nombre_negocio)          $errores[]='El nombre del negocio es obligatorio.';
    if ($stock_minimo_defecto < 0) $errores[]='El stock mínimo no puede ser negativo.';

    if (!$errores) {
        $pdo->prepare("UPDATE configuracion SET nombre_negocio=?,moneda=?,stock_minimo_defecto=? WHERE usuario_id=?")
            ->execute([$nombre_negocio,$moneda,$stock_minimo_defecto,$uid]);
        $guardado = true;
        $config = $pdo->prepare("SELECT * FROM configuracion WHERE usuario_id=? LIMIT 1");
        $config->execute([$uid]);
        $config = $config->fetch();
    }
}

$monedas = ['USD'=>'Dólares (USD)','EUR'=>'Euros (EUR)','MXN'=>'Pesos MX','COP'=>'Pesos CO',
            'ARS'=>'Pesos AR','CLP'=>'Pesos CL','PEN'=>'Soles PE','GTQ'=>'Quetzales','SVC'=>'Colones'];
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración – KoichiMaker</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><div><div class="page-title">Configuración</div><div class="page-subtitle">Ajustes de tu cuenta</div></div></div>
        <?php if ($guardado): ?><div class="alert alert-success">✅ Configuración guardada.</div><?php endif; ?>
        <?php if ($errores): ?><div class="alert alert-danger"><?php foreach($errores as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?></div><?php endif; ?>

        <div class="card" style="max-width:520px">
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Nombre del negocio</label>
                    <input class="form-control" type="text" name="nombre_negocio"
                           value="<?= htmlspecialchars($config['nombre_negocio'] ?? '') ?>" required autofocus>
                    <span class="form-hint">Se muestra en la barra lateral</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Moneda</label>
                    <select class="form-control" name="moneda">
                        <?php foreach ($monedas as $code=>$label): ?>
                            <option value="<?= $code ?>" <?= ($config['moneda']??'USD')===$code?'selected':'' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock mínimo por defecto</label>
                    <input class="form-control" type="number" name="stock_minimo_defecto" min="0"
                           value="<?= (int)($config['stock_minimo_defecto'] ?? 5) ?>">
                    <span class="form-hint">Valor sugerido al agregar productos</span>
                </div>
                <div style="border-top:1px solid var(--border);padding-top:18px;margin-top:6px">
                    <div style="font-size:.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px">Seguridad</div>
                    <a href="cambiar_password.php" class="btn btn-outline btn-sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Cambiar contraseña
                    </a>
                </div>
                <div style="margin-top:20px">
                    <button class="btn btn-primary" type="submit">✅ Guardar cambios</button>
                </div>
            </form>
        </div>
    </main>
</div>
</body></html>
