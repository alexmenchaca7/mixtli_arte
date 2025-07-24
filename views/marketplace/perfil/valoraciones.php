<main class="perfil-usuario">
    <h2 class="perfil-usuario__heading"><?php echo $titulo; ?></h2>
    
    <div class="tabs">
        <button type="button" data-tab="#tab-1" class="tabs__boton current">Calificaciones que has hecho</button>
        <button type="button" data-tab="#tab-2" class="tabs__boton">Calificaciones que has recibido</button>
    </div>

    <div id="tab-1" class="tabs__contenido current">
        <?php if (!empty($valoraciones)): ?>
            <?php else: ?>
            <p class="t-align-center">Aún no has realizado ninguna calificación.</p>
        <?php endif; ?>
    </div>

    <div id="tab-2" class="tabs__contenido">
        <?php if (!empty($valoracionesRecibidas)): ?>
            <div class="valoraciones-estadisticas">
                <h4>Resumen de Tus Calificaciones Recibidas</h4>
                <p><strong>Promedio General:</strong> <?php echo $promedioEstrellasRecibidas; ?> ⭐ (Basado en <?php echo $totalCalificacionesRecibidas; ?> reseñas)</p>
                <div class="desglose-barras">
                    <?php foreach($desgloseEstrellasRecibidas as $estrellas => $cantidad): ?>
                        <div class="barra-item">
                            <span class="barra-label"><?php echo $estrellas; ?> estrella<?php echo $estrellas > 1 ? 's' : ''; ?></span>
                            <div class="barra-fondo">
                                <div class="barra-progreso" style="width: <?php echo $totalCalificacionesRecibidas > 0 ? ($cantidad / $totalCalificacionesRecibidas) * 100 : 0; ?>%;"></div>
                            </div>
                            <span class="barra-porcentaje"><?php echo $totalCalificacionesRecibidas > 0 ? round(($cantidad / $totalCalificacionesRecibidas) * 100) : 0; ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="valoraciones-listado">
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
                            <span>De: <strong><?php echo htmlspecialchars($valoracion->calificador->nombre ?? 'Usuario no disponible'); ?></strong></span>
                            <button class="reportar-btn" data-valoracion-id="<?= $valoracion->id ?>">
                                <i class="fa-solid fa-flag"></i> Reportar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="t-align-center">Aún no has recibido ninguna calificación.</p>
        <?php endif; ?>
        
        </div>
</main>