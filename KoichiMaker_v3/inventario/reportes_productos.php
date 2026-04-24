<?php
// reportes_productos.php - Más vendidos del usuario en sesión
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta))  $hasta = date('Y-m-d');

$stmt = $pdo->prepare("SELECT p.nombre, SUM(dv.cantidad) AS cant_vendida, SUM(dv.cantidad*dv.precio_unitario) AS total_vendido
    FROM detalle_ventas dv
    INNER JOIN productos p ON p.id=dv.producto_id
    INNER JOIN ventas v ON v.id=dv.venta_id
    WHERE v.usuario_id=? AND DATE(v.fecha) BETWEEN ? AND ?
    GROUP BY p.id,p.nombre ORDER BY cant_vendida DESC");
$stmt->execute([$uid, $desde, $hasta]);
$filas = $stmt->fetchAll();
$max_cant = !empty($filas) ? $filas[0]['cant_vendida'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Más vendidos – KoichiMaker</title><link rel="stylesheet" href="assets/css/style.css">
<style>.progress-bar{height:6px;background:var(--border);border-radius:4px;overflow:hidden;margin-top:5px;width:140px}.progress-fill{height:100%;background:var(--primary);border-radius:4px}</style>
</head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <a href="dashboard.php" class="back-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Volver</a>
        <div class="page-header"><div><div class="page-title">Productos más vendidos</div><div class="page-subtitle">Ranking por cantidad vendida</div></div></div>
        <form method="GET" class="card" style="padding:18px 22px;margin-bottom:22px">
            <div style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap">
                <div class="form-group" style="margin:0"><label class="form-label">Desde</label><input class="form-control" type="date" name="desde" value="<?= $desde ?>"></div>
                <div class="form-group" style="margin:0"><label class="form-label">Hasta</label><input class="form-control" type="date" name="hasta" value="<?= $hasta ?>"></div>
                <button class="btn btn-primary" type="submit">Filtrar</button>
            </div>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>Producto</th><th>Cantidad vendida</th><th>Total vendido</th></tr></thead>
                <tbody>
                <?php if (empty($filas)): ?><tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">Sin datos para el período.</td></tr><?php endif; ?>
                <?php foreach ($filas as $i=>$f): ?>
                    <tr>
                        <td><?= $i===0?'🥇':($i===1?'🥈':($i===2?'🥉':$i+1)) ?></td>
                        <td style="font-weight:500"><?= htmlspecialchars($f['nombre']) ?></td>
                        <td>
                            <span style="font-family:'DM Mono',monospace;font-weight:600"><?= $f['cant_vendida'] ?></span>
                            <div class="progress-bar"><div class="progress-fill" style="width:<?= round($f['cant_vendida']/$max_cant*100) ?>%"></div></div>
                        </td>
                        <td style="font-family:'DM Mono',monospace;color:var(--success);font-weight:600">$<?= number_format($f['total_vendido'],2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!empty($filas)): ?><div class="table-footer"><?= count($filas) ?> productos vendidos en el período</div><?php endif; ?>
        </div>
    </main>
</div>
</body></html>
