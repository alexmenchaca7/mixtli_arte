<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información General</legend>

    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input 
            type="text"
            class="formulario__input"
            placeholder="Nombre"
            id="nombre"
            name="nombre"
            value="<?php echo $categoria->nombre; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="descripcion" class="formulario__label">Descripción (opcional)</label>
        <input 
            type="text"
            class="formulario__input"
            placeholder="Descripción"
            id="descripcion"
            name="descripcion"
            value="<?php echo $categoria->descripcion; ?>"
        >
    </div>
</fieldset>