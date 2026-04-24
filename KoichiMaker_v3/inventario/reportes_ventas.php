<?php
// reportes_ventas.php - Ventas por día del usuario en sesión
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-7 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime('-7 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta))  $hasta = date('Y-m-d');

$stmt = $pdo->prepare("SELECT DATE(fecha) AS dia, COUNT(*) AS cantidad, SUM(total) AS total_dia
    FROM ventas WHERE usuario_id=? AND DATE(fecha) BETWEEN ? AND ?
    GROUP BY DATE(fecha) ORDER BY DATE(fecha) DESC");
$stmt->execute([$uid, $desde, $hasta]);
$filas = $stmt->fetchAll();
$total_general = array_sum(array_column($filas,'total_dia'));
$ventas_total  = array_sum(array_column($filas,'cantidad'));
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ventas por día – KoichiMaker</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <a href="dashboard.php" class="back-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Volver</a>
        <div class="page-header"><div><div class="page-title">Ventas por día</div><div class="page-subtitle">Tus ingresos por fecha</div></div></div>
        <form method="GET" class="card" style="padding:18px 22px;margin-bottom:22px">
            <div style="display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap">
                <div class="form-group" style="margin:0"><label class="form-label">Desde</label><input class="form-control" type="date" name="desde" value="<?= $desde ?>"></div>
                <div class="form-group" style="margin:0"><label class="form-label">Hasta</label><input class="form-control" type="date" name="hasta" value="<?= $hasta ?>"></div>
                <button class="btn btn-primary" type="submit">Filtrar</button>
            </div>
        </form>
        <?php if (!empty($filas)): ?>
        <div class="stats-grid" style="margin-bottom:22px">
            <div class="stat-card green"><div class="stat-label">Total período</div><div class="stat-value">$<?= number_format($total_general,2) ?></div></div>
            <div class="stat-card blue"><div class="stat-label">Ventas</div><div class="stat-value"><?= $ventas_total ?></div></div>
            <div class="stat-card orange"><div class="stat-label">Días con ventas</div><div class="stat-value"><?= count($filas) ?></div></div>
        </div>
        <?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Fecha</th><th>N° ventas</th><th>Total del día</th><th>Promedio</th></tr></thead>
                <tbody>
                <?php if (empty($filas)): ?><tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">Sin ventas en el período.</td></tr><?php endif; ?>
                <?php foreach ($filas as $f): ?>
                    <tr>
                        <td style="font-weight:500"><?= date('d/m/Y',strtotime($f['dia'])) ?></td>
                        <td><?= $f['cantidad'] ?></td>
                        <td style="font-family:'DM Mono',monospace;font-weight:600;color:var(--success)">$<?= number_format($f['total_dia'],2) ?></td>
                        <td style="font-family:'DM Mono',monospace;color:var(--text-muted)">$<?= number_format($f['total_dia']/$f['cantidad'],2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!empty($filas)): ?>
            <div class="table-footer" style="display:flex;justify-content:space-between">
                <span><?= count($filas) ?> días encontrados</span>
                <span style="font-weight:600">Total: $<?= number_format($total_general,2) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body></html>
