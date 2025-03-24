<main class="auth">
    <?php require_once __DIR__ . '/../templates/alertas.php'; ?>

    <?php if($token_valido): ?>
        <form method="POST" class="formulario">
            <div class="formulario__campo">
                <label for="pass" class="formulario__label">Nuevo Password</label>
                <input 
                    type="password"
                    class="formulario__input"
                    placeholder="Tu Nuevo Password"
                    id="pass"
                    name="pass"
                >
            </div>

            <div class="formulario__campo">
                <label for="pass2" class="formulario__label">Confirmar Password</label>
                <input 
                    type="password"
                    class="formulario__input"
                    placeholder="Confirma tu Nuevo Password"
                    id="pass2"
                    name="pass2"
                >
            </div>

            <input type="submit" class="formulario__submit" value="Guardar Password">
        </form>
    <?php endif; ?>

    <div class="acciones--centrar">
        <a href="/login" class="acciones__enlace">¿Ya tienes una cuenta?</a>
    </div>
</main>