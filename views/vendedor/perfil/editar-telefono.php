<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/vendedor/perfil">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
    
    <form method="POST" action="/vendedor/editar-telefono" class="formulario">
        <div class="formulario__campo">
            <label for="telefono" class="formulario__label">Teléfono</label>
            <input 
                type="tel"
                class="formulario__input"
                id="telefono"
                name="telefono"
                placeholder="Tu Teléfono"
                value="<?php echo $vendedor->telefono ?? ''; ?>"
            >
        </div>

        <input type="submit" class="formulario__submit" value="Editar Teléfono">
    </form>
</div>