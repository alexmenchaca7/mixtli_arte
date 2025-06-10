<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/comprador/valoraciones">
        <i class="fa-solid fa-star"></i>
        Ver mis Valoraciones
    </a>
</div>

<div class="dashboard__formulario">
    <?php include_once __DIR__ . '/../../../templates/alertas.php'; ?>
    
    <form method="POST" enctype="multipart/form-data" class="formulario">
        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Información Personal</legend>
            </fieldset>

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Dirección Residencial</legend>
            </fieldset>

        <fieldset class="formulario__fieldset">
            <legend class="formulario__legend">Preferencias de Compra</legend>
            <p>Selecciona las categorías de artesanías que más te interesan para personalizar tu experiencia.</p>
            <div class="preferencias__grid">
                <?php foreach($categorias as $categoria): ?>
                    <label class="preferencia__label">
                        <input 
                            type="checkbox" 
                            name="categorias[]" 
                            value="<?php echo $categoria->id; ?>"
                            <?php echo in_array($categoria->id, $categoriasSeleccionadas) ? 'checked' : ''; ?>
                        >
                        <?php echo htmlspecialchars($categoria->nombre); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <input type="submit" class="formulario__submit" value="Actualizar Perfil">
    </form>
</div>