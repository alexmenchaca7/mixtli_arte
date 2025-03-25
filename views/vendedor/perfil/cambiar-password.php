<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/vendedor/perfil">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
    
    <form method="POST" action="/vendedor/cambiar-password" class="formulario">
    <div class="formulario__campo">
            <label for="password_actual" class="formulario__label">Password Actual</label>
            <input 
                type="password"
                class="formulario__input"
                id="password_actual"
                name="password_actual"
                placeholder="Tu Password Actual"
            >
        </div>

        <div class="formulario__campo">
            <label for="password_nuevo" class="formulario__label">Nuevo Password</label>
            <input 
                type="password"
                class="formulario__input"
                id="password_nuevo"
                name="password_nuevo"
                placeholder="Tu Nuevo Password"
            >
            <p class="formulario__texto">El password debe contener al menos 6 caracteres</p>
        </div>

        <input type="submit" class="formulario__submit" value="Cambiar Password">

    </form>
</div>