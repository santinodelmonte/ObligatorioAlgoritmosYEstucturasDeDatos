<?php
/*
 * Clase: Categoria
 * Representa un nodo en el árbol N-ario de categorías.
 * Implementa operaciones de agregado/elimnación de subcategorías,
 * búsqueda recursiva, recorridos y utilidades asociadas al árbol.
 */
class Categoria
{
    private int $id;
    private string $nombre;
    private string $descripcion;
    private ?Categoria $categoriaPadre;
    private array $subcategorias;
    private int $nivel;

    // Constructor: $categoriaPadre es opcional (no agrega automáticamente al padre)
    public function __construct(int $id, string $nombre, string $descripcion = "", ?Categoria $categoriaPadre = null, int $nivel = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->categoriaPadre = $categoriaPadre;
        $this->subcategorias = [];
        if ($nivel !== null) {
            $this->nivel = $nivel;
        } else {
            $this->nivel = ($categoriaPadre !== null) ? ($categoriaPadre->getNivel() + 1) : 0;
        }
    }

    /* ---------------------- Constructor ---------------------- */
    // Constructor: $categoriaPadre es opcional (no agrega automáticamente al padre)
    
    /* ------------------ Gestión de Subcategorías ------------------ */
    // Agregar una subcategoría (establece padre y niveles recursivamente)
    public function agregarSubcategoria(Categoria $categoria): bool
    {
        // Evitar duplicados por id
        foreach ($this->subcategorias as $sub) {
            if ($sub->getId() === $categoria->getId()) return false;
        }
        $categoria->setCategoriaPadre($this);
        $categoria->setNivel($this->nivel + 1);
        $categoria->actualizarNivelesRecursivo();
        $this->subcategorias[] = $categoria;
        return true;
    }

    // Eliminar una subcategoría (directa o en profundidad). Devuelve true si se eliminó.
    public function eliminarSubcategoria(int $id): bool
    {
        // Buscar entre hijos directos
        foreach ($this->subcategorias as $idx => $sub) {
            if ($sub->getId() === $id) {
                array_splice($this->subcategorias, $idx, 1);
                return true;
            }
        }
        // Buscar recursivamente en subnodos
        foreach ($this->subcategorias as $sub) {
            if ($sub->eliminarSubcategoria($id)) return true;
        }
        return false;
    }

    /* ------------------ Recorridos y Búsqueda ------------------ */
    // Mostrar el árbol visualmente con indentación (devuelve string HTML/texto)
    // $html = true devuelve con <br> y &nbsp; para vistas web; false devuelve texto plano con \n
public function mostrarArbol(int $nivel = 0, bool $html = true): string
{
    // Indentación según formato
    $indentacion = $html
        ? str_repeat('&nbsp;', $nivel * 4)
        : str_repeat(' ', $nivel * 4);

    // Línea del nodo actual
    $linea = $indentacion . "<strong>" . htmlspecialchars($this->nombre) . "</strong>"
            . " <span style='color:#999'>(ID: {$this->id} | Nivel {$this->nivel})</span>";

    // Salto de línea según formato
    $salto = $html ? "<br>" : PHP_EOL;
    $salida = $linea . $salto;

    // Recorrido recursivo de subcategorías
    foreach ($this->subcategorias as $sub) {
        $salida .= $sub->mostrarArbol($nivel + 1, $html);
    }

    return $salida;
}

    // Búsqueda DFS por id en este subárbol
    public function buscarPorId(int $id): ?Categoria
    {
        if ($this->id === $id) return $this;
        foreach ($this->subcategorias as $sub) {
            $r = $sub->buscarPorId($id);
            if ($r !== null) return $r;
        }
        return null;
    }

    // Obtener ruta completa desde la raíz hasta este nodo (string: "Inicio > A > B")
    public function getRutaCompleta(string $inicio = "Inicio"): string
    {
        $nombres = [];
        $actual = $this;
        while ($actual !== null) {
            array_unshift($nombres, $actual->getNombre());
            $actual = $actual->getCategoriaPadre();
        }
        array_unshift($nombres, $inicio);
        return implode(" > ", $nombres);
    }

