<?php
/*
 * Inicializador de la aplicación
 * - Carga las clases necesarias
 * - Población inicial en `$_SESSION` con categorías, proveedores,
 *   productos, stocks y pedidos de ejemplo.
 * - Inicializa `$_SESSION['nextIds']` para un generador simple de IDs.
 */
/* =========== CARGAR CLASES SIEMPRE ============ */
require_once "clases/categoria.php";
require_once "clases/producto.php";
require_once "clases/proveedor.php";
require_once "clases/stock.php";
require_once "clases/pedido.php";
require_once "clases/detallePedido.php";

/* =========== SI YA ESTÁ INICIALIZADO, SALIR ============ */
if (isset($_SESSION["inicializado"]) && $_SESSION["inicializado"] === true) {
    return;
}

/* ========= INICIALIZAR ARRAYS DE SESIÓN ============= */
$_SESSION["categorias"] = [];
$_SESSION["proveedores"] = [];
$_SESSION["productos"] = [];
$_SESSION["stocks"] = [];
$_SESSION["pedidos"] = [];

/* Inicializador de IDs para evitar colisiones al crear nuevas entidades */
$_SESSION['nextIds'] = [
    'categorias' => 13, // último id de categoria usado en este archivo + 1
    'proveedores' => 4,
    'productos' => 11,
    'stocks' => 11,
    'pedidos' => 3
];

/* =========== CREAR CATEGORÍAS (ÁRBOL REQUERIDO) ============ */
// Raíces
$catElectronica = new Categoria(1, "Electrónica", "Productos electrónicos");
$catRopa = new Categoria(2, "Ropa", "Indumentaria");
$catHogar = new Categoria(3, "Hogar", "Artículos para el hogar");

// Subcategorías Electrónica
$catCelulares = new Categoria(4, "Celulares", "Smartphones y accesorios");
$catComputadoras = new Categoria(5, "Computadoras", "PCs y laptops");
$catLaptops = new Categoria(6, "Laptops", "Computadoras portátiles");
$catDesktop = new Categoria(7, "Desktop", "PCs de escritorio");
$catAudio = new Categoria(8, "Audio", "Auriculares y parlantes");

// Subcategorías Ropa
$catHombre = new Categoria(9, "Hombre", "Ropa para hombre");
$catMujer = new Categoria(10, "Mujer", "Ropa para mujer");

// Subcategorías Hogar
$catCocina = new Categoria(11, "Cocina", "Accesorios de cocina");
$catDecoracion = new Categoria(12, "Decoración", "Decoración para el hogar");

// Construir árbol
$catElectronica->agregarSubcategoria($catCelulares);
$catElectronica->agregarSubcategoria($catComputadoras);
$catElectronica->agregarSubcategoria($catAudio);

$catComputadoras->agregarSubcategoria($catLaptops);
$catComputadoras->agregarSubcategoria($catDesktop);

$catRopa->agregarSubcategoria($catHombre);
$catRopa->agregarSubcategoria($catMujer);

$catHogar->agregarSubcategoria($catCocina);
$catHogar->agregarSubcategoria($catDecoracion);

// Insertar raíces en sesión
$_SESSION["categorias"][] = $catElectronica;
$_SESSION["categorias"][] = $catRopa;
$_SESSION["categorias"][] = $catHogar;

/* ========== CREAR PROVEEDORES ============== */
$prov1 = new Proveedor(1, "MegaTech SA", "Juan Pérez", "29001234", "contacto@megatech.com", "Av. Italia 1234");
$prov2 = new Proveedor(2, "Distribuidora Hogar SRL", "María Gómez", "24004567", "ventas@hogarsrl.com", "18 de Julio 4567");
$prov3 = new Proveedor(3, "SportMax SA", "Carlos López", "23007890", "contacto@sportmax.com", "Rivera 2020");

