<?php
/*
 * Clase: Producto
 * Representa un producto del catálogo con atributos básicos
 * y operaciones sencillas (cambio de categoría, descuento, etc.).
 */
class Producto {
    private $id;
    private $nombre;
    private $descripcion;
    private $precio;
    private $categoria; // instancia de Categoria
    private $proveedor; // instancia de Proveedor
    private $fechaRegistro;
    private $activo;

    /* ---------------------- Constructor ---------------------- */
    public function __construct($id, $nombre, $descripcion, $precio, $categoria, $proveedor) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->categoria = $categoria;
        $this->proveedor = $proveedor;
        $this->fechaRegistro = date('Y-m-d H:i:s');
        $this->activo = true;
    }

    /* ------------------ Operaciones principales ------------------ */
    public function cambiarCategoria($nuevaCategoria) {
        $this->categoria = $nuevaCategoria;
    }

    public function aplicarDescuento($porcentaje) {
        return round($this->precio * (1 - $porcentaje / 100), 2);
    }

    public function toString() {
        return "{$this->nombre} ({$this->id}) - {$this->descripcion} - \${$this->precio}";
    }

    /* ---------------- Getters / Setters ---------------- */
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getPrecio() { return $this->precio; }
    public function getCategoria() { return $this->categoria; }
    public function getProveedor() { return $this->proveedor; }
    public function getFechaRegistro() { return $this->fechaRegistro; }
    public function isActivo() { return $this->activo; }

    public function setNombre($n) { $this->nombre = $n; }
    public function setDescripcion($d) { $this->descripcion = $d; }
    public function setPrecio($p) { $this->precio = $p; }
    public function setProveedor($prov) { $this->proveedor = $prov; }
    public function setActivo($b) { $this->activo = (bool)$b; }
}
?>