<?php
require_once __DIR__ . "/../controladores/categoriaController.php";

$cat = $categoria ?? null;
if (!$cat) {
    echo '<div class="alert alert-danger">Categoría no encontrada.</div>';
    return;
}

$controller = new CategoriasController();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Mover Categoría: <?= htmlspecialchars($cat->getNombre()) ?></h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <form method="POST" action="?accion=categoria_mover_guardar">
            <input type="hidden" name="id" value="<?= $cat->getId() ?>">

            <div class="mb-3">
                <label class="form-label">Seleccionar nuevo padre:</label>
                <select name="nuevo_padre_id" class="form-control" required>
                    <option value="">-- Sin padre (Raíz) --</option>
                    <?php
                        // Excluir la propia categoría (evita elegirla como padre) y todo su subárbol
                        echo $controller->generarOptions(null, $cat->getId());
                    ?>
                </select>
            </div>

            <div class="alert alert-warning">
                <strong>Advertencia:</strong> No puedes mover una categoría a uno de sus propios descendientes (evita ciclos).
            </div>

            <div class="alert alert-info">
                <strong>Categoría actual:</strong> <?= htmlspecialchars($cat->getRutaCompleta()) ?><br>
                <strong>Nivel:</strong> <?= $cat->getNivel() ?>
            </div>

            <button type="submit" class="btn btn-primary">Mover</button>
            <a href="?accion=categorias" class="btn btn-secondary">Volver a Categorías</a>
        </form>
    </div>
</div>