$_SESSION["proveedores"][] = $prov1;
$_SESSION["proveedores"][] = $prov2;
$_SESSION["proveedores"][] = $prov3;

/* ========== CREAR PRODUCTOS (mínimo 10) ============ */
$prod1 = new Producto(1, "Gaming Laptop XYZ", "Laptop 16GB, RTX", 120000, $catLaptops, $prov1);
$prod2 = new Producto(2, "Ultrabook Slim 13", "Laptop ligera 8GB", 90000, $catLaptops, $prov1);
$prod3 = new Producto(3, "Desktop Pro", "PC de escritorio i7", 80000, $catDesktop, $prov1);
$prod4 = new Producto(4, "Samsung Galaxy S21", "128GB", 65000, $catCelulares, $prov1);
$prod5 = new Producto(5, "iPhone 12", "128GB Negro", 75000, $catCelulares, $prov1);
$prod6 = new Producto(6, "Auriculares Bluetooth", "Inalámbricos", 4500, $catAudio, $prov1);
$prod7 = new Producto(7, "Silla de living", "Silla acolchonada", 3500, $catDecoracion, $prov2);
$prod8 = new Producto(8, "Sartén Antiadherente 28cm", "28cm premium", 1500, $catCocina, $prov2);
$prod9 = new Producto(9, "Remera Hombre", "Remera algodón", 1200, $catHombre, $prov3);
$prod10 = new Producto(10, "Vestido Mujer", "Vestido verano", 2800, $catMujer, $prov3);

$_SESSION["productos"] = [$prod1,$prod2,$prod3,$prod4,$prod5,$prod6,$prod7,$prod8,$prod9,$prod10];

/* ======== CREAR STOCK INICIAL (uno por producto) ============ */
$_SESSION["stocks"][] = new Stock(1, $prod1, 5, "Depósito A", 2);
$_SESSION["stocks"][] = new Stock(2, $prod2, 4, "Depósito A", 2);
$_SESSION["stocks"][] = new Stock(3, $prod3, 3, "Depósito A", 1);
$_SESSION["stocks"][] = new Stock(4, $prod4, 10, "Depósito B", 3);
$_SESSION["stocks"][] = new Stock(5, $prod5, 6, "Depósito B", 2);
$_SESSION["stocks"][] = new Stock(6, $prod6, 15, "Depósito B", 5);
$_SESSION["stocks"][] = new Stock(7, $prod7, 12, "Depósito C", 4);
$_SESSION["stocks"][] = new Stock(8, $prod8, 20, "Depósito C", 5);
$_SESSION["stocks"][] = new Stock(9, $prod9, 18, "Depósito C", 6);
$_SESSION["stocks"][] = new Stock(10, $prod10, 8, "Depósito C", 3);

/* ========== CREAR PEDIDOS DE EJEMPLO (mínimo 2) ============ */
$pedido1 = new Pedido(1, $prov1); // por defecto pendiente
$pedido1->agregarDetalle($prod1, 2, 115000);  // 2 laptops

$pedido2 = new Pedido(2, $prov2);
$pedido2->agregarDetalle($prod7, 5, 3200);   // sillas
$pedido2->agregarDetalle($prod8, 10, 1400);  // sartenes
// Intentar marcar recibido (si la clase implementa cambiarEstado)
if (method_exists($pedido2, 'cambiarEstado')) {
    $pedido2->cambiarEstado('recibido');
}

$_SESSION["pedidos"][] = $pedido1;
$_SESSION["pedidos"][] = $pedido2;

/* =========== LIGAR GLOBAL $productos (USADO POR arbol.php) ============ */
/* arbol.php usa "global $productos" dentro de los métodos de Categoria.
   Para mantener compatibilidad sin tocar arbol.php, creamos un alias. */
global $productos;
$productos = &$_SESSION["productos"];

/* ============ MARCAR COMO INICIALIZADO ========= */
$_SESSION["inicializado"] = true;

?>