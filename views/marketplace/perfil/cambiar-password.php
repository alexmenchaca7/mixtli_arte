<main class="contenedor seccion">
    <h2 class="auth__heading"><?php echo $titulo; ?></h2>

    <div style="max-width: 60rem; margin: 0 auto;">
        <div class="dashboard__contenedor-boton" style="margin-bottom: 2rem;">
            <a class="dashboard__boton" href="/comprador/perfil/editar">
                <i class="fa-solid fa-circle-arrow-left"></i>
                Volver
            </a>
        </div>
    
        <div class="dashboard__formulario">
            <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>
            
            <form method="POST" class="formulario">
                <div class="formulario__campo">
                    <label for="password_actual" class="formulario__label">Contraseña Actual</label>
                    <input 
                        type="password"
                        class="formulario__input"
                        id="password_actual"
                        name="password_actual"
                        placeholder="Tu Contraseña Actual"
                    >
                </div>
        
                <div class="formulario__campo">
                    <label for="password_nuevo" class="formulario__label">Nueva Contraseña</label>
                    <input 
                        type="password"
                        class="formulario__input"
                        id="password_nuevo"
                        name="password_nuevo"
                        placeholder="Tu Nueva Contraseña"
                    >
                    <p style="font-size: 1.2rem; color: #666; margin-top: 0.5rem;">
                        Mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.
                    </p>
                </div>
        
                <input type="submit" class="formulario__submit" value="Cambiar Contraseña">
            </form>
        </div>
    </div>
</main>