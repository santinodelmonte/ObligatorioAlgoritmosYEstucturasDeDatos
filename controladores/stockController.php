<?php
require_once __DIR__ . "/../clases/stock.php";
require_once __DIR__ . "/../clases/producto.php";
require_once __DIR__ . "/productosController.php"; // para reconstrucciones si hace falta

/*
 * Controlador: StockController
 * Operaciones CRUD y utilidades sobre inventario en sesión, cálculo de
 * valor total y filtrado por categoría (incluye subcategorías).
 */
if (!class_exists('StockController')) {
    class StockController {
        public function listar() {
            if (!isset($_SESSION)) session_start();
            // Normalizar si hace falta (mantener lógica previa si existe)
            return $_SESSION['stocks'] ?? [];
        }

        // Buscar stock por su id
        public function buscarPorId($id) {
            if (!isset($_SESSION)) session_start();
            foreach ($_SESSION['stocks'] ?? [] as $s) {
                if ($s->getId() == $id) return $s;
            }
            return null;
        }

        // Buscar stock por productoId
        public function buscarPorProductoId($productoId) {
            if (!isset($_SESSION)) session_start();
            foreach ($_SESSION['stocks'] ?? [] as $s) {
                if ($s->getProducto()->getId() == $productoId) return $s;
            }
            return null;
        }

        // Crear nuevo registro de stock
        /* ------------------ Creación y modificación de stock ------------------ */
        public function crearStock($productoId, $cantidad = 0, $ubicacion = 'Depósito', $stockMinimo = 0) {
            if (!isset($_SESSION)) session_start();
            // buscar producto
            require_once __DIR__ . "/productosController.php";
            $pc = new ProductosController();
            $producto = $pc->buscarPorId((int)$productoId);
            if (!$producto) return null;
                // Usar generador centralizado de IDs en sesión
                if (!isset($_SESSION['nextIds']['stocks'])) {
                    $_SESSION['nextIds']['stocks'] = count($_SESSION['stocks'] ?? []) + 1;
                }
                $nextId = $_SESSION['nextIds']['stocks']++;
                $stock = new Stock($nextId, $producto, $cantidad, $ubicacion, $stockMinimo);
            $_SESSION['stocks'][] = $stock;
            return $stock;
        }

        // Agregar stock por idStock (fecha opcional)
        public function agregarStock($idStock, $cantidad, $fecha = null) {
            if (!isset($_SESSION)) session_start();
            foreach ($_SESSION['stocks'] as $s) {
                if ($s->getId() == $idStock) {
                    $s->agregarStock($cantidad, $fecha);
                    return true;
                }
            }
            return false;
        }

        // Descontar stock por idStock (fecha opcional)
        public function descontarStock($idStock, $cantidad, $fecha = null) {
            if (!isset($_SESSION)) session_start();
            foreach ($_SESSION['stocks'] as $s) {
                if ($s->getId() == $idStock) {
                    $s->descontarStock($cantidad, $fecha);
                    return true;
                }
            }
            return false;
        }

        public function valorTotalInventario() {
            $total = 0;
            foreach ($this->listar() as $s) {
                $total += $s->calcularValorTotal();
            }
            return $total;
        }

        // Filtrar stocks que pertenecen a una categoría (incluye subcategorías)
        public function filtrarPorCategoria($categoria) {
            if (!$categoria) return [];
            $productos = $categoria->obtenerTodosLosProductos();
            $stocks = [];
            foreach ($this->listar() as $s) {
                foreach ($productos as $p) {
                    if ($s->getProducto()->getId() == $p->getId()) {
                        $stocks[] = $s;
                        break;
                    }
                }
            }
            return $stocks;
        }
    }
}
?>
