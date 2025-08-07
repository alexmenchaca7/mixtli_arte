<main class="auth">
    
    <?php if(isset($_SESSION['rol']) && ($_SESSION['rol'] !== 'comprador')): ?>
        <h2 class="auth__heading"><?php echo $titulo; ?></h2>
    <?php endif; ?>

    <?php require_once __DIR__ . '/../templates/alertas.php'; ?>

    <?php if(isset($_SESSION['rol'])): ?>
        <?php if(isset($alertas['exito'])): ?>
            <div class="acciones--centrar">
                <a href="/login" class="acciones__enlace">Iniciar Sesión</a>
            </div>
        <?php else: ?>
            <div class="acciones--centrar">
                <a href="/" class="acciones__enlace">Regresar</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>