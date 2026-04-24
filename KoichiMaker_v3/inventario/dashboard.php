<?php
// dashboard.php - Panel principal
// Muestra solo los datos del usuario en sesión.
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];
$hoy = date('Y-m-d');

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) AS total, COUNT(*) AS cant FROM ventas WHERE usuario_id=? AND DATE(fecha)=?");
$stmt->execute([$uid, $hoy]);
$ventas_hoy = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(stock),0) AS total FROM productos WHERE usuario_id=?");
$stmt->execute([$uid]);
$stock_total = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE usuario_id=? AND stock <= stock_minimo");
$stmt->execute([$uid]);
$por_agotarse = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT nombre, stock FROM productos WHERE usuario_id=? AND stock <= stock_minimo ORDER BY stock ASC LIMIT 3");
$stmt->execute([$uid]);
$alertas = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT DATE(fecha) AS dia, COALESCE(SUM(total),0) AS total
    FROM ventas WHERE usuario_id=? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(fecha) ORDER BY dia ASC");
$stmt->execute([$uid]);
$ventas_semana_raw = $stmt->fetchAll();

$dias_labels = []; $dias_valores = []; $mapa = [];
foreach ($ventas_semana_raw as $row) $mapa[$row['dia']] = $row['total'];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $dias_labels[]  = date('d M', strtotime($fecha));
    $dias_valores[] = $mapa[$fecha] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – KoichiMaker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <div class="page-title">Dashboard</div>
                <div class="page-subtitle"><?= date('d \d\e F \d\e Y') ?></div>
            </div>
            <span style="color:var(--text-muted);font-size:.85rem">👋 Hola, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
        </div>
        <div class="stats-grid">
            <div class="stat-card green">
                <div class="stat-label">Ventas hoy</div>
                <div class="stat-value">$<?= number_format($ventas_hoy['total'], 2) ?></div>
                <div class="stat-sub"><?= $ventas_hoy['cant'] ?> transacciones</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">Productos en stock</div>
                <div class="stat-value"><?= $stock_total ?></div>
                <div class="stat-sub">Unidades totales</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">Por agotarse</div>
                <div class="stat-value"><?= $por_agotarse ?></div>
                <div class="stat-sub"><a href="alertas.php" style="color:var(--warning);text-decoration:none;font-weight:500">Ver alertas →</a></div>
            </div>
        </div>
        <div class="grid-2">
            <div class="card">
                <div class="card-title">⚠️ Alertas de stock</div>
                <?php if (empty($alertas)): ?>
                    <p style="color:var(--text-muted);font-size:.875rem">Sin alertas activas 🎉</p>
                <?php else: ?>
                    <?php foreach ($alertas as $a): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
                            <div>
                                <div style="font-weight:500"><?= htmlspecialchars($a['nombre']) ?></div>
                                <div style="font-size:.78rem;color:var(--text-muted)">Quedan <?= $a['stock'] ?> unidades</div>
                            </div>
                            <span class="badge badge-low">Bajo</span>
                        </div>
                    <?php endforeach; ?>
                    <a href="alertas.php" style="display:inline-block;margin-top:12px;font-size:.82rem;color:var(--primary);text-decoration:none;font-weight:500">Ver todas →</a>
                <?php endif; ?>
            </div>
            <div class="card">
                <div class="card-title">📈 Ventas últimos 7 días</div>
                <canvas id="chartVentas" height="160"></canvas>
            </div>
        </div>
    </main>
</div>
<script>
new Chart(document.getElementById('chartVentas').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($dias_labels) ?>,
        datasets: [{ label: 'Ventas ($)', data: <?= json_encode(array_map('floatval', $dias_valores)) ?>,
            borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.08)',
            borderWidth: 2.5, pointBackgroundColor: '#2563eb', pointRadius: 4, fill: true, tension: 0.35 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#f1f5f9' },
            ticks: { callback: v => '$' + v.toLocaleString() } } } }
});
</script>
</body>
</html>
