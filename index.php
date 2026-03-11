<?php
/*
 * Archivo: index.php
 * Punto de entrada de la aplicación. Rutea acciones via `?accion=`,
 * inicializa sesión y carga controladores y vistas principales.
 */
require_once __DIR__ . "/clases/categoria.php";
require_once __DIR__ . "/clases/producto.php";
require_once __DIR__ . "/clases/proveedor.php";
require_once __DIR__ . "/clases/stock.php";
require_once __DIR__ . "/clases/pedido.php";
require_once __DIR__ . "/clases/detallePedido.php";

session_start();
require_once "inicializador.php";

// Añadir alias global para compatibilidad con arbol.php (usa global $productos)
global $productos;
$productos = &$_SESSION["productos"];

require_once __DIR__ . "/controladores/categoriaController.php";
require_once __DIR__ . "/controladores/productosController.php";
require_once __DIR__ . "/controladores/stockController.php";
require_once __DIR__ . "/controladores/proveedorController.php";
require_once __DIR__ . "/controladores/pedidosController.php";
require_once __DIR__ . "/controladores/detallePedidoController.php";

$accion = $_GET["accion"] ?? "home";

function vista($archivo, $vars = [])
{
    extract($vars);
    require __DIR__ . "/vistas/$archivo.php";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema AED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
<?php
switch ($accion) {

    // ====================== CATEGORÍAS ======================
    case "categorias":
        vista("categorias");
        break;

    case "categoria_nueva":
        vista("categoriaAgregar"); // vista para crear categoría raíz o subcategoría
        break;

    case "categoria_guardar":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller = new CategoriasController();
            $nombre = trim($_POST["nombre"]);
            $descripcion = trim($_POST["descripcion"]);
            $padreId = $_POST["padre_id"] ?? null;

            if (!isset($_SESSION['nextIds']['categorias'])) {
                $_SESSION['nextIds']['categorias'] = count($_SESSION['categorias'] ?? []) + 1;
            }
            $nuevoId = $_SESSION['nextIds']['categorias']++;
            $controller->categoriaAgregar($nuevoId, $nombre, $descripcion, (int)$padreId ?: null);

            echo '<div class="alert alert-success">Categoría creada correctamente.</div>';
            vista("categorias");
        }
        break;

    case "categoria_editar":
        if (isset($_GET["id"])) {
            $controller = new CategoriasController();
            $categoria = $controller->buscarCategoria(intval($_GET["id"]));
            vista("categoriaEditar", ["categoria" => $categoria]);
        } else {
            echo '<div class="alert alert-warning">ID de categoría no especificado.</div>';
        }
        break;

    case "categoria_actualizar":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller = new CategoriasController();
            $id = intval($_POST["id"]);
            $nombre = trim($_POST["nombre"]);
            $descripcion = trim($_POST["descripcion"]);

            if ($controller->editarCategoria($id, $nombre, $descripcion)) {
                echo '<div class="alert alert-success">Categoría actualizada correctamente.</div>';
            } else {
                echo '<div class="alert alert-danger">Error al actualizar categoría.</div>';
            }
            vista("categorias");
        }
        break;

    case "categoria_mover":
        if (isset($_GET["id"])) {
            $controller = new CategoriasController();
            $categoria = $controller->buscarCategoria(intval($_GET["id"]));
            vista("categoriaMover", ["categoria" => $categoria]);
        }
        break;

    case "categoria_mover_guardar":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller = new CategoriasController();
            $id = intval($_POST["id"]);
            $nuevoPadreId = $_POST["nuevo_padre_id"] === "" ? null : intval($_POST["nuevo_padre_id"]);

            $categoria = $controller->buscarCategoria($id);
            if ($nuevoPadreId === null) {
                echo '<div class="alert alert-success">Categoría movida a raíz.</div>';
            } else {
                if ($controller->moverCategoria($id, $nuevoPadreId)) {
                    echo '<div class="alert alert-success">Categoría movida correctamente.</div>';
                } else {
                    echo '<div class="alert alert-danger">Error: no se puede mover la categoría (ciclo detectado o padre inválido).</div>';
                }
            }
            vista("categorias");
        }
        break;

    case "categoria_eliminar":
        if (isset($_GET["id"])) {
            $controller = new CategoriasController();
            if ($controller->eliminarCategoria(intval($_GET["id"]))) {
                echo '<div class="alert alert-success">Categoría eliminada correctamente.</div>';
            } else {
                echo '<div class="alert alert-danger">Error: no se puede eliminar la categoría (tiene subcategorías o productos asociados).</div>';
            }
            vista("categorias");
        }
        break;

    // ====================== PRODUCTOS ======================
    case "productos":
        vista("productos");
        break;

    case "producto_nuevo":
        vista("producto_form");
        break;

    case "producto_editar":
        if (isset($_GET["id"])) {
            $controller = new ProductosController();
            $producto = $controller->buscarPorId(intval($_GET["id"]));
            if ($producto) {
                vista("producto_form", ["producto" => $producto]);
            } else {
                echo '<div class="alert alert-warning">Producto no encontrado.</div>';
            }
        }
        break;

    case "producto_eliminar":
        if (isset($_GET["id"])) {
            $controller = new ProductosController();
            if ($controller->eliminar(intval($_GET["id"]))) {
                echo '<div class="alert alert-success">Producto eliminado correctamente.</div>';
            }
            vista("productos");
        }
        break;

    case "producto_guardar":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller = new ProductosController();

            $id = intval($_POST["id"]);
            $nombre = trim($_POST["nombre"]);
            $descripcion = trim($_POST["descripcion"]);
            $precio = floatval($_POST["precio"]);

            $categoriaId = intval($_POST["categoria"]);
            $proveedorId = intval($_POST["proveedor"]);

            $categoriaController = new CategoriasController();
            $categoria = $categoriaController->buscarCategoria($categoriaId);
            $proveedor = Proveedor::buscarPorId($proveedorId);

            if (!$categoria || !$proveedor) {
                echo '<div class="alert alert-danger">Categoría o proveedor inválido.</div>';
                vista("producto_form");
                break;
            }

            if ($id === 0) {
                // Crear nuevo producto
                if (!isset($_SESSION['nextIds']['productos'])) {
                    $_SESSION['nextIds']['productos'] = count($_SESSION['productos'] ?? []) + 1;
                }
                $nuevoId = $_SESSION['nextIds']['productos']++;
                $controller->agregar($nuevoId, $nombre, $descripcion, $precio, $categoria, $proveedor);
                echo '<div class="alert alert-success">Producto creado correctamente.</div>';
            } else {
                // Editar existente
                $controller->editar($id, $nombre, $descripcion, $precio);
                echo '<div class="alert alert-success">Producto actualizado correctamente.</div>';
            }

            vista("productos");
        }
        break;

    // ====================== STOCK ======================
case 'stocks':
    $controller = new StockController();
    $stocks = $controller->listar();
    // Pasamos también el controlador para que la vista pueda llamar valorTotalInventario()
    vista("stock", ["stocks" => $stocks, "controller" => $controller]);
    break;

case 'stock_bajo':
    vista('stock_bajo');
    break;

case 'stock_agregar':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idStock = (int)$_POST['idStock'];
        $cantidad = (int)$_POST['cantidad'];
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
        $controller = new StockController();
        $controller->agregarStock($idStock, $cantidad, $fecha);
        header("Location: index.php?accion=stocks");
        exit;
    }
    break;

case 'stock_descontar':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idStock = (int)$_POST['idStock'];
        $cantidad = (int)$_POST['cantidad'];
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
        $controller = new StockController();
        $controller->descontarStock($idStock, $cantidad, $fecha);
        header("Location: index.php?accion=stocks");
        exit;
    }
    break;

case 'stock_crear':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $productoId = (int)$_POST['productoId'];
        $cantidad = (int)$_POST['cantidad'];
        $stockMinimo = (int)$_POST['stockMinimo'];
        $ubicacion = trim($_POST['ubicacion'] ?? 'Depósito');
        $controller = new StockController();
        $controller->crearStock($productoId, $cantidad, $ubicacion, $stockMinimo);
        header("Location: index.php?accion=stocks");
        exit;
    }
    break;

    // ====================== PROVEEDORES ======================
    case "proveedores":
        vista("proveedores");
        break;

    case "proveedor_nuevo":
        vista("proveedor_form");
        break;

    case "proveedor_guardar":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller = new ProveedorController();
            $nombre = trim($_POST["nombreEmpresa"]);
            $contacto = trim($_POST["contacto"]);
            $telefono = trim($_POST["telefono"]);
            $email = trim($_POST["email"]);
            $direccion = trim($_POST["direccion"]);

            if (!isset($_SESSION['nextIds']['proveedores'])) {
                $_SESSION['nextIds']['proveedores'] = count($_SESSION['proveedores'] ?? []) + 1;
            }
            $nuevoId = $_SESSION['nextIds']['proveedores']++;
            $controller->agregar($nuevoId, $nombre, $contacto, $telefono, $email, $direccion);

            echo '<div class="alert alert-success">Proveedor creado correctamente.</div>';
            vista("proveedores");
        }
        break;

    case "proveedor_editar":
        if (isset($_GET["id"])) {
            $controller = new ProveedorController();
            $proveedor = $controller->buscarPorId(intval($_GET["id"]));
            vista("proveedor_form", ["proveedor" => $proveedor]);
        }
        break;

    case "proveedor_actualizar":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $controller = new ProveedorController();
            $id = intval($_POST["id"]);
            $nombre = trim($_POST["nombreEmpresa"]);
            $contacto = trim($_POST["contacto"]);
            $telefono = trim($_POST["telefono"]);
            $email = trim($_POST["email"]);
            $direccion = trim($_POST["direccion"]);

            if ($controller->editar($id, $nombre, $contacto, $telefono, $email, $direccion)) {
                echo '<div class="alert alert-success">Proveedor actualizado correctamente.</div>';
            } else {
                echo '<div class="alert alert-danger">Error al actualizar proveedor.</div>';
            }
            vista("proveedores");
        }
        break;

    case "proveedor_eliminar":
        if (isset($_GET["id"])) {
            $controller = new ProveedorController();
            if ($controller->eliminar(intval($_GET["id"]))) {
                echo '<div class="alert alert-success">Proveedor eliminado correctamente.</div>';
            } else {
                echo '<div class="alert alert-danger">No se puede eliminar proveedor: tiene productos asociados.</div>';
            }
            vista("proveedores");
        }
        break;

    case "proveedor_ver":
        if (isset($_GET["id"])) {
            // Mostrar vista de productos filtrada por proveedor
            $_GET['proveedor'] = intval($_GET["id"]);
            vista("productos");
        } else {
            echo '<div class="alert alert-warning">ID de proveedor no especificado.</div>';
        }
        break;

    // ====================== PEDIDOS ======================
    case "pedidos":
        vista("pedidos");
        break;

    case "pedido_nuevo":
        vista("pedido_form");
        break;

    case "pedido_guardar":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $provId = intval($_POST["proveedor_id"]);
            $productoIds = $_POST["producto"] ?? [];
            $cantidades = $_POST["cantidad"] ?? [];
            $precios = $_POST["precio"] ?? [];

            $prov = Proveedor::buscarPorId($provId);
            if (!$prov) {
                echo '<div class="alert alert-danger">Proveedor inválido.</div>';
                vista("pedidos");
                break;
            }

            $pedController = new PedidosController();
            if (!isset($_SESSION['nextIds']['pedidos'])) {
                $_SESSION['nextIds']['pedidos'] = count($_SESSION['pedidos'] ?? []) + 1;
            }
            $nuevoId = $_SESSION['nextIds']['pedidos']++;
            $pedido = $pedController->agregar($nuevoId, $prov);

            // Agregar detalles
            for ($i = 0; $i < count($productoIds); $i++) {
                $pid = intval($productoIds[$i]);
                $cantidad = intval($cantidades[$i] ?? 0);
                $precioUnit = floatval($precios[$i] ?? 0);
                if ($cantidad > 0 && $precioUnit > 0) {
                    $prodController = new ProductosController();
                    $prod = $prodController->buscarPorId($pid);
                    if ($prod) {
                        $pedido->agregarDetalle($prod, $cantidad, $precioUnit);
                    }
                }
            }

            // Guardar pedido (ya guardado por PedidosController->agregar en sesión)
            echo '<div class="alert alert-success">Pedido creado correctamente.</div>';
            vista("pedidos");
        }
        break;

    case "pedido_cambiar_estado":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = intval($_POST["id"]);
            $estado = trim($_POST["estado"]);
            $pedController = new PedidosController();
            if ($pedController->cambiarEstado($id, $estado)) {
                echo '<div class="alert alert-success">Estado del pedido actualizado.</div>';
            } else {
                echo '<div class="alert alert-danger">Error al cambiar estado del pedido.</div>';
            }
            vista("pedidos");
        }
        break;

    case "pedido_ver":
        if (isset($_GET["id"])) {
            $controller = new PedidosController();
            $pedido = $controller->buscar(intval($_GET["id"]));
            vista("pedido_detalle", ["pedido" => $pedido]);
        } else {
            echo '<div class="alert alert-warning">ID de pedido no especificado.</div>';
        }
        break;

    // ====================== MENÚ PRINCIPAL ======================
    default:
        ?>
        <div class="text-center">
            <h1 class="mb-4">Menú Principal</h1>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Categorías</h5>
                            <p class="card-text">Administrar categorías de productos.</p>
                            <a href="?accion=categorias" class="btn btn-primary">Ver Categorías</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Productos</h5>
                            <p class="card-text">Gestionar los productos del sistema.</p>
                            <a href="?accion=productos" class="btn btn-primary">Ver Productos</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Stock</h5>
                            <p class="card-text">Controlar el inventario disponible.</p>
                            <a href="?accion=stocks" class="btn btn-primary">Ver Stock</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Proveedores</h5>
                            <p class="card-text">Administrar proveedores del sistema.</p>
                            <a href="?accion=proveedores" class="btn btn-primary">Ver Proveedores</a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title">Pedidos</h5>
                            <p class="card-text">Gestionar los pedidos realizados.</p>
                            <a href="?accion=pedidos" class="btn btn-primary">Ver Pedidos</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        break;
}
?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>