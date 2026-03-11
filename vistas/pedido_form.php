<?php
require_once __DIR__ . "/../controladores/proveedorController.php";
$provController = new ProveedorController();
$proveedores = $provController->listar();
$productos = $_SESSION['productos'] ?? [];
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Crear Pedido</h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <form method="POST" action="?accion=pedido_guardar">
            <div class="mb-3">
                <label class="form-label">Proveedor</label>
                <select name="proveedor_id" class="form-select" required>
                    <option value="">-- Seleccionar proveedor --</option>
                    <?php foreach ($proveedores as $prov): ?>
                        <option value="<?= $prov->getId() ?>"><?= htmlspecialchars($prov->getNombreEmpresa()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h6>Detalles del pedido</h6>
            <div id="lineas">
                <div class="row g-2 mb-2 linea">
                    <div class="col-md-5">
                        <select name="producto[]" class="form-select" required>
                            <option value="">-- Producto --</option>
                            <?php 
                            $proveedorIdSeleccionado = $_POST['proveedor_id'] ?? null; // o de sesión
                            foreach ($productos as $prod): 
                                if (!$proveedorIdSeleccionado || $prod->getProveedor()->getId() == $proveedorIdSeleccionado):
                            ?>
                                <option value="<?= $prod->getId() ?>"><?= htmlspecialchars($prod->getNombre()) ?></option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input name="cantidad[]" type="number" class="form-control" placeholder="Cantidad" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <input name="precio[]" type="number" step="0.01" class="form-control" placeholder="Precio unit." required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar-linea">X</button>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button id="agregarLinea" type="button" class="btn btn-sm btn-outline-secondary">Añadir línea</button>
            </div>

            <button class="btn btn-primary">Crear Pedido</button>
            <a href="?accion=pedidos" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<script>
// JS mínimo para clonar línea
document.getElementById('agregarLinea').addEventListener('click', function() {
    const template = document.querySelector('.linea');
    const clone = template.cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = i.name === 'cantidad[]' ? 1 : '');
    document.getElementById('lineas').appendChild(clone);
    attachEliminar();
});
function attachEliminar(){
    document.querySelectorAll('.btn-eliminar-linea').forEach(btn=>{
        btn.onclick = function(){ if(document.querySelectorAll('.linea').length>1) this.closest('.linea').remove(); };
    });
}
attachEliminar();
</script>
