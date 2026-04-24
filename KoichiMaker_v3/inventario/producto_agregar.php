<?php
// producto_agregar.php - Agrega producto vinculado al usuario en sesión
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];
$errores = []; $datos = ['nombre'=>'','precio'=>'','stock'=>'','stock_minimo'=>''];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $datos = ['nombre'=>trim($_POST['nombre']??''), 'precio'=>trim($_POST['precio']??''),
              'stock'=>trim($_POST['stock']??''), 'stock_minimo'=>trim($_POST['stock_minimo']??'')];
    if (!$datos['nombre'])                              $errores[]='El nombre es obligatorio.';
    if (!is_numeric($datos['precio'])||$datos['precio']<0) $errores[]='El precio debe ser un número positivo.';
    if (!ctype_digit($datos['stock']))                  $errores[]='El stock debe ser entero.';
    if (!ctype_digit($datos['stock_minimo']))            $errores[]='El stock mínimo debe ser entero.';
    if (!$errores) {
        $chk = $pdo->prepare("SELECT id FROM productos WHERE usuario_id=? AND nombre=? LIMIT 1");
        $chk->execute([$uid, $datos['nombre']]);
        if ($chk->fetch()) $errores[]='Ya tienes un producto con ese nombre.';
    }
    if (!$errores) {
        $pdo->prepare("INSERT INTO productos (usuario_id,nombre,precio,stock,stock_minimo) VALUES (?,?,?,?,?)")
            ->execute([$uid,$datos['nombre'],$datos['precio'],$datos['stock'],$datos['stock_minimo']]);
        header('Location: productos.php?msg=guardado'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agregar producto – KoichiMaker</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <a href="productos.php" class="back-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Volver</a>
        <div class="page-header"><div class="page-title">Agregar producto</div></div>
        <?php if ($errores): ?><div class="alert alert-danger"><?php foreach($errores as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?></div><?php endif; ?>
        <div class="card" style="max-width:520px">
            <form method="POST" autocomplete="off">
                <div class="form-group"><label class="form-label">Nombre del producto</label>
                    <input class="form-control" type="text" name="nombre" placeholder="Ej. Café Coscafe" value="<?= htmlspecialchars($datos['nombre']) ?>" required autofocus></div>
                <div class="form-group"><label class="form-label">Precio ($)</label>
                    <input class="form-control" type="number" name="precio" step="0.01" min="0" placeholder="Ej. 7.00" value="<?= htmlspecialchars($datos['precio']) ?>" required></div>
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">Stock inicial</label>
                        <input class="form-control" type="number" name="stock" min="0" placeholder="Ej. 60" value="<?= htmlspecialchars($datos['stock']) ?>" required>
                        <span class="form-hint">Unidades disponibles</span></div>
                    <div class="form-group"><label class="form-label">Stock mínimo (alerta)</label>
                        <input class="form-control" type="number" name="stock_minimo" min="0" placeholder="Ej. 5" value="<?= htmlspecialchars($datos['stock_minimo']) ?>" required>
                        <span class="form-hint">Alerta si baja de aquí</span></div>
                </div>
                <div style="display:flex;gap:10px">
                    <button class="btn btn-primary" type="submit">✅ Guardar producto</button>
                    <a href="productos.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body></html>
