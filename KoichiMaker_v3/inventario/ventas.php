<?php
// ventas.php - Registrar venta
// FIX: sincroniza cantidades reales antes del submit
require_once 'includes/session.php';
require_once 'config/db.php';
$uid = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['confirmar'])) {
    $carrito = json_decode($_POST['carrito'] ?? '[]', true);
    $error_venta = '';

    if (empty($carrito)) {
        $error_venta = 'El carrito está vacío.';
    } else {
        foreach ($carrito as &$item) {
            $item['cantidad'] = (int)$item['cantidad'];  // forzar entero
            $item['id']       = (int)$item['id'];
            if ($item['cantidad'] < 1) { $error_venta = 'Cantidad inválida en el carrito.'; break; }
            // Verificar que el producto pertenece a este usuario y tiene stock
            $st = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id=? AND usuario_id=?");
            $st->execute([$item['id'], $uid]);
            $prod = $st->fetch();
            if (!$prod) { $error_venta = 'Producto no encontrado.'; break; }
            if ($prod['stock'] < $item['cantidad']) {
                $error_venta = "Stock insuficiente para «{$prod['nombre']}» (disponible: {$prod['stock']}, pedido: {$item['cantidad']}).";
                break;
            }
        }
        unset($item);
    }

    if (!$error_venta) {
        $pdo->beginTransaction();
        try {
            $total = array_sum(array_map(fn($i) => (float)$i['precio'] * (int)$i['cantidad'], $carrito));
            $pdo->prepare("INSERT INTO ventas (usuario_id, total) VALUES (?,?)")->execute([$uid, $total]);
            $venta_id = $pdo->lastInsertId();

            foreach ($carrito as $item) {
                $cantidad = (int)$item['cantidad'];
                $pdo->prepare("INSERT INTO detalle_ventas (venta_id,producto_id,cantidad,precio_unitario) VALUES (?,?,?,?)")
                    ->execute([$venta_id, $item['id'], $cantidad, (float)$item['precio']]);
                // Descuenta exactamente la cantidad vendida
                $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id=? AND usuario_id=?")
                    ->execute([$cantidad, $item['id'], $uid]);
            }
            $pdo->commit();
            header('Location: ventas.php?ok=1'); exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_venta = 'Error al procesar la venta.';
        }
    }
}

$buscar_prod = trim($_GET['buscar'] ?? '');
if ($buscar_prod) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE usuario_id=? AND nombre LIKE ? AND stock>0 ORDER BY nombre ASC");
    $stmt->execute([$uid, '%'.$buscar_prod.'%']);
} else {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE usuario_id=? AND stock>0 ORDER BY nombre ASC");
    $stmt->execute([$uid]);
}
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva venta – KoichiMaker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .ventas-grid { display:grid; grid-template-columns:1fr 400px; gap:20px; }
        .carrito-item { display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border); }
        .carrito-item:last-child { border-bottom:none; }
        .total-row { display:flex;justify-content:space-between;align-items:center;padding:14px 0 4px;font-size:1.1rem;font-weight:700;border-top:2px solid var(--border);margin-top:8px; }
        .qty-input { width:65px;text-align:center;padding:6px 8px; }
        @media(max-width:900px){.ventas-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <a href="dashboard.php" class="back-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg> Volver al dashboard</a>
        <div class="page-header"><div class="page-title">Nueva venta</div></div>

        <?php if (isset($_GET['ok'])): ?><div class="alert alert-success">✅ Venta registrada. Stock actualizado.</div><?php endif; ?>
        <?php if (!empty($error_venta)): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error_venta) ?></div><?php endif; ?>

        <div class="ventas-grid">
            <div>
                <form method="GET" style="margin-bottom:16px">
                    <div class="search-box">
                        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="form-control" style="width:280px" type="text" name="buscar" placeholder="Buscar producto..." value="<?= htmlspecialchars($buscar_prod) ?>">
                    </div>
                </form>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Producto</th><th>Precio</th><th>Stock</th><th>Acción</th></tr></thead>
                        <tbody>
                        <?php if (empty($productos)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted)">Sin productos con stock disponible.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td style="font-weight:500"><?= htmlspecialchars($p['nombre']) ?></td>
                                <td style="font-family:'DM Mono',monospace">$<?= number_format($p['precio'],2) ?></td>
                                <td><?= $p['stock'] ?></td>
                                <td><button class="btn btn-primary btn-sm"
                                        onclick="agregarAlCarrito(<?= $p['id'] ?>,'<?= addslashes(htmlspecialchars($p['nombre'])) ?>',<?= $p['precio'] ?>,<?= $p['stock'] ?>)">+ Agregar</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Carrito -->
            <div class="card" style="height:fit-content;position:sticky;top:20px">
                <div class="card-title">🛒 Productos en venta</div>
                <div id="carrito-items">
                    <p id="carrito-vacio" style="color:var(--text-muted);font-size:.875rem;text-align:center;padding:20px 0">Agrega productos desde la lista</p>
                </div>
                <div class="total-row" id="total-row" style="display:none">
                    <span>TOTAL:</span>
                    <span id="total-valor" style="color:var(--primary)">$0.00</span>
                </div>
                <form method="POST" id="form-venta" style="margin-top:14px">
                    <input type="hidden" name="confirmar" value="1">
                    <input type="hidden" name="carrito" id="input-carrito" value="[]">
                    <button class="btn btn-success" type="submit" style="width:100%;justify-content:center" id="btn-confirmar" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Confirmar venta
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
let carrito = [];

