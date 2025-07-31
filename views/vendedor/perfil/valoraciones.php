<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor">
    <?php
        // Filtrar las valoraciones para mantener solo las que tienen un comentario
        $valoracionesConComentario = [];
        if (!empty($valoracionesRecibidas)) {
            foreach ($valoracionesRecibidas as $valoracion) {
                if (!empty($valoracion->comentario)) {
                    $valoracionesConComentario[] = $valoracion;
                }
            }
        }
    ?>

    <div class="valoraciones-estadisticas" style="margin-bottom: 2rem; border: 1px solid #e0e0e0; padding: 1.5rem; border-radius: .8rem; background-color: #f9f9f9;">
        <h4>Resumen de Tus Calificaciones</h4>
        <p style="font-size: 1.6rem; margin: 0 0 1rem 0;">
            <strong>Promedio General:</strong> <?php echo $promedioEstrellas; ?> ⭐ 
            <span style="color: #666;">(Basado en <?php echo $totalCalificaciones; ?> reseñas)</span>
        </p>
        <?php if ($totalCalificaciones < 5): ?>
            <p style="font-size: 1.3rem; color: #777; margin: 0;"><i>Tus calificaciones y promedio serán públicos para otros usuarios después de que recibas 5 o más valoraciones.</i></p>
        <?php endif; ?>
        
        <div class="desglose-barras" style="margin-top: 1.5rem;">
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

    <?php 
        $esPublico = false; // Mostrar todos los puntos en la vista privada
        include __DIR__ . '/../../templates/_puntos-fuertes.php'; 
    ?>

    <div class="valoraciones-listado">
        <h4 style="margin-top: 4rem;">Detalle de Calificaciones Recibidas</h4>
        
        <?php if (!empty($valoracionesConComentario)): ?>
            <?php foreach($valoracionesConComentario as $valoracion): ?>
                <div class="valoracion-item">
                    <div class="valoracion-item__header">
                        <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                        <span class="valoracion-item__contexto">
                            Sobre: <strong><?php echo htmlspecialchars($valoracion->producto->nombre ?? 'Producto no disponible'); ?></strong>
                            el <?php echo date('d/m/Y', strtotime($valoracion->creado)); ?>
                        </span>
                    </div>
                    
                    <p class="valoracion-item__comentario">"<?php echo htmlspecialchars($valoracion->comentario); ?>"</p>
                    
                    <div class="valoracion-item__footer">
                        <span>De: <strong><?php echo htmlspecialchars($valoracion->calificador->nombre); ?></strong></span>
                        <button class="reportar-btn" data-valoracion-id="<?= $valoracion->id ?>">
                            <i class="fa-solid fa-flag"></i> Reportar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="t-align-center">Aún no has recibido ninguna calificación con comentario.</p>
        <?php endif; ?>
    </div>
</div>