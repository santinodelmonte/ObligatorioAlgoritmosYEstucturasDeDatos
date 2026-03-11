<?php
/*
 * Controlador: ProveedorController
 * Gestión CRUD de proveedores y validaciones para eliminar (no eliminar
 * si existen productos asociados en sesión).
 */
require_once __DIR__ . "/../clases/proveedor.php";

class ProveedorController {
    public function listar() {
        if (!isset($_SESSION)) session_start();
        return $_SESSION['proveedores'] ?? [];
    }

    public function buscarPorId($id) {
        return Proveedor::buscarPorId($id);
    }

    public function agregar($id, $nombreEmpresa, $contacto, $telefono, $email, $direccion) {
        if (!isset($_SESSION)) session_start();
        $nuevo = new Proveedor($id, $nombreEmpresa, $contacto, $telefono, $email, $direccion);
        $_SESSION['proveedores'][] = $nuevo;
        return $nuevo;
    }

    public function editar($id, $nombreEmpresa, $contacto, $telefono, $email, $direccion) {
        if (!isset($_SESSION)) session_start();
        foreach ($_SESSION['proveedores'] as $p) {
            if ($p->getId() == $id) {
                $p->setNombreEmpresa($nombreEmpresa);
                $p->setContacto($contacto);
                $p->setTelefono($telefono);
                $p->setEmail($email);
                $p->setDireccion($direccion);
                return true;
            }
        }
        return false;
    }

    // Evitar eliminar proveedor si aún suministra productos
    public function eliminar($id) {
        if (!isset($_SESSION)) session_start();
        // verificar productos asociados
        foreach ($_SESSION['productos'] ?? [] as $prod) {
            $prov = null;
            if (is_object($prod) && method_exists($prod, 'getProveedor')) {
                $prov = $prod->getProveedor();
            }
            if ($prov && $prov->getId() == $id) {
                // Tiene productos asociados: no eliminar
                return false;
            }
        }

        foreach ($_SESSION['proveedores'] as $k => $p) {
            if ($p->getId() == $id) {
                unset($_SESSION['proveedores'][$k]);
                $_SESSION['proveedores'] = array_values($_SESSION['proveedores']);
                return true;
            }
        }
        return false;
    }
}
?>