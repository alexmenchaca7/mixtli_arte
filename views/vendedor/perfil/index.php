<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/vendedor/editar-telefono">
        <i class="fa-solid fa-phone"></i>
        Editar Teléfono
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
    
    <form method="POST" action="/vendedor/perfil" enctype="multipart/form-data" class="formulario">
        <div class="formulario__campo">
            <label for="nombre" class="formulario__label">Nombre</label>
            <input 
                type="text"
                class="formulario__input"
                id="nombre"
                name="vendedor[nombre]"
                placeholder="Tu Nombre"
                value="<?php echo $vendedor->nombre ?? ''; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="apellido" class="formulario__label">Apellido</label>
            <input 
                type="text"
                class="formulario__input"
                id="apellido"
                name="vendedor[apellido]"
                placeholder="Tu Apellido"
                value="<?php echo $vendedor->apellido ?? ''; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="fecha_nacimiento" class="formulario__label">Fecha de Nacimiento</label>
            <input 
                type="date"
                class="formulario__input"
                id="fecha_nacimiento"
                name="vendedor[fecha_nacimiento]"
                value="<?php echo $vendedor->fecha_nacimiento ?? ''; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="sexo" class="formulario__label">Sexo</label>
            <select class="formulario__input" id="sexo" name="vendedor[sexo]">
                <option value="Masculino" <?php echo ($vendedor->sexo === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                <option value="Femenino" <?php echo ($vendedor->sexo === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
            </select>
        </div>

        <div class="formulario__campo">
            <label for="telefono" class="formulario__label">Teléfono</label>
            <input 
                type="tel"
                class="formulario__input"
                id="telefono"
                name="vendedor[telefono]"
                placeholder="Tu Teléfono"
                value="<?php echo $vendedor->telefono ?? ''; ?>"
                readonly
            >
        </div>

        <div class="formulario__campo">
            <label for="direccion_residencia" class="formulario__label">Dirección de Residencia</label>
            <input 
                type="text"
                class="formulario__input"
                id="direccion_residencia"
                name="vendedor[direccion_residencia]"
                placeholder="Tu Dirección de Residencia"
                value="<?php echo $vendedor->direccion_residencia ?? ''; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="direccion_comercial" class="formulario__label">Dirección Comercial</label>
            <input 
                type="text"
                class="formulario__input"
                id="direccion_comercial"
                name="vendedor[direccion_comercial]"
                placeholder="Tu Dirección Comercial"
                value="<?php echo $vendedor->direccion_comercial ?? ''; ?>"
            >
        </div>

        <div class="formulario__campo">
            <label for="email" class="formulario__label">Email</label>
            <input 
                type="email"
                class="formulario__input"
                id="email"
                name="vendedor[email]"
                placeholder="Tu Email"
                value="<?php echo $vendedor->email ?? ''; ?>"
            >
        </div>

        <input type="submit" class="formulario__submit" value="Actualizar Perfil">
    </form>
</div>