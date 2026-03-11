<?php
require_once __DIR__ . "/../controladores/productosController.php";
require_once __DIR__ . "/../controladores/categoriaController.php";
require_once __DIR__ . "/../controladores/stockController.php";

$controller = new ProductosController();
$catController = new CategoriasController();
$stockController = new StockController();

$filtroCategoria = $_GET["categoria"] ?? null;
$filtroProveedor = $_GET["proveedor"] ?? null;
// Nuevo: stock_status: 'all' | 'low' | 'ok'
$stockStatus = $_GET["stock_status"] ?? 'all';

$productos = $controller->listar();

// Aplicar filtro por categoría (recursivo)
if (!empty($filtroCategoria)) {
    $cat = $catController->buscarCategoria((int)$filtroCategoria);
    if ($cat) {
        $productos = $controller->buscarPorCategoria($cat);
    }
}

// Filtrar por proveedor
if (!empty($filtroProveedor)) {
    $productos = array_filter($productos, function($p) use ($filtroProveedor) {
        return $p->getProveedor()->getId() == (int)$filtroProveedor;
    });
}

// Filtrar por estado de stock
if ($stockStatus !== 'all') {
    $productos = array_filter($productos, function($p) use ($stockController, $stockStatus) {
        $s = $stockController->buscarPorProductoId($p->getId());
        if (!$s) {
            // Sin stock considerarlo como 'low' (opcional). Aquí tratamos como low si no existe.
            return $stockStatus === 'low';
        }
        $isLow = $s->verificarStockBajo();
        return ($stockStatus === 'low' && $isLow) || ($stockStatus === 'ok' && !$isLow);
    });
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gestión de Productos</h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <!-- Botón para crear producto -->
        <a href="?accion=producto_nuevo" class="btn btn-success mb-3">+ Nuevo Producto</a>

        <!-- Filtros -->
        <div class="card mb-3 bg-light">
            <div class="card-body">
                <h6>Filtros:</h6>
                <form method="GET" class="row g-2">
                    <input type="hidden" name="accion" value="productos">
                    
                    <div class="col-md-3">
                        <select name="categoria" class="form-select form-select-sm">
                            <option value="">-- Todas las categorías --</option>
                            <?= $catController->generarOptions((int)$filtroCategoria ?: null) ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="proveedor" class="form-select form-select-sm">
                            <option value="">-- Todos los proveedores --</option>
                            <?php foreach ($_SESSION['proveedores'] ?? [] as $prov): ?>
                                <option value="<?= $prov->getId() ?>" <?= $filtroProveedor == $prov->getId() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prov->getNombreEmpresa()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Nuevo: filtro estado de stock -->
                    <div class="col-md-2">
                        <select name="stock_status" class="form-select form-select-sm">
                            <option value="all" <?= $stockStatus === 'all' ? 'selected' : '' ?>>Todos</option>
                            <option value="low" <?= $stockStatus === 'low' ? 'selected' : '' ?>>Stock bajo ≤ mínimo</option>
                            <option value="ok" <?= $stockStatus === 'ok' ? 'selected' : '' ?>>Stock ok &gt; mínimo</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Filtrar</button>
                    </div>

                    <div class="col-md-2">
                        <a href="?accion=productos" class="btn btn-sm btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de productos -->
        <?php if (empty($productos)): ?>
            <div class="alert alert-info">No hay productos que coincidan con los filtros.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Proveedor</th>
                            <th>Stock</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <?php 
                                $stock = $stockController->buscarPorProductoId($prod->getId());
                            ?>
                            <tr>
                                <td><?= $prod->getId() ?></td>
                                <td><strong><?= htmlspecialchars($prod->getNombre()) ?></strong></td>
                                <td><?= htmlspecialchars(substr($prod->getDescripcion(), 0, 30)) ?></td>
                                <td>$<?= number_format($prod->getPrecio(), 2) ?></td>
                                <td><?= htmlspecialchars($prod->getCategoria()->getRutaCompleta()) ?></td>
                                <td><?= htmlspecialchars($prod->getProveedor()->getNombreEmpresa()) ?></td>
                                <td>
                                    <?php if ($stock): ?>
                                        <span class="badge <?= $stock->verificarStockBajo() ? 'bg-danger' : 'bg-success' ?>">
                                            <?= $stock->getCantidad() ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sin stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $prod->isActivo() ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>' ?>
                                </td>
                                <td>
                                    <a href="?accion=producto_editar&id=<?= $prod->getId() ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="?accion=producto_eliminar&id=<?= $prod->getId() ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar producto?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
