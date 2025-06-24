<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor">
    <div class="valoraciones-estadisticas">
        <h4>Resumen de Tus Calificaciones</h4>
        <p><strong>Promedio General:</strong> <?php echo $promedioEstrellas; ?> ⭐ (Basado en <?php echo $totalCalificaciones; ?> reseñas)</p>
        <div class="desglose-barras">
            <?php foreach($desgloseEstrellas as $estrellas => $cantidad): ?>
                <div class="barra-item">
                    <span class="barra-label"><?php echo $estrellas; ?> estrella<?php echo $estrellas > 1 ? 's' : ''; ?></span>
                    <div class="barra-fondo">
                        <div class="barra-progreso" style="width: <?php echo $totalCalificaciones > 0 ? ($cantidad / $totalCalificaciones) * 100 : 0; ?>%;"></div>
                    </div>
                    <span class="barra-porcentaje"><?php echo $totalCalificaciones > 0 ? round(($cantidad / $totalCalificaciones) * 100) : 0; ?>%</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="valoraciones-listado">
        <h4 style="margin-top: 4rem;">Detalle de Calificaciones Recibidas</h4>
        <?php if ($totalCalificaciones > 0): ?>
            <?php foreach($valoracionesRecibidas as $valoracion): ?>
                <div class="valoracion-item">
                    <div class="valoracion-item__header">
                        <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                        <span class="valoracion-item__contexto">
                            Sobre: <strong><?php echo htmlspecialchars($valoracion->producto->nombre ?? 'Producto no disponible'); ?></strong>
                            el <?php echo date('d/m/Y', strtotime($valoracion->creado)); ?>
                        </span>
                    </div>
                    <?php if(!empty($valoracion->comentario)): ?>
                        <p class="valoracion-item__comentario">"<?php echo htmlspecialchars($valoracion->comentario); ?>"</p>
                    <?php endif; ?>
                    <div class="valoracion-item__footer">
                        <span>De: <strong><?php echo htmlspecialchars($valoracion->calificador->nombre); ?></strong></span>
                        <button class="reportar-btn" data-valoracion-id="<?= $valoracion->id ?>">
                            <i class="fa-solid fa-flag"></i> Reportar
                        </button>
                    </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Aún no has recibido ninguna calificación.</p>
        <?php endif; ?>
    </div>
</div>