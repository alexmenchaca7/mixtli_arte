<main class="auth">
    <h2 class="auth__heading"><?php echo $titulo; ?></h2>

    <?php require_once __DIR__ . '/../templates/alertas.php'; ?>

    <form method="POST" class="formulario">
        <div class="formulario__campo">
            <label for="codigo" class="formulario__label">Código de verificación</label>
            <input 
                type="text" 
                id="codigo" 
                name="codigo" 
                class="formulario__input"
                placeholder="Ingresa el código de 6 dígitos"
                autocomplete="off"
                required
            >
        </div>
        
        <input type="submit" class="formulario__submit" value="Verificar">
    </form>
    
    <div class="auth__acciones">
        <a href="/login" class="auth__enlace">Volver a inicio de sesión</a>
    </div>
</main>