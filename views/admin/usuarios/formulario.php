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
            value="<?php echo $usuario->nombre; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="apellido" class="formulario__label">Apellido</label>
        <input 
            type="text"
            class="formulario__input"
            placeholder="Apellido"
            id="apellido"
            name="apellido"
            value="<?php echo $usuario->apellido; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="fecha_nacimiento" class="formulario__label">Fecha de Nacimiento</label>
        <input 
            type="date"
            class="formulario__input"
            id="fecha_nacimiento"
            name="fecha_nacimiento"
            value="<?php echo $usuario->fecha_nacimiento; ?>"
            max="<?php echo $fecha_hoy; ?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="sexo" class="formulario__label">Sexo</label>
        <select class="formulario__input" name="sexo" id="sexo">
            <option value="" disabled <?php echo empty($usuario->sexo) ? 'selected' : ''; ?>>--Seleccione--</option>
            <option value="Femenino" <?php echo ($usuario->sexo === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
            <option value="Masculino" <?php echo ($usuario->sexo === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
        </select>
    </div>

    <div class="formulario__campo">
        <label for="rol" class="formulario__label">Tipo de usuario</label>
        <select class="formulario__input" name="rol" id="rol">
            <option value="" disabled <?php echo empty($usuario->rol) ? 'selected' : ''; ?>>--Seleccione--</option>
            <option value="comprador" <?php echo ($usuario->rol === 'comprador') ? 'selected' : ''; ?>>Comprador</option>
            <option value="vendedor" <?php echo ($usuario->rol === 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
            <option value="admin" <?php echo ($usuario->rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
        </select>
    </div>
    
    <div class="formulario__campo">
        <label for="email" class="formulario__label">Email</label>
        <input 
            type="email"
            class="formulario__input"
            placeholder="Email"
            id="email"
            name="email"
            value="<?php echo $usuario->email; ?>"
        >
    </div>
</fieldset>