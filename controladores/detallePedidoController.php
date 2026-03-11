<?php
require_once __DIR__ . "/../clases/detallePedido.php";

class DetallePedidoController {
    public function listar($pedido = null) {
        if (!isset($_SESSION)) session_start();

        // Sin pedido: devolver todos los detalles de todos los pedidos (flat)
        if ($pedido === null) {
            $all = [];
            foreach ($_SESSION['pedidos'] ?? [] as $p) {
                if (method_exists($p, 'getDetalles')) {
                    foreach ($p->getDetalles() as $d) $all[] = $d;
                }
            }
            return $all;
        }

        // Si pasaron un id
        if (is_int($pedido) || ctype_digit(strval($pedido))) {
            $id = (int)$pedido;
            foreach ($_SESSION['pedidos'] ?? [] as $p) {
                if (method_exists($p, 'getId') && $p->getId() == $id) {
                    return $p->getDetalles();
                }
            }
            return [];
        }

        // Si pasaron un objeto pedido
        if (is_object($pedido) && method_exists($pedido, 'getDetalles')) {
            return $pedido->getDetalles();
        }

        return [];
    }

    public function agregarEnPedido($pedido, $producto, $cantidad, $precioUnitario) {
        if (!$pedido) return false;
        $pedido->agregarDetalle($producto, $cantidad, $precioUnitario);
        return true;
    }

    public function eliminarDelPedido($pedido, $idDetalle) {
        if (!$pedido) return false;
        return $pedido->eliminarDetalle($idDetalle);
    }
}
?>