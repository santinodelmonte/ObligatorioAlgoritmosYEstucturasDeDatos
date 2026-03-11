<?php
require_once __DIR__ . "/../controladores/categoriaController.php";

$controller = new CategoriasController();
$padreId = $_GET["padre"] ?? null;
$tipoCreacion = ($padreId) ? "Subcategoría" : "Categoría Raíz";
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Crear Nueva <?= $tipoCreacion ?></h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <form method="POST" action="?accion=categoria_guardar">
            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>

            <?php if ($padreId): ?>
                <input type="hidden" name="padre_id" value="<?= (int)$padreId ?>">
                <div class="alert alert-info">
                    Creando subcategoría dentro de: <?= htmlspecialchars($controller->buscarCategoria((int)$padreId)->getNombre()) ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Creando categoría raíz (sin padre)</div>
            <?php endif; ?>

            <button type="submit" class="btn btn-success">Crear</button>
            <a href="?accion=categorias" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>