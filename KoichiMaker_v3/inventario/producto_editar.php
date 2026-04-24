<?php
// producto_editar.php - Edita solo productos del usuario en sesión
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: productos.php'); exit; }

// Verificar que el producto pertenece a este usuario
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id=? AND usuario_id=? LIMIT 1");
$stmt->execute([$id, $uid]);
$producto = $stmt->fetch();
if (!$producto) { header('Location: productos.php'); exit; }

$errores = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre=$trim_n=trim($_POST['nombre']??''); $precio=trim($_POST['precio']??'');
    $stock=trim($_POST['stock']??''); $stock_minimo=trim($_POST['stock_minimo']??'');
    if (!$nombre)                              $errores[]='El nombre es obligatorio.';
    if (!is_numeric($precio)||$precio<0)       $errores[]='El precio debe ser número positivo.';
    if (!ctype_digit($stock))                  $errores[]='El stock debe ser entero.';
    if (!ctype_digit($stock_minimo))            $errores[]='El stock mínimo debe ser entero.';
    if (!$errores) {
        $chk=$pdo->prepare("SELECT id FROM productos WHERE usuario_id=? AND nombre=? AND id!=? LIMIT 1");
        $chk->execute([$uid,$nombre,$id]);
        if ($chk->fetch()) $errores[]='Ya tienes otro producto con ese nombre.';
    }
    if (!$errores) {
        $pdo->prepare("UPDATE productos SET nombre=?,precio=?,stock=?,stock_minimo=? WHERE id=? AND usuario_id=?")
            ->execute([$nombre,$precio,$stock,$stock_minimo,$id,$uid]);
        header('Location: productos.php?msg=editado'); exit;
    }
    $producto = array_merge($producto,['nombre'=>$nombre,'precio'=>$precio,'stock'=>$stock,'stock_minimo'=>$stock_minimo]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar producto – KoichiMaker</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <a href="productos.php" class="back-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Volver</a>
        <div class="page-header"><div class="page-title">Editar producto</div><span style="color:var(--text-muted);font-size:.85rem">ID #<?= $id ?></span></div>
        <?php if ($errores): ?><div class="alert alert-danger"><?php foreach($errores as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?></div><?php endif; ?>
        <div class="card" style="max-width:520px">
            <form method="POST" autocomplete="off">
                <div class="form-group"><label class="form-label">Nombre del producto</label>
                    <input class="form-control" type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required autofocus></div>
                <div class="form-group"><label class="form-label">Precio ($)</label>
                    <input class="form-control" type="number" name="precio" step="0.01" min="0" value="<?= htmlspecialchars($producto['precio']) ?>" required></div>
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">Stock actual</label>
                        <input class="form-control" type="number" name="stock" min="0" value="<?= htmlspecialchars($producto['stock']) ?>" required></div>
                    <div class="form-group"><label class="form-label">Stock mínimo</label>
                        <input class="form-control" type="number" name="stock_minimo" min="0" value="<?= htmlspecialchars($producto['stock_minimo']) ?>" required></div>
                </div>
                <div style="display:flex;gap:10px">
                    <button class="btn btn-primary" type="submit">✅ Guardar cambios</button>
                    <a href="productos.php" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body></html>
