<div class="dashboard__contenedor">
    <a class="dashboard__boton" href="/vendedor/mensajes">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver a Mensajes
    </a>
</div>

<div class="perfil-usuario contenedor seccion">
    <div class="perfil-usuario__body">
        <div class="perfil-usuario__info-principal" style="margin-bottom: 3rem;">
            <div class="perfil-usuario__imagen">
                <img src="/img/usuarios/<?php echo $comprador->imagen ? $comprador->imagen . '.png' : 'default.png'; ?>" alt="Imagen de <?php echo htmlspecialchars($comprador->nombre); ?>">
            </div>
            <div class="perfil-usuario__datos">
                <h1><?php echo htmlspecialchars($comprador->nombre . ' ' . $comprador->apellido); ?></h1>
                <p>Comprador</p>
                <?php if ($totalCalificaciones > 0): ?>
                    <div class="vendedor-rating">
                        <span>Calificación Promedio: <?php echo $promedioEstrellas; ?> ⭐</span>
                        <span>(<?php echo $totalCalificaciones; ?> calificaciones)</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="perfil-card">
            <h3>Calificaciones Recibidas de Vendedores</h3>
            <?php if(!empty($valoraciones)): ?>
                <?php foreach($valoraciones as $valoracion): ?>
                    <div class="valoracion-item">
                        <div class="valoracion-item__header">
                            <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                            <?php if($valoracion->producto): ?>
                                <span class="valoracion-item__producto">Sobre el producto: <strong><?php echo htmlspecialchars($valoracion->producto->nombre); ?></strong></span>
                            <?php endif; ?>
                        </div>
                        <?php if($valoracion->comentario): ?>
                            <p class="valoracion-item__comentario">"<?php echo htmlspecialchars($valoracion->comentario); ?>"</p>
                        <?php endif; ?>
                        <div class="valoracion-item__footer">
                            <span>Fecha: <?php echo date('d/m/Y', strtotime($valoracion->creado)); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Este comprador aún no ha recibido calificaciones.</p>
            <?php endif; ?>
        </div>
    </div>
</div>