<?php
require_once __DIR__ . "/../controladores/proveedorController.php";

$controller = new ProveedorController();
$proveedores = $controller->listar();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gestión de Proveedores</h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <a href="?accion=proveedor_nuevo" class="btn btn-success mb-3">+ Nuevo Proveedor</a>

        <?php if (empty($proveedores)): ?>
            <div class="alert alert-info">No hay proveedores cargados.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $prov): ?>
                            <tr>
                                <td><?= $prov->getId() ?></td>
                                <td><?= htmlspecialchars($prov->getNombreEmpresa()) ?></td>
                                <td><?= htmlspecialchars($prov->getContacto()) ?></td>
                                <td><?= htmlspecialchars($prov->getTelefono()) ?></td>
                                <td><?= htmlspecialchars($prov->getEmail()) ?></td>
                                <td><?= htmlspecialchars($prov->getDireccion()) ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $prov->contarProductos() ?></span>
                                </td>
                                <td>
                                    <a href="?accion=productos&proveedor=<?= $prov->getId() ?>" class="btn btn-sm btn-primary">Ver Productos</a>
                                    <a href="?accion=proveedor_editar&id=<?= $prov->getId() ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="?accion=proveedor_eliminar&id=<?= $prov->getId() ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar proveedor? Si tiene productos no podrá eliminarse.')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>