    /* ------------------ Consultas sobre Productos ------------------ */
    // Contar productos en esta categoría y todas sus subcategorías (busca en $_SESSION["productos"])
    public function contarProductosTotales(): int
{
    // Recolectar ids de categorías de esta rama
    $ids = $this->obtenerIdsRama();
    $count = 0;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }

    if (!isset($_SESSION["productos"]) || !is_array($_SESSION["productos"])) {
        return 0;
    }

    foreach ($_SESSION["productos"] as $p) {

        $catId = null;

        // Caso producto como objeto
        if (is_object($p)) {

            // getCategoria()
            if (method_exists($p, 'getCategoria')) {
                $cat = $p->getCategoria();

                if (is_object($cat) && method_exists($cat, 'getId')) {
                    $catId = $cat->getId();
                } elseif (is_int($cat)) {
                    $catId = $cat;
                }

            // propiedad categoria
            } elseif (property_exists($p, 'categoria')) {
                $cat = $p->categoria;

                if (is_object($cat) && property_exists($cat, 'id')) {
                    $catId = $cat->id;
                } elseif (is_int($cat)) {
                    $catId = $cat;
                }
            }
        }

        if ($catId !== null && in_array($catId, $ids, true)) {
            $count++;
        }
    }

    return $count;
}

    // Verificar si es hoja (no tiene subcategorías)
    public function esHoja(): bool
    {
        return count($this->subcategorias) === 0;
    }

    /* ------------------ Utilidades de árbol ------------------ */
    // Obtener array plano de nodos en pre-orden (útil para dropdowns)
    public function obtenerPreOrdenConNivel(int $nivelBase = null): array
    {
        $nivel = ($nivelBase !== null) ? $nivelBase : $this->nivel;
        $resultado = [['categoria' => $this, 'nivel' => $nivel]];
        foreach ($this->subcategorias as $sub) {
            $resultado = array_merge($resultado, $sub->obtenerPreOrdenConNivel($nivel + 1));
        }
        return $resultado;
    }

    // Obtener TODOS los productos de esta categoría y subcategorías (recursivo)
    public function obtenerTodosLosProductos(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        
        $resultado = [];
        $ids = $this->obtenerIdsRama();
        
        // Productos directos de esta categoría y subcategorías
        $productos = $_SESSION["productos"] ?? [];
        foreach ($productos as $prod) {
            $catObj = null;
            if (is_object($prod)) {
                if (method_exists($prod, 'getCategoria')) $catObj = $prod->getCategoria();
            }
            if ($catObj !== null) {
                $catId = null;
                if (is_object($catObj) && method_exists($catObj, 'getId')) {
                    $catId = $catObj->getId();
                }
                if ($catId !== null && in_array($catId, $ids, true)) {
                    $resultado[] = $prod;
                }
            }
        }
        
        return $resultado;
    }

    /* ---------------- Getters y Setters ---------------- */

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }
    public function getDescripcion(): string
    {
        return $this->descripcion;
    }
    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }
    public function getCategoriaPadre(): ?Categoria
    {
        return $this->categoriaPadre;
    }
    public function setCategoriaPadre(?Categoria $categoriaPadre): void
    {
        $this->categoriaPadre = $categoriaPadre;
    }

    // Devuelve array de objetos Categoria
    public function getSubcategorias(): array
    {
        return $this->subcategorias;
    }

    public function setSubcategorias(array $subcategorias): void
    {
        $this->subcategorias = $subcategorias;
    }

    public function getNivel(): int
    {
        return $this->nivel;
    }

    public function setNivel(int $nivel): void
    {
        $this->nivel = $nivel;
    }

    /* ---------------- Helpers privados ---------------- */

    // Actualiza niveles recursivamente según el atributo nivel actual
    private function actualizarNivelesRecursivo(): void
    {
        foreach ($this->subcategorias as $sub) {
            $sub->setNivel($this->nivel + 1);
            $sub->actualizarNivelesRecursivo();
        }
    }

    // Obtener array de ids de esta rama (incluye este nodo)
    private function obtenerIdsRama(): array
    {
        $ids = [$this->id];
        foreach ($this->subcategorias as $sub) {
            $ids = array_merge($ids, $sub->obtenerIdsRama());
        }
        return $ids;
    }

    /**
     * Verificar si puede ser eliminada
     * Solo se puede eliminar si no tiene hijos ni productos
     */
    public function puedeSerEliminada(): bool {
    // Si tiene subcategorías, no se puede eliminar
    if (!empty($this->subcategorias)) {
        return false;
    }

    // Si tiene productos en cualquier nivel, no se puede eliminar
    return $this->contarProductosTotales() === 0;
}

    /**
     * Verificar si hay ciclo (para evitar referencias circulares)
     * Devuelve true si $objetivo está en la cadena de ancestros de ESTE nodo
     */
    public function existeCiclo($objetivo): bool
    {
        // Si este nodo es el objetivo, hay ciclo
        if ($this->id == $objetivo->getId()) {
            return true;
        }

        // Recorrer hacia arriba en la jerarquía comprobando ancestros
        $actual = $this->categoriaPadre;
        while ($actual !== null) {
            if ($actual->getId() == $objetivo->getId()) {
                return true;
            }
            $actual = $actual->getCategoriaPadre();
        }

        return false;
    }

    /**
     * Mover esta categoría a un nuevo padre (o a raíz si $nuevoPadre es null)
     * - Elimina la referencia del padre actual (o de las raíces en sesión)
     * - Agrega la categoría al nuevo padre (o a las raíces en sesión)
     * - Actualiza niveles recursivamente
     */
    public function moverA(?Categoria $nuevoPadre): bool
    {

        if ($nuevoPadre !== null && $nuevoPadre->existeCiclo($this)) { return false; }
        
        // Si el nuevo padre es el mismo que el actual, no hacemos nada
        if ($this->categoriaPadre === $nuevoPadre) {
            return true;
        }

        // 1) Remover del padre actual o de las raíces en sesión
        if ($this->categoriaPadre !== null) {
            // Eliminar de la lista de subcategorías del padre actual
            $this->categoriaPadre->eliminarSubcategoria($this->id);
            $this->categoriaPadre = null;
        } else {
            // Si era raíz, eliminar del array de raíces en sesión
            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
            if (!empty($_SESSION['categorias'])) {
                foreach ($_SESSION['categorias'] as $idx => $raiz) {
                    if ($raiz->getId() === $this->id) {
                        unset($_SESSION['categorias'][$idx]);
                        $_SESSION['categorias'] = array_values($_SESSION['categorias']);
                        break;
                    }
                }
            }
        }

        // 2) Agregar al nuevo padre o a raíces en sesión
        if ($nuevoPadre === null) {
            // Mover a raíz
            $this->categoriaPadre = null;
            $this->setNivel(0);
            // Actualizar niveles de descendientes
            $this->actualizarNivelesRecursivo();

            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
            $_SESSION['categorias'][] = $this;
            return true;
        } else {
            // Verificar que no se intente crear ciclo
            if ($nuevoPadre->existeCiclo($this)) {
                return false;
            }
            // Agregar como subcategoría del nuevo padre (agregarSubcategoria setea padre y niveles)
            $nuevoPadre->agregarSubcategoria($this);
            return true;
        }
    }

    // Búsqueda por nombre (búsqueda parcial, case-insensitive)
    // Devuelve array con los nodos que coinciden
    public function buscarPorNombre(string $nombre, array $resultados = []): array
    {
        if (stripos($this->nombre, $nombre) !== false) {
            $resultados[] = $this;
        }

        foreach ($this->subcategorias as $sub) {
            // pasar el acumulador al hijo y recoger el resultado
            $resultados = $sub->buscarPorNombre($nombre, $resultados);
        }

        return $resultados;
    }
}
?>