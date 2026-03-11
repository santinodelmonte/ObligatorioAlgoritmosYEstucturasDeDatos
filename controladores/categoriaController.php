<?php

/*
 * Controlador: CategoriasController
 * Encapsula operaciones CRUD y utilidades para la gestión del árbol
 * de categorías (listado, búsqueda, mover, generar options para selects).
 */
require_once __DIR__ . "/../clases/categoria.php";

class CategoriasController {
    public function listar() {
        if (!isset($_SESSION)) session_start();
        return $_SESSION['categorias'] ?? [];
    }

    // Compatibilidad con vistas que llaman listarCategorias()
    public function listarCategorias() {
        return $this->listar();
    }

    public function buscarCategoria($id) {
        if (!isset($_SESSION)) session_start();
        foreach ($_SESSION['categorias'] as $raiz) {
            $res = $raiz->buscarPorId($id);
            if ($res) return $res;
        }
        return null;
    }

    public function categoriaAgregar($id, $nombre, $descripcion = "", $padreId = null) {
        if (!isset($_SESSION)) session_start();
        $nueva = new Categoria($id, $nombre, $descripcion);
        if ($padreId === null || $padreId == 0) {
            $_SESSION['categorias'][] = $nueva;
            return true;
        } else {
            $padre = $this->buscarCategoria($padreId);
            if ($padre) {
                return $padre->agregarSubcategoria($nueva);
            }
        }
        return false;
    }

    public function editarCategoria($id, $nombre, $descripcion) {
        $cat = $this->buscarCategoria($id);
        if ($cat) {
            $cat->setNombre($nombre);
            $cat->setDescripcion($descripcion);
            return true;
        }
        return false;
    }

    // Busca el padre y elimina la subcategoría.
    public function eliminarCategoria($id) {
        if (!isset($_SESSION)) session_start();
        // Verificar en raíces
        foreach ($_SESSION['categorias'] as $k => $raiz) {
            if ($raiz->getId() == $id) {
                // Sólo eliminar si puede ser eliminada
                if ($raiz->puedeSerEliminada()) {
                    unset($_SESSION['categorias'][$k]);
                    $_SESSION['categorias'] = array_values($_SESSION['categorias']);
                    return true;
                } else {
                    return false;
                }
            }
            // Buscar en subárbol y eliminar
            $parent = $this->buscarPadre($id, $raiz);
            if ($parent) {
                $hijosAntes = $parent->getSubcategorias();
                foreach ($hijosAntes as $h) {
                    if ($h->getId() == $id) {
                        if ($h->puedeSerEliminada()) {
                            return $parent->eliminarSubcategoria($id);
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
        return false;
    }

    // Retorna el nodo padre de la categoría con id buscado (o null)
    private function buscarPadre($id, $nodo) {
        foreach ($nodo->getSubcategorias() as $sub) {
            if ($sub->getId() == $id) return $nodo;
            $res = $this->buscarPadre($id, $sub);
            if ($res) return $res;
        }
        return null;
    }

    public function moverCategoria($id, $nuevoPadreId) {
        $cat = $this->buscarCategoria($id);
        $nuevoPadre = $this->buscarCategoria($nuevoPadreId);
        if (!$cat || !$nuevoPadre) return false;
        // Evitar ciclos
        if ($nuevoPadre->existeCiclo($cat)) return false;
        return $cat->moverA($nuevoPadre);
    }

    /**
     * Genera opciones <option> para un <select> con el árbol de categorías.
     * - $seleccionado: id que deberá marcarse selected (opcional)
     * - $excluirId: id que debe excluirse (y su subárbol). Útil al mover una categoría.
     */
    public function generarOptions($seleccionado = null, $excluirId = null) {
        $html = "";
        foreach ($this->listar() as $raiz) {
            $html .= $this->generarOptionsNodo($raiz, 0, $seleccionado, $excluirId);
        }
        return $html;
    }

    // Función recursiva que construye las opciones desde un nodo
    private function generarOptionsNodo($nodo, $nivel = 0, $seleccionado = null, $excluirId = null) {
        // Si el nodo es el excluido, omitimos todo su subárbol
        if ($excluirId !== null && $nodo->getId() == $excluirId) {
            return "";
        }

        $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);
        $selectedAttr = ($seleccionado !== null && $seleccionado == $nodo->getId()) ? " selected" : "";
        // Usar htmlspecialchars en el nombre
        $nombre = htmlspecialchars($nodo->getNombre(), ENT_QUOTES, 'UTF-8');

        $html = "<option value='{$nodo->getId()}'{$selectedAttr}>{$indent}{$nombre}</option>\n";

        foreach ($nodo->getSubcategorias() as $sub) {
            $html .= $this->generarOptionsNodo($sub, $nivel + 1, $seleccionado, $excluirId);
        }

        return $html;
    }
}
?>
