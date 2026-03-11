<?php
// Vista dedicada: Lista de productos con stock bajo
require_once __DIR__ . "/../controladores/stockController.php";
require_once __DIR__ . "/../controladores/categoriaController.php";

$stockController = new StockController();
$catController = new CategoriasController();

$stocks = array_filter($stockController->listar(), function($s){ return $s->verificarStockBajo(); });
$totalBajo = 0;
foreach ($stocks as $s) { $totalBajo += $s->calcularValorTotal(); }
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Productos con Stock Bajo</h5>
        <a href="?accion=stocks" class="btn btn-outline-secondary btn-sm">← Volver a Inventario</a>
    </div>
    <div class="card-body">
        <p class="mb-3">Se muestran los registros cuyo <strong>stock ≤ stock mínimo</strong>.</p>

        <?php if (empty($stocks)): ?>
            <div class="alert alert-info">No hay productos con stock bajo.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>ID</th><th>Producto</th><th>Categoría</th><th>Cantidad</th><th>Stock mínimo</th><th>Valor</th></tr></thead>
                    <tbody>
                        <?php foreach ($stocks as $s): ?>
                            <tr>
                                <td><?= $s->getId() ?></td>
                                <td><?= htmlspecialchars($s->getProducto()->getNombre()) ?></td>
                                <td><?= htmlspecialchars($s->getProducto()->getCategoria()->getRutaCompleta()) ?></td>
                                <td><span class="badge bg-danger"><?= $s->getCantidad() ?></span></td>
                                <td><?= $s->getStockMinimo() ?></td>
                                <td>$<?= number_format($s->calcularValorTotal(),2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <strong>Valor total del inventario en stock bajo:</strong> $<?= number_format($totalBajo,2) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
