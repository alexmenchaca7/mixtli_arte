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
                <p>
                    <?php echo ($comprador->sexo === 'Femenino') ? 'Compradora' : 'Comprador'; ?>
                </p>
                <?php if ($totalCalificaciones >= 5): ?>
                    <div class="vendedor-rating">
                        <span><?php echo $promedioEstrellas; ?> ⭐</span>
                        <span>(<?php echo $totalCalificaciones; ?> calificaciones)</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="perfil-card">
            <h3>Calificaciones de este Comprador</h3>
            <?php if($totalCalificaciones >= 5): ?>
                <div class="valoraciones-estadisticas">
                    <h4>Resumen de Calificaciones</h4>
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

                <?php if(!empty($valoracionesConComentario)): ?>
                    <?php foreach($valoracionesConComentario as $valoracion): ?>
                        <div class="valoracion-item">
                            <div class="valoracion-item__header">
                                <span class="valoracion-item__estrellas"><?php echo str_repeat('⭐', $valoracion->estrellas); ?></span>
                                <span class="valoracion-item__contexto">
                                    Sobre: <strong><?php echo htmlspecialchars($valoracion->producto->nombre); ?></strong>
                                    el <?php echo date('d/m/Y', strtotime($valoracion->creado)); ?>
                                </span>
                            </div>
                            <?php if($valoracion->comentario): ?>
                                <p class="valoracion-item__comentario">"<?php echo htmlspecialchars($valoracion->comentario); ?>"</p>
                            <?php endif; ?>
                            <div class="valoracion-item__footer">
                                <span>De: <strong><?php echo htmlspecialchars($valoracion->calificador->nombre); ?></strong></span>
                                <button class="reportar-btn" data-valoracion-id="<?= $valoracion->id ?>">
                                    <i class="fa-solid fa-flag"></i> Reportar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Este comprador aún no ha recibido ninguna calificación con comentario.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Este comprador necesita al menos 5 calificaciones para que se muestren públicamente.</p>
            <?php endif; ?>
        </div>
    </div>
</div>