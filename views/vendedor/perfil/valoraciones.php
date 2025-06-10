<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/vendedor/perfil">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver al Perfil
    </a>
</div>

<div class="dashboard__contenedor">
    <div class="valoraciones-summary">
        <h3 class="dashboard__subtitle">Resumen General</h3>
        <div class="valoraciones-summary__grid">
            <div class="valoraciones-summary__card">
                <p class="valoraciones-summary__label">Promedio de Calificaciones</p>
                <p class="valoraciones-summary__value"><?php echo $promedio; ?> ⭐</p>
            </div>
            <div class="valoraciones-summary__card">
                <p class="valoraciones-summary__label">Total Recibidas</p>
                <p class="valoraciones-summary__value"><?php echo count($valoraciones); ?></p>
            </div>
        </div>
    </div>

    <div class="valoraciones-listado" style="margin-top: 4rem;">
        <h3 class="dashboard__subtitle">Detalle de Calificaciones</h3>
        <?php if(!empty($valoraciones)): ?>
            <?php foreach($valoraciones as $valoracion): ?>
                <div class="valoracion-item">
                    <div class="valoracion-item__header">
                        <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                        <span class="valoracion-item__producto">Producto: <strong><?php echo htmlspecialchars($valoracion->producto->nombre); ?></strong></span>
                    </div>
                    <?php if($valoracion->comentario): ?>
                        <p class="valoracion-item__comentario">"<?php echo htmlspecialchars($valoracion->comentario); ?>"</p>
                    <?php endif; ?>
                    <div class="valoracion-item__footer">
                        <span>De: <strong><?php echo htmlspecialchars($valoracion->calificador->nombre); ?></strong></span>
                        <div class="valoracion-item__estado">
                            <?php if($valoracion->moderado == 1): ?>
                                <span style="color: #2E7D31;">(Aprobado)</span>
                            <?php elseif($valoracion->moderado == 0): ?>
                                 <span style="color: #FBC02D;">(Pendiente)</span>
                            <?php else: ?>
                                <span style="color: #C62828;">(Rechazado)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">Aún no has recibido ninguna calificación.</p>
        <?php endif; ?>
    </div>
</div>