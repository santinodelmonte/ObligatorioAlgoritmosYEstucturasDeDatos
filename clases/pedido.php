<?php
require_once __DIR__ . "/detallePedido.php";

/*
 * Clase: Pedido
 * Modelo para pedidos de reposición: contiene proveedor, estado,
 * detalles (lista de DetallePedido) y lógica para calcular totales
 * y procesar la recepción (actualiza `Stock` en sesión).
 */
class Pedido {
    private $id;
    private $fechaPedido;
    private $proveedor; // Proveedor
    private $estado; // pendiente, recibido, cancelado
    private $detalles; // array DetallePedido
    private $total;

    /* ---------------------- Constructor ---------------------- */
    public function __construct($id, $proveedor) {
        $this->id = $id;
        $this->fechaPedido = date('Y-m-d H:i:s');
        $this->proveedor = $proveedor;
        $this->estado = 'pendiente';
        $this->detalles = [];
        $this->total = 0;
    }

    /* ------------------ Gestión de detalles ------------------ */
    public function agregarDetalle($producto, $cantidad, $precioUnitario) {
        $nextId = count($this->detalles) + 1;
        $detalle = new DetallePedido($nextId, $producto, $cantidad, $precioUnitario);
        $this->detalles[] = $detalle;
        $this->calcularTotal();
    }

    public function eliminarDetalle($idDetalle) {
        foreach ($this->detalles as $k => $d) {
            if ($d->getId() == $idDetalle) {
                unset($this->detalles[$k]);
                $this->detalles = array_values($this->detalles);
                $this->calcularTotal();
                return true;
            }
        }
        return false;
    }

    public function calcularTotal() {
        $sum = 0;
        foreach ($this->detalles as $d) {
            $sum += $d->getCantidad() * $d->getPrecioUnitario();
        }
        $this->total = $sum;
        return $this->total;
    }

    /* ------------------ Control de estado ------------------ */
    public function cambiarEstado($nuevoEstado) {
        $nuevoEstado = strtolower($nuevoEstado);
        $this->estado = $nuevoEstado;
        // Si se marca como recibido, actualizar stock en sesión
        if ($nuevoEstado === 'recibido') {
            $this->procesarRecepcion();
        }
    }

    // Procesa la recepción de este pedido y actualiza/crea registros de stock
    private function procesarRecepcion() {
        if (!isset($_SESSION)) session_start();
        foreach ($this->detalles as $detalle) {
            $producto = $detalle->getProducto();
            $cantidad = $detalle->getCantidad();

            // Buscar stock existente para ese producto
            $encontrado = false;
            foreach ($_SESSION['stocks'] ?? [] as $stock) {
                if ($stock->getProducto()->getId() == $producto->getId()) {
                    $stock->agregarStock($cantidad);
                    $encontrado = true;
                    break;
                }
            }
            // Si no existe stock, crear uno nuevo
            if (!$encontrado) {
                if (!isset($_SESSION['nextIds']['stocks'])) {
                    $_SESSION['nextIds']['stocks'] = count($_SESSION['stocks'] ?? []) + 1;
                }
                $nextId = $_SESSION['nextIds']['stocks']++;
                $_SESSION['stocks'][] = new Stock($nextId, $producto, $cantidad, 'Depósito (auto)', 0);
            }
        }
    }

    // Getters / setters
    public function getId() { return $this->id; }
    public function getFechaPedido() { return $this->fechaPedido; }
    public function getProveedor() { return $this->proveedor; }
    public function getEstado() { return $this->estado; }
    public function getDetalles() { return $this->detalles; }
    public function getTotal() { return $this->total; }
}
?>