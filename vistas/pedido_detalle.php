<?php
$pedido = $pedido ?? null;
if (!$pedido) {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $ids = [];
    foreach ($_SESSION['pedidos'] ?? [] as $p) {
        if (is_object($p) && method_exists($p, 'getId')) $ids[] = $p->getId();
    }
    echo '<div class="alert alert-warning">Pedido no encontrado.</div>';
    echo '<div class="alert alert-secondary"><strong>Pedidos en sesión:</strong> ' . (empty($ids) ? '(ninguno)' : implode(', ', $ids)) . '</div>';
    echo '<a href="?accion=pedidos" class="btn btn-secondary">Volver a Pedidos</a>';
    return;
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pedido #<?= $pedido->getId() ?></h5>
        <a href="?accion=pedidos" class="btn btn-outline-secondary btn-sm">← Volver</a>
    </div>
    <div class="card-body">
        <p><strong>Proveedor:</strong> <?= htmlspecialchars($pedido->getProveedor()->getNombreEmpresa()) ?></p>
        <p><strong>Fecha:</strong> <?= $pedido->getFechaPedido() ?></p>
        <p><strong>Estado:</strong> <?= htmlspecialchars(ucfirst($pedido->getEstado())) ?></p>

        <h6>Detalles</h6>
        <table class="table table-sm">
            <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unit.</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach ($pedido->getDetalles() as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d->getProducto()->getNombre()) ?></td>
                        <td><?= $d->getCantidad() ?></td>
                        <td>$<?= number_format($d->getPrecioUnitario(),2) ?></td>
                        <td>$<?= number_format($d->getCantidad()*$d->getPrecioUnitario(),2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="alert alert-info">
            <strong>Total:</strong> $<?= number_format($pedido->getTotal(),2) ?>
        </div>

        <?php if ($pedido->getEstado() !== 'recibido'): ?>
            <form method="POST" action="?accion=pedido_cambiar_estado" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $pedido->getId() ?>">
                <input type="hidden" name="estado" value="recibido">
                <button class="btn btn-success" onclick="return confirm('Marcar pedido como recibido? Esto actualizará el stock.')">Marcar recibido</button>
            </form>
            <form method="POST" action="?accion=pedido_cambiar_estado" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $pedido->getId() ?>">
                <input type="hidden" name="estado" value="cancelado">
                <button class="btn btn-secondary" onclick="return confirm('Cancelar pedido?')">Cancelar</button>
            </form>
        <?php endif; ?>
    </div>
</div>