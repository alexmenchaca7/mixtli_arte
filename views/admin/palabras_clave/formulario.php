<fieldset class="formulario__fieldset">
    <legend class="formulario__legend">Información de la Palabra Clave</legend>

    <div class="formulario__campo">
        <label for="palabra" class="formulario__label">Palabra Clave</label>
        <input type="text" class="formulario__input" id="palabra" name="palabra" placeholder="Ej: registro, iniciar sesion, etc." value="<?php echo $palabra->palabra ?? ''; ?>">
    </div>
</fieldset>