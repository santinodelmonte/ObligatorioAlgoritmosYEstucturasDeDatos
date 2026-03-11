<?php
// Vista de Inventario / Stocks
require_once __DIR__ . "/../controladores/stockController.php";
require_once __DIR__ . "/../controladores/categoriaController.php";

$stockController = new StockController();
$catController = new CategoriasController();

$filtroCategoria = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? intval($_GET['categoria']) : null;
$stockStatus = $_GET['stock_status'] ?? 'all';

if ($filtroCategoria) {
    $categoria = $catController->buscarCategoria($filtroCategoria);
    $stocks = $stockController->filtrarPorCategoria($categoria);
} else {
    $stocks = $stockController->listar();
}

// Filtrar por estado de stock si aplica
if ($stockStatus !== 'all') {
    $stocks = array_filter($stocks, function($s) use ($stockStatus) {
        $isLow = $s->verificarStockBajo();
        return ($stockStatus === 'low' && $isLow) || ($stockStatus === 'ok' && !$isLow);
    });
}

$totalInventario = $stockController->valorTotalInventario();
// Si se filtró por categoría, calcular valor del inventario para esa rama
$branchTotal = null;
if ($filtroCategoria) {
    $branchTotal = 0;
    foreach ($stocks as $s) {
        $branchTotal += $s->calcularValorTotal();
    }
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inventario (Stocks)</h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <div class="mb-3 row g-2">
            <div class="col-md-6">
                <form method="GET" class="d-flex" style="gap:8px;">
                    <input type="hidden" name="accion" value="stocks">
                    <select name="categoria" class="form-select form-select-sm me-2">
                        <option value="">-- Todas las categorías --</option>
                        <?= $catController->generarOptions($filtroCategoria) ?>
                    </select>

                    <select name="stock_status" class="form-select form-select-sm me-2">
                        <option value="all" <?= $stockStatus === 'all' ? 'selected' : '' ?>>Todos</option>
                        <option value="low" <?= $stockStatus === 'low' ? 'selected' : '' ?>>Stock bajo ≤ mínimo</option>
                        <option value="ok" <?= $stockStatus === 'ok' ? 'selected' : '' ?>>Stock ok &gt; mínimo</option>
                    </select>

                    <button class="btn btn-sm btn-outline-primary">Filtrar</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <strong>Valor total inventario:</strong> $<?= number_format($totalInventario, 2) ?>
                <?php if ($branchTotal !== null): ?>
                    <br><small>Valor inventario para rama seleccionada: <strong>$<?= number_format($branchTotal, 2) ?></strong></small>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($stocks)): ?>
            <div class="alert alert-info">No hay registros de stock.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Stock mínimo</th>
                            <th>Última actualización</th>
                            <th>Ubicación</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $s): ?>
                            <?php
                                $producto = $s->getProducto();
                                $categoriaRuta = is_object($producto) ? $producto->getCategoria()->getRutaCompleta() : '(sin categoría)';
                                $esBajo = $s->verificarStockBajo();
                            ?>
                            <tr class="<?= $esBajo ? 'table-danger' : '' ?>">
                                <td><?= $s->getId() ?></td>
                                <td><?= htmlspecialchars($producto->getNombre()) ?></td>
                                <td><?= htmlspecialchars($categoriaRuta) ?></td>

                                <!-- Cambiado: cantidad en badge, rojo si está en/bajo el mínimo -->
                                <td>
                                    <span class="badge <?= $esBajo ? 'bg-danger' : 'bg-success' ?>">
                                        <?= $s->getCantidad() ?>
                                    </span>
                                </td>

                                <td><?= $s->getStockMinimo() ?></td>
                                <td><?= $s->getFechaUltimaActualizacion() ?></td>
                                <td><?= htmlspecialchars($s->getUbicacion()) ?></td>
                                <td>$<?= number_format($s->calcularValorTotal(), 2) ?></td>
                                <td>
                                    <!-- Formulario pequeño para agregar -->
                                    <form method="POST" action="?accion=stock_agregar" class="d-inline-flex" style="gap:4px;">
                                        <input type="hidden" name="idStock" value="<?= $s->getId() ?>">
                                        <input type="number" name="cantidad" class="form-control form-control-sm" placeholder="+ cant" style="width:80px" required>
                                        <input type="datetime-local" name="fecha" class="form-control form-control-sm" style="width:170px">
                                        <button class="btn btn-sm btn-success">+</button>
                                    </form>

                                    <!-- Formulario pequeño para descontar -->
                                    <form method="POST" action="?accion=stock_descontar" class="d-inline-flex" style="gap:4px;">
                                        <input type="hidden" name="idStock" value="<?= $s->getId() ?>">
                                        <input type="number" name="cantidad" class="form-control form-control-sm" placeholder="- cant" style="width:80px" required>
                                        <input type="datetime-local" name="fecha" class="form-control form-control-sm" style="width:170px">
                                        <button class="btn btn-sm btn-danger">-</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Crear nuevo stock para un producto que no tenga -->
        <hr>
        <h6>Crear nuevo registro de stock</h6>
        <form method="POST" action="?accion=stock_crear" class="row g-2">
            <div class="col-md-4">
                <label class="form-label">Producto</label>
                <select name="productoId" class="form-select" required>
                    <option value="">-- Seleccionar producto --</option>
                    <?php foreach ($_SESSION['productos'] ?? [] as $p): ?>
                        <option value="<?= $p->getId() ?>"><?= htmlspecialchars($p->getNombre()) ?> (<?= htmlspecialchars($p->getCategoria()->getRutaCompleta()) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" name="cantidad" class="form-control" value="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock mínimo</label>
                <input type="number" name="stockMinimo" class="form-control" value="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" value="Depósito">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary">Crear Stock</button>
            </div>
        </form>
    </div>
</div>
