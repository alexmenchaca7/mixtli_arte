<main class="auth">
    <h2 class="auth__heading"><?php echo $titulo; ?></h2>
    <p class="auth__texto">Estado de la Eliminación de tu Cuenta</p>

    <?php 
        // Incluir el template para mostrar las alertas
        require_once __DIR__ . '/../templates/alertas.php';
    ?>

    <?php if(isset($alertas['exito'])): ?>
        <div class="acciones">
            <a href="/" class="acciones__enlace">Volver al Inicio</a>
        </div>
    <?php else: ?>
         <div class="acciones">
            <a href="/comprador/perfil/editar" class="acciones__enlace">Volver al Perfil para Intentar de Nuevo</a>
        </div>
    <?php endif; ?>
</main>