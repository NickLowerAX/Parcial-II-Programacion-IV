<?php
// alertas.php - Solo muestra alertas del usuario en sesión
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT id,nombre,stock,stock_minimo FROM productos WHERE usuario_id=? AND stock<=stock_minimo ORDER BY stock ASC");
$stmt->execute([$uid]);
$alertas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alertas – KoichiMaker</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div><div class="page-title">Alertas de stock</div><div class="page-subtitle">Productos por debajo del mínimo</div></div>
        </div>
        <?php if (empty($alertas)): ?>
            <div class="card" style="text-align:center;padding:48px">
                <div style="font-size:2.5rem;margin-bottom:12px">🎉</div>
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px">¡Todo en orden!</div>
                <div style="color:var(--text-muted);font-size:.875rem">Ningún producto está por debajo del mínimo.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span><?= count($alertas) ?> producto<?= count($alertas)>1?'s':'' ?> requieren atención</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Producto</th><th>Stock actual</th><th>Mínimo</th><th>Diferencia</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
                    <?php foreach ($alertas as $a): ?>
                        <tr>
                            <td style="font-weight:500"><?= htmlspecialchars($a['nombre']) ?></td>
                            <td style="font-family:'DM Mono',monospace;font-weight:700;color:var(--danger)"><?= $a['stock'] ?></td>
                            <td style="font-family:'DM Mono',monospace;color:var(--text-muted)"><?= $a['stock_minimo'] ?></td>
                            <td style="font-family:'DM Mono',monospace;color:var(--warning)">-<?= $a['stock_minimo']-$a['stock'] ?></td>
                            <td><?php if ($a['stock']==0): ?><span class="badge badge-low">Agotado</span><?php else: ?><span class="badge badge-low">Bajo</span><?php endif; ?></td>
                            <td><a href="producto_editar.php?id=<?= $a['id'] ?>" class="btn btn-outline btn-sm">Actualizar stock</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="table-footer"><?= count($alertas) ?> producto<?= count($alertas)>1?'s':'' ?> requieren atención</div>
            </div>
        <?php endif; ?>
    </main>
</div>
</body></html>
