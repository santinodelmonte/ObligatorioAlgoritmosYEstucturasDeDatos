<?php
/*
 * Clase: DetallePedido
 * Elemento de línea para `Pedido` que contiene producto, cantidad
 * y precio unitario. Provee subtotal y getters/setters.
 */
class DetallePedido {
    private $id;
    private $producto; // Producto
    private $cantidad;
    private $precioUnitario;

    /* ---------------------- Constructor ---------------------- */
    public function __construct($id, $producto, $cantidad, $precioUnitario) {
        $this->id = $id;
        $this->producto = $producto;
        $this->cantidad = $cantidad;
        $this->precioUnitario = $precioUnitario;
    }

    public function getId() { return $this->id; }
    public function getProducto() { return $this->producto; }
    public function getCantidad() { return $this->cantidad; }
    public function getPrecioUnitario() { return $this->precioUnitario; }

    public function setCantidad($c) { $this->cantidad = (int)$c; }
    public function setPrecioUnitario($p) { $this->precioUnitario = $p; }

    /* ------------------ Utilidades ------------------ */
    public function subtotal() {
        return $this->cantidad * $this->precioUnitario;
    }
}