<h1>Editar Categoría</h1>

<a href="index.php?vista=categorias">Volver</a>
<br><br>

<?php
$controller = new CategoriasController();

if (!isset($_GET["id"])) {
    echo "Categoría no encontrada.";
    return;
}

$id = intval($_GET["id"]);
$categoria = $controller->buscarCategoria($id);

if (!$categoria) {
    echo "Categoría no encontrada.";
    return;
}

if ($_POST) {
    $nuevoNombre = $_POST["nombre"];
    $nuevaDescripcion = $_POST["descripcion"];

    $controller->editarCategoria($id, $nuevoNombre, $nuevaDescripcion);
    header("Location: index.php?vista=categorias");
    exit;
}
?>

<?php
$cat = $categoria ?? null;
if (!$cat) {
    echo '<div class="alert alert-danger">Categoría no encontrada.</div>';
    return;
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Editar Categoría: <?= htmlspecialchars($cat->getNombre()) ?></h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <form method="POST" action="?accion=categoria_actualizar">
            <input type="hidden" name="id" value="<?= $cat->getId() ?>">

            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($cat->getNombre()) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción:</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($cat->getDescripcion()) ?></textarea>
            </div>

            <div class="alert alert-info">
                <strong>Ruta:</strong> <?= htmlspecialchars($cat->getRutaCompleta()) ?><br>
                <strong>Nivel:</strong> <?= $cat->getNivel() ?><br>
                <strong>Productos totales:</strong> <?= $cat->contarProductosTotales() ?>
            </div>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="?accion=categorias" class="btn btn-secondary">Volver a Categorías</a>
        </form>
    </div>
</div>