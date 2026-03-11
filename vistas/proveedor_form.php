<?php
$prov = $proveedor ?? null;
$esEdicion = $prov !== null;
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= $esEdicion ? "Editar Proveedor" : "Nuevo Proveedor" ?></h5>
        <a href="?accion=home" class="btn btn-outline-secondary btn-sm">← Volver al Menú</a>
    </div>
    <div class="card-body">
        <form method="POST" action="?accion=<?= $esEdicion ? 'proveedor_actualizar' : 'proveedor_guardar' ?>">
            <input type="hidden" name="id" value="<?= $esEdicion ? $prov->getId() : 0 ?>">

            <div class="mb-3">
                <label class="form-label">Nombre de la Empresa</label>
                <input type="text" name="nombreEmpresa" class="form-control" required value="<?= $esEdicion ? htmlspecialchars($prov->getNombreEmpresa()) : '' ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Contacto</label>
                <input type="text" name="contacto" class="form-control" value="<?= $esEdicion ? htmlspecialchars($prov->getContacto()) : '' ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?= $esEdicion ? htmlspecialchars($prov->getTelefono()) : '' ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= $esEdicion ? htmlspecialchars($prov->getEmail()) : '' ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control" value="<?= $esEdicion ? htmlspecialchars($prov->getDireccion()) : '' ?>">
            </div>

            <button class="btn btn-primary"><?= $esEdicion ? 'Actualizar' : 'Crear' ?></button>
            <a href="?accion=proveedores" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
