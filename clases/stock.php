<?php
/*
 * Clase: Stock
 * Representa el inventario asociado a un `Producto` con cantidad,
 * ubicación y reglas para agregar/descontar unidades.
 */
class Stock {
    private $id;
    private $producto; // instancia de Producto
    private $cantidad;
    private $ubicacion;
    private $fechaUltimaActualizacion;
    private $stockMinimo;

    /* ---------------------- Constructor ---------------------- */
    public function __construct($id, $producto, $cantidad = 0, $ubicacion = "", $stockMinimo = 0) {
        $this->id = $id;
        $this->producto = $producto;
        $this->cantidad = (int)$cantidad;
        $this->ubicacion = $ubicacion;
        $this->fechaUltimaActualizacion = date('Y-m-d H:i:s');
        $this->stockMinimo = (int)$stockMinimo;
    }

    /* ------------------ Operaciones de stock ------------------ */
    // Incrementa el stock. $fecha es opcional (YYYY-MM-DD HH:MM:SS o date()-compatible)
    public function agregarStock($cantidad, $fecha = null) {
        $this->cantidad += (int)$cantidad;
        $this->fechaUltimaActualizacion = $fecha ? date('Y-m-d H:i:s', strtotime($fecha)) : date('Y-m-d H:i:s');
    }

    // Reduce el stock (no permite valores negativos). $fecha opcional
    public function descontarStock($cantidad, $fecha = null) {
        $this->cantidad = max(0, $this->cantidad - (int)$cantidad);
        $this->fechaUltimaActualizacion = $fecha ? date('Y-m-d H:i:s', strtotime($fecha)) : date('Y-m-d H:i:s');
    }

    // Verifica si está por debajo o en el stock mínimo
    public function verificarStockBajo() {
        return $this->cantidad <= $this->stockMinimo;
    }

    // Calcula el valor total del stock (cantidad * precio del producto)
    public function calcularValorTotal() {
        $precio = 0;
        if (is_object($this->producto) && method_exists($this->producto, 'getPrecio')) {
            $precio = (float)$this->producto->getPrecio();
        }
        return $this->cantidad * $precio;
    }

    // Setter para la fecha si se necesita asignar directamente
    public function setFechaUltimaActualizacion($fecha) {
        $this->fechaUltimaActualizacion = $fecha ? date('Y-m-d H:i:s', strtotime($fecha)) : date('Y-m-d H:i:s');
    }

    /* ---------------- Getters / Setters ---------------- */
    public function getId() { return $this->id; }
    public function getProducto() { return $this->producto; }
    public function getCantidad() { return $this->cantidad; }
    public function getUbicacion() { return $this->ubicacion; }
    public function getFechaUltimaActualizacion() { return $this->fechaUltimaActualizacion; }
    public function getStockMinimo() { return $this->stockMinimo; }

    public function setProducto($producto) { $this->producto = $producto; }
    public function setCantidad($cantidad) { $this->cantidad = (int)$cantidad; $this->fechaUltimaActualizacion = date('Y-m-d H:i:s'); }
    public function setUbicacion($ubicacion) { $this->ubicacion = $ubicacion; }
    public function setStockMinimo($stockMinimo) { $this->stockMinimo = (int)$stockMinimo; }
}
?>