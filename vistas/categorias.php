<?php
require_once __DIR__ . "/../controladores/categoriaController.php";

$controller = new CategoriasController();
$categorias = $controller->listarCategorias();
$busqueda = $_GET["buscar"] ?? "";
$resultadoBusqueda = [];

if (!empty($busqueda)) {
    // Intentar buscar por ID primero
    if (is_numeric($busqueda)) {
        $porId = $controller->buscarCategoria((int)$busqueda);
        if ($porId) {
            $resultadoBusqueda[] = $porId;
        }
    }
    
    // Si no encontró por ID, buscar por nombre
    if (empty($resultadoBusqueda)) {
        foreach ($categorias as $raiz) {
            $resultados = $raiz->buscarPorNombre($busqueda, []);
            $resultadoBusqueda = array_merge($resultadoBusqueda, $resultados);
        }
    }
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gestión de Categorías (Árbol N-ario)</h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <!-- Barra de búsqueda -->
        <form method="GET" class="mb-3">
            <input type="hidden" name="accion" value="categorias">
            <div class="input-group">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar categoría por nombre o ID..." value="<?= htmlspecialchars($busqueda) ?>">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                <?php if (!empty($busqueda)): ?>
                    <a href="?accion=categorias" class="btn btn-outline-secondary">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Botón para crear nueva categoría raíz -->
        <a href="?accion=categoria_nueva" class="btn btn-success mb-3">+ Nueva Categoría Raíz</a>

        <!-- Mostrar resultados de búsqueda si existen -->
        <?php if (!empty($busqueda) && !empty($resultadoBusqueda)): ?>
            <div class="alert alert-info">
                <strong>Resultados de búsqueda para "<?= htmlspecialchars($busqueda) ?>":</strong> (<?= count($resultadoBusqueda) ?> encontradas)
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Ruta Completa</th>
                            <th>Nivel</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultadoBusqueda as $cat): ?>
                            <tr>
                                <td><?= $cat->getId() ?></td>
                                <td><?= htmlspecialchars($cat->getNombre()) ?></td>
                                <td><?= htmlspecialchars($cat->getRutaCompleta()) ?></td>
                                <td><?= $cat->getNivel() ?></td>
                                <td><span class="badge bg-info"><?= $cat->contarProductosTotales() ?></span></td>
                                <td>
                                    <a href="?accion=productos&categoria=<?= $cat->getId() ?>" class="btn btn-sm btn-primary">Ver Productos</a>
                                    <a href="?accion=categoria_editar&id=<?= $cat->getId() ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="?accion=categoria_mover&id=<?= $cat->getId() ?>" class="btn btn-sm btn-info">Mover</a>
                                    <a href="?accion=categoria_eliminar&id=<?= $cat->getId() ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar categoría?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (!empty($busqueda) && empty($resultadoBusqueda)): ?>
            <div class="alert alert-warning">
                No se encontraron categorías que coincidan con "<?= htmlspecialchars($busqueda) ?>".
            </div>
        <?php endif; ?>

        <!-- Árbol completo de categorías (si no hay búsqueda) -->
        <?php if (empty($busqueda)): ?>
            <h6 class="mt-4 mb-3">Árbol Completo de Categorías:</h6>
            <div class="border p-3 bg-white" style="font-family: monospace; max-height: 600px; overflow-y: auto;">
                <?php foreach ($categorias as $raiz): ?>
                    <?php mostrarNodoArbol($raiz); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Función recursiva para mostrar un nodo y sus subcategorías
 * Demuestra recorrido pre-orden del árbol N-ario
 */
function mostrarNodoArbol($categoria, $nivel = 0) {
    $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);
    $productos = $categoria->contarProductosTotales();
    $esHoja = $categoria->esHoja() ? "🍂" : "📁";
    
    echo $indent . "<strong>{$esHoja} " . htmlspecialchars($categoria->getNombre()) . "</strong> ";
    echo "<span style='color: #666;'>(ID: " . $categoria->getId() . ", ";
    echo "Nivel: " . $categoria->getNivel() . ", ";
    echo "Productos: " . $productos . "</span>)</span><br>";
    
    
    // Botones de acción
    echo $indent . "<small>";
    echo "<a href='?accion=productos&categoria=" . $categoria->getId() . "' class='link-primary' style='text-decoration: none;'>Ver Productos</a> | ";
    echo "<a href='?accion=categoria_nueva&padre=" . $categoria->getId() . "' class='link-success' style='text-decoration: none;'>Agregar SubCategoria</a> | ";
    echo "<a href='?accion=categoria_editar&id=" . $categoria->getId() . "' class='link-warning' style='text-decoration: none;'>Editar</a> | ";
    echo "<a href='?accion=categoria_mover&id=" . $categoria->getId() . "' class='link-info' style='text-decoration: none;'>Mover</a> | ";
    echo "<a href='?accion=categoria_eliminar&id=" . $categoria->getId() . "' class='link-danger' onclick='return confirm(\"¿Eliminar?\");' style='text-decoration: none;'>Eliminar</a>";
    echo "</small><br><br>";
    
    // Recorrido recursivo pre-orden: procesar hijos
    foreach ($categoria->getSubcategorias() as $sub) {
        mostrarNodoArbol($sub, $nivel + 1);
    }
}
?>