function agregarAlCarrito(id, nombre, precio, stockMax) {
    const idx = carrito.findIndex(i => i.id === id);
    if (idx >= 0) {
        if (carrito[idx].cantidad < stockMax) carrito[idx].cantidad++;
        else alert('Stock máximo: ' + stockMax);
    } else {
        carrito.push({id, nombre, precio, cantidad: 1, stockMax});
    }
    renderCarrito();
}

// Actualiza la cantidad en el array sin re-renderizar (no pierde el foco)
function setCantidad(id, val) {
    const idx = carrito.findIndex(i => i.id === id);
    if (idx < 0) return;
    let n = parseInt(val, 10);
    if (isNaN(n) || n < 1) n = 1;
    if (n > carrito[idx].stockMax) n = carrito[idx].stockMax;
    carrito[idx].cantidad = n;
    // Solo actualiza subtotal y JSON, sin re-render completo
    const subEl = document.getElementById('sub-' + id);
    if (subEl) subEl.textContent = '$' + (carrito[idx].precio * n).toFixed(2);
    actualizarTotalYJSON();
}

function quitarDelCarrito(id) {
    carrito = carrito.filter(i => i.id !== id);
    renderCarrito();
}

function actualizarTotalYJSON() {
    const total = carrito.reduce((s, i) => s + i.precio * i.cantidad, 0);
    document.getElementById('total-valor').textContent = '$' + total.toFixed(2);
    document.getElementById('input-carrito').value = JSON.stringify(carrito);
}

function renderCarrito() {
    const cont = document.getElementById('carrito-items');
    const totalRow = document.getElementById('total-row');
    const btnConf  = document.getElementById('btn-confirmar');

    if (carrito.length === 0) {
        cont.innerHTML = '<p style="color:var(--text-muted);font-size:.875rem;text-align:center;padding:20px 0">Agrega productos desde la lista</p>';
        totalRow.style.display = 'none';
        btnConf.disabled = true;
        document.getElementById('input-carrito').value = '[]';
        return;
    }

    let html = '';
    carrito.forEach(item => {
        html += `
        <div class="carrito-item">
            <div style="flex:1;min-width:0">
                <div style="font-weight:500;font-size:.875rem">${item.nombre}</div>
                <div style="font-size:.78rem;color:var(--text-muted)">$${item.precio.toFixed(2)} c/u · máx ${item.stockMax}</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-left:10px">
                <input class="form-control qty-input" id="qty-${item.id}" type="number"
                       min="1" max="${item.stockMax}" value="${item.cantidad}"
                       oninput="setCantidad(${item.id}, this.value)">
                <span id="sub-${item.id}" style="font-family:'DM Mono',monospace;min-width:56px;text-align:right;font-size:.85rem">
                    $${(item.precio * item.cantidad).toFixed(2)}
                </span>
                <button onclick="quitarDelCarrito(${item.id})" class="btn-icon del" type="button">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                </button>
            </div>
        </div>`;
    });

    cont.innerHTML = html;
    totalRow.style.display = 'flex';
    btnConf.disabled = false;
    actualizarTotalYJSON();
}

// ✅ FIX CLAVE: sincronizar cantidades actuales de los inputs justo antes de enviar
document.getElementById('form-venta').addEventListener('submit', function(e) {
    // Leer cada input del DOM y actualizar el array
    carrito.forEach(item => {
        const inp = document.getElementById('qty-' + item.id);
        if (inp) {
            let n = parseInt(inp.value, 10);
            if (isNaN(n) || n < 1) n = 1;
            if (n > item.stockMax) n = item.stockMax;
            item.cantidad = n;
        }
    });
    document.getElementById('input-carrito').value = JSON.stringify(carrito);
});
</script>
</body>
</html>
