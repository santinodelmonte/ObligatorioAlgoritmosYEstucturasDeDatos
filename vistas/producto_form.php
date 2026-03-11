<?php
require_once __DIR__ . "/../controladores/categoriaController.php";

$catController = new CategoriasController();
$producto = $producto ?? null;
$titulo = $producto ? "Editar Producto" : "Crear Nuevo Producto";
$accion = $producto ? "producto_guardar" : "producto_guardar";
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= $titulo ?></h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <form method="POST" action="?accion=<?= $accion ?>">
            <input type="hidden" name="id" value="<?= $producto ? $producto->getId() : 0 ?>">

            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" value="<?= $producto ? htmlspecialchars($producto->getNombre()) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= $producto ? htmlspecialchars($producto->getDescripcion()) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Precio:</label>
                <input type="number" name="precio" class="form-control" step="0.01" value="<?= $producto ? $producto->getPrecio() : '' ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría:</label>
                <select name="categoria" class="form-control" required>
                    <option value="">-- Seleccionar categoría --</option>
                    <?= $catController->generarOptions($producto ? $producto->getCategoria()->getId() : null) ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Proveedor:</label>
                <select name="proveedor" class="form-control" required>
                    <option value="">-- Seleccionar proveedor --</option>
                    <?php foreach ($_SESSION['proveedores'] ?? [] as $prov): ?>
                        <option value="<?= $prov->getId() ?>" <?= $producto && $producto->getProveedor()->getId() == $prov->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prov->getNombreEmpresa()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($producto): ?>
                <div class="alert alert-info">
                    <strong>Información:</strong><br>
                    Fecha de Registro: <?= $producto->getFechaRegistro() ?><br>
                    Estado: <?= $producto->isActivo() ? 'Activo' : 'Inactivo' ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary"><?= $producto ? 'Actualizar' : 'Crear' ?></button>
            <a href="?accion=productos" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>