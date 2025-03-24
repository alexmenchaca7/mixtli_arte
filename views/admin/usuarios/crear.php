<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/usuarios">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

    <?php if(isset($_GET['confirmacion']) && $_GET['confirmacion'] == 1): ?>
        <p class="alerta alerta__exito">Usuario registrado correctamente. Revisa el email para confirmar la cuenta</p>
    <?php endif; ?>
    
    <form method="POST" action="/admin/usuarios/crear" enctype="multipart/form-data" class="formulario">
        <?php include_once __DIR__ . '/formulario.php'; ?>

        <input class="formulario__submit formulario__submit--registrar" type="submit" value="Registrar Usuario">
    </form>
</div>