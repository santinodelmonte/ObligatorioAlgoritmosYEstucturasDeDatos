<?php
require_once __DIR__ . "/../clases/pedido.php";

/*
 * Controlador: PedidosController
 * Gestiona creación, listado y cambio de estado de pedidos en sesión.
 */
class PedidosController {
    public function listar() {
        if (!isset($_SESSION)) session_start();
        return $_SESSION['pedidos'] ?? [];
    }

    public function buscar($id) {
        if (!isset($_SESSION)) session_start();
        foreach ($_SESSION['pedidos'] ?? [] as $p) {
            if ($p->getId() == $id) return $p;
        }
        return null;
    }

    public function agregar($id, $proveedor) {
        if (!isset($_SESSION)) session_start();
        $nuevo = new Pedido($id, $proveedor);
        $_SESSION['pedidos'][] = $nuevo;
        return $nuevo;
    }

    public function cambiarEstado($id, $estado) {
        $p = $this->buscar($id);
        if ($p) {
            $p->cambiarEstado($estado);
            return true;
        }
        return false;
    }
}
?>