<?php
// productos.php - Lista de productos del usuario en sesión
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];

if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Solo puede eliminar sus propios productos
    $pdo->prepare("DELETE FROM productos WHERE id=? AND usuario_id=?")->execute([$id, $uid]);
    header('Location: productos.php?msg=eliminado'); exit;
}

$buscar = trim($_GET['buscar'] ?? '');
if ($buscar) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE usuario_id=? AND nombre LIKE ? ORDER BY nombre ASC");
    $stmt->execute([$uid, '%'.$buscar.'%']);
} else {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE usuario_id=? ORDER BY nombre ASC");
    $stmt->execute([$uid]);
}
$productos = $stmt->fetchAll();
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos – KoichiMaker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div><div class="page-title">Productos</div><div class="page-subtitle">Tu catálogo de productos</div></div>
            <a href="producto_agregar.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Agregar producto
            </a>
        </div>
        <?php if ($msg==='guardado'): ?><div class="alert alert-success">✅ Producto guardado.</div>
        <?php elseif ($msg==='editado'): ?><div class="alert alert-success">✅ Producto actualizado.</div>
        <?php elseif ($msg==='eliminado'): ?><div class="alert alert-danger">🗑️ Producto eliminado.</div><?php endif; ?>

        <form method="GET" style="margin-bottom:18px">
            <div class="search-box">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="form-control" style="width:280px" type="text" name="buscar" placeholder="Buscar producto..." value="<?= htmlspecialchars($buscar) ?>">
            </div>
            <?php if ($buscar): ?><a href="productos.php" class="btn btn-outline btn-sm" style="margin-left:8px">Limpiar</a><?php endif; ?>
        </form>

        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Producto</th><th>Precio</th><th>Stock actual</th><th>Mínimo</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">Sin productos. <a href="producto_agregar.php" style="color:var(--primary)">Agrega el primero →</a></td></tr>
                <?php endif; ?>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td style="color:var(--text-muted);font-family:'DM Mono',monospace;font-size:.8rem"><?= $p['id'] ?></td>
                        <td style="font-weight:500"><?= htmlspecialchars($p['nombre']) ?></td>
                        <td style="font-family:'DM Mono',monospace">$<?= number_format($p['precio'],2) ?></td>
                        <td style="font-family:'DM Mono',monospace;font-weight:600"><?= $p['stock'] ?></td>
                        <td style="color:var(--text-muted)"><?= $p['stock_minimo'] ?></td>
                        <td><?php if ($p['stock'] <= $p['stock_minimo']): ?><span class="badge badge-low">Bajo</span><?php else: ?><span class="badge badge-ok">OK</span><?php endif; ?></td>
                        <td>
                            <a href="producto_editar.php?id=<?= $p['id'] ?>" class="btn-icon" title="Editar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="productos.php?eliminar=<?= $p['id'] ?>" class="btn-icon del"
                               onclick="return confirm('¿Eliminar «<?= htmlspecialchars($p['nombre'],ENT_QUOTES) ?>»?')" title="Eliminar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="table-footer">Mostrando <?= count($productos) ?> producto<?= count($productos)!==1?'s':'' ?></div>
        </div>
    </main>
</div>
</body>
</html>
