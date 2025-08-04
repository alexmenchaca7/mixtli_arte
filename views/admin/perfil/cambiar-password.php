<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/perfil">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
    
    <form method="POST" action="/admin/cambiar-password" class="formulario">
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
            <p style="font-size: 1.2rem; color: #666; margin-top: 0.5rem;">
                Mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.
            </p>
        </div>

        <input type="submit" class="formulario__submit" value="Cambiar Password">
    </form>
</div>