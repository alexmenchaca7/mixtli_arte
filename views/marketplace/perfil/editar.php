<main class="contenedor seccion">
    
    <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
        <a href="/comprador/perfil" class="boton-rosa" style="display: inline-block;">Volver al Perfil</a>
    </div>

    <div class="formulario-contenedor">
        <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
        
        <form method="POST" action="/comprador/perfil/editar" enctype="multipart/form-data" class="formulario">
            <fieldset class="formulario__fieldset">
                <legend class="formulario__legend">Información Personal</legend>
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre*</label>
                    <input type="text" class="formulario__input" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario->nombre ?? ''); ?>" required>
                </div>
                <div class="formulario__campo">
                    <label for="apellido" class="formulario__label">Apellido*</label>
                    <input type="text" class="formulario__input" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario->apellido ?? ''); ?>" required>
                </div>
                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Email*</label>
                    <input type="email" class="formulario__input" id="email" name="email" value="<?php echo htmlspecialchars($usuario->email ?? ''); ?>" required>
                </div>
                <div class="formulario__campo">
                    <label for="telefono" class="formulario__label">Teléfono</label>
                    <input type="tel" class="formulario__input" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario->telefono ?? ''); ?>">
                </div>
            </fieldset>

            <fieldset class="formulario__fieldset">
                <legend class="formulario__legend">Dirección Residencial</legend>
                <div class="formulario__campo">
                    <label for="calle_residencial" class="formulario__label">Calle y número</label>
                    <input type="text" class="formulario__input" id="calle_residencial" name="calle_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'calle'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="colonia_residencial" class="formulario__label">Colonia</label>
                    <input type="text" class="formulario__input" id="colonia_residencial" name="colonia_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'colonia'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="codigo_postal_residencial" class="formulario__label">Código Postal</label>
                    <input type="text" class="formulario__input" id="codigo_postal_residencial" name="codigo_postal_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'codigo_postal'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="ciudad_residencial" class="formulario__label">Ciudad</label>
                    <input type="text" class="formulario__input" id="ciudad_residencial" name="ciudad_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'ciudad'); ?>">
                </div>
                <div class="formulario__campo">
                    <label for="estado_residencial" class="formulario__label">Estado</label>
                    <input type="text" class="formulario__input" id="estado_residencial" name="estado_residencial" value="<?php echo obtenerDireccion($direcciones ?? [], 'residencial', 'estado'); ?>">
                </div>
            </fieldset>

            <fieldset class="formulario__fieldset">
                <legend class="formulario__legend">Preferencias de Compra</legend>
                <p>Selecciona las categorías de artesanías que más te interesan.</p>
                <div class="preferencias__grid">
                    <?php foreach($categorias as $categoria): ?>
                        <label class="preferencia__label">
                            <input type="checkbox" name="categorias[]" value="<?php echo $categoria->id; ?>" <?php echo in_array($categoria->id, $categoriasSeleccionadas) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($categoria->nombre); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <input type="submit" class="formulario__submit" value="Guardar Cambios">
        </form>
    </div>
</main>

<style>
    /* Estilos para centrar y dar formato al contenedor del formulario */
    .formulario-contenedor {
        max-width: 80rem;
        margin: 0 auto;
        padding: 3rem;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .preferencias__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .preferencia__label {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.8rem;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .preferencia__label:hover {
        background-color: #f7f7f7;
    }
</style>