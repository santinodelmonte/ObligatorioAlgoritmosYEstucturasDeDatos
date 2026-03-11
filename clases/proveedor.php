<?php

/*
 * Clase: Proveedor
 * Modelo simple para almacenar información de proveedores y
 * recuperar los productos que suministran desde la sesión.
 */
class Proveedor {
    private $id;
    private $nombreEmpresa;
    private $contacto;
    private $telefono;
    private $email;
    private $direccion;

    public function __construct($id, $nombreEmpresa, $contacto, $telefono, $email, $direccion) {
        $this->id = $id;
        $this->nombreEmpresa = $nombreEmpresa;
        $this->contacto = $contacto;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->direccion = $direccion;
    }

    /* ------------------ Consultas estáticas ------------------ */
    // Buscar proveedor por id en sesión
    public static function buscarPorId($id) {
        if (!isset($_SESSION)) session_start();
        foreach ($_SESSION['proveedores'] ?? [] as $prov) {
            if ($prov->getId() == $id) return $prov;
        }
        return null;
    }

    /* ------------------ Operaciones de instancia ------------------ */
    public function getProductos() {
        if (!isset($_SESSION)) session_start();
        $resultado = [];
        foreach ($_SESSION['productos'] ?? [] as $prod) {
            if ($prod->getProveedor()->getId() == $this->id) {
                $resultado[] = $prod;
            }
        }
        return $resultado;
    }

    public function contarProductos() {
        return count($this->getProductos());
    }

    // Getters y setters
    public function getId() { return $this->id; }
    public function getNombreEmpresa() { return $this->nombreEmpresa; }
    public function getContacto() { return $this->contacto; }
    public function getTelefono() { return $this->telefono; }
    public function getEmail() { return $this->email; }
    public function getDireccion() { return $this->direccion; }

    public function setNombreEmpresa($n) { $this->nombreEmpresa = $n; }
    public function setContacto($c) { $this->contacto = $c; }
    public function setTelefono($t) { $this->telefono = $t; }
    public function setEmail($e) { $this->email = $e; }
    public function setDireccion($d) { $this->direccion = $d; }
}
?>