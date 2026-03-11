<?php
/*
 * Controlador: ProductosController
 * Provee CRUD y filtros para productos en sesión, incluyendo búsquedas
 * por categoría (recursiva) y filtrado por proveedor.
 */
require_once __DIR__ . "/../clases/producto.php";
require_once __DIR__ . "/../clases/stock.php";

class ProductosController {
    public function listar() {
        if (!isset($_SESSION)) session_start();
        return $_SESSION['productos'] ?? [];
    }

    public function buscarPorId($id) {
        if (!isset($_SESSION)) session_start();
        foreach ($_SESSION['productos'] ?? [] as $p) {
            if ($p->getId() == $id) return $p;
        }
        return null;
    }

    public function agregar($id, $nombre, $descripcion, $precio, $categoria, $proveedor) {
        if (!isset($_SESSION)) session_start();
        $nuevo = new Producto($id, $nombre, $descripcion, $precio, $categoria, $proveedor);
        $_SESSION['productos'][] = $nuevo;
        global $productos;
        $productos = &$_SESSION['productos'];
        // Crear registro de stock inicial (cantidad 0) para que el producto
        // aparezca inmediatamente en el listado de stocks y pueda modificarse.
        if (!isset($_SESSION['stocks'])) $_SESSION['stocks'] = [];
        if (!isset($_SESSION['nextIds']['stocks'])) {
            $_SESSION['nextIds']['stocks'] = count($_SESSION['stocks']) + 1;
        }
        $nextStockId = $_SESSION['nextIds']['stocks']++;
        $_SESSION['stocks'][] = new Stock($nextStockId, $nuevo, 0, 'Depósito', 0);

        return $nuevo;
    }

    public function editar($id, $nombre, $descripcion, $precio = null) {
        $p = $this->buscarPorId($id);
        if (!$p) return false;
        $p->setNombre($nombre);
        $p->setDescripcion($descripcion);
        if ($precio !== null) $p->setPrecio($precio);
        return true;
    }

    public function eliminar($id) {
        if (!isset($_SESSION)) session_start();
        foreach ($_SESSION['productos'] as $k => $p) {
            if ($p->getId() == $id) {
                // Eliminar stocks asociados
                foreach ($_SESSION['stocks'] ?? [] as $si => $s) {
                    if ($s->getProducto()->getId() == $id) {
                        unset($_SESSION['stocks'][$si]);
                    }
                }
                $_SESSION['stocks'] = array_values($_SESSION['stocks'] ?? []);
                // Eliminar producto
                unset($_SESSION['productos'][$k]);
                $_SESSION['productos'] = array_values($_SESSION['productos']);
                global $productos;
                $productos = &$_SESSION['productos'];
                return true;
            }
        }
        return false;
    }

    // Buscar productos por categoría (incluyendo subcategorías recursivamente)
    public function buscarPorCategoria($categoria) {
        if (!$categoria) return [];
        $productosCategoria = $categoria->obtenerTodosLosProductos();
        return $productosCategoria;
    }

    // Filtrar productos por proveedor
    public function filtrarPorProveedor($proveedorId) {
        $resultado = [];
        foreach ($this->listar() as $p) {
            if ($p->getProveedor()->getId() == $proveedorId) {
                $resultado[] = $p;
            }
        }
        return $resultado;
    }

    // Listar productos con stock bajo
    public function listarStockBajo() {
        if (!isset($_SESSION)) session_start();
        $stockController = new StockController();
        $resultado = [];
        foreach ($stockController->listar() as $s) {
            if ($s->verificarStockBajo()) {
                $resultado[] = $s->getProducto();
            }
        }
        return $resultado;
    }
}
?>