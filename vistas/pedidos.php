<?php
require_once __DIR__ . "/../controladores/pedidosController.php";

$controller = new PedidosController();
$pedidos = $controller->listar();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Pedidos</h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <a href="?accion=pedido_nuevo" class="btn btn-success mb-3">+ Nuevo Pedido</a>

        <?php if (empty($pedidos)): ?>
            <div class="alert alert-info">No hay pedidos cargados.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td><?= $p->getId() ?></td>
                                <td><?= $p->getFechaPedido() ?></td>
                                <td><?= htmlspecialchars($p->getProveedor()->getNombreEmpresa()) ?></td>
                                <td><span class="badge <?= $p->getEstado() === 'recibido' ? 'bg-success' : ($p->getEstado() === 'cancelado' ? 'bg-secondary' : 'bg-warning') ?>"><?= htmlspecialchars(ucfirst($p->getEstado())) ?></span></td>
                                <td>$<?= number_format($p->getTotal(), 2) ?></td>
                                <td><?= count($p->getDetalles()) ?></td>
                                <td>
                                    <a href="?accion=pedido_ver&id=<?= $p->getId() ?>" class="btn btn-sm btn-primary">Ver</a>
                                    <?php if ($p->getEstado() !== 'recibido'): ?>
                                        <form method="POST" action="?accion=pedido_cambiar_estado" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $p->getId() ?>">
                                            <input type="hidden" name="estado" value="recibido">
                                            <button class="btn btn-sm btn-success" onclick="return confirm('Marcar pedido como recibido?')">Marcar recibido</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>