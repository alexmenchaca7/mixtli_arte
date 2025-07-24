<div class="perfil-usuario">
    <div class="perfil-usuario__header">
        </div>

    <div class="perfil-usuario__body contenedor">
        <div class="perfil-usuario__info-principal">
            <div class="perfil-usuario__imagen">
                <img src="/img/usuarios/<?php echo $usuario->imagen ? $usuario->imagen . '.png' : 'default.png'; ?>" alt="Imagen de perfil">
            </div>
            <div class="perfil-usuario__datos">
                <h1><?php echo htmlspecialchars($usuario->nombre . ' ' . $usuario->apellido); ?></h1>
                <p>Comprador</p>
            </div>
            <div class="perfil-usuario__acciones">
                <a href="/comprador/perfil/editar" class="boton-rosa">Editar Perfil</a>
                
                <?php if(is_auth('comprador')): ?>
                    <form class="dashboard__form" method="POST" action="/logout">
                        <input type="submit" value="Cerrar Sesión" class="boton-rosa">
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="perfil-usuario__grid">
            <aside class="perfil-usuario__sidebar">
                <div class="perfil-card">
                    <h3>Sobre Mí</h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario->email ?? ''); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario->telefono ?? 'No especificado'); ?></p>
                    <p><strong>Dirección:</strong> 
                        <?php 
                            $direccionResidencial = null;
                            foreach($direcciones as $dir) {
                                if ($dir->tipo === 'residencial') {
                                    $direccionResidencial = $dir;
                                    break;
                                }
                            }
                            $direccionCompleta = ($direccionResidencial && $direccionResidencial->calle) 
                                ? ($direccionResidencial->calle . ', ' . $direccionResidencial->colonia) 
                                : 'No especificada';
                            echo htmlspecialchars($direccionCompleta);
                        ?>
                    </p>
                </div>
                <div class="perfil-card">
                    <h3>Mis Preferencias</h3>
                    <?php if(!empty($categoriasInteres)): ?>
                        <ul class="lista-preferencias">
                        <?php foreach($categoriasInteres as $categoria): ?>
                            <li><span class="tag-categoria"><?php echo htmlspecialchars($categoria->nombre); ?></span></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Aún no has seleccionado categorías de interés. ¡<a href="/comprador/perfil/editar">Edita tu perfil</a> para hacerlo!</p>
                    <?php endif; ?>
                </div>
            </aside>

            <main class="perfil-usuario__valoraciones">
                <h3>Valoraciones Recibidas</h3>

                <div class="valoraciones-estadisticas" style="margin-bottom: 2rem; border: 1px solid #e0e0e0; padding: 1.5rem; border-radius: .8rem; background-color: #f9f9f9;">
                    <h4>Resumen de Calificaciones</h4>
                    <p style="font-size: 1.6rem; margin: 0 0 1rem 0;">
                        <strong>Promedio General:</strong> <?php echo $promedioEstrellas; ?> ⭐ 
                        <span style="color: #666;">(Basado en <?php echo $totalCalificaciones; ?> reseñas)</span>
                    </p>
                    <?php if ($totalCalificaciones < 5): ?>
                        <p style="font-size: 1.3rem; color: #777; margin: 0;"><i>Tus calificaciones y promedio serán públicos para otros usuarios después de que recibas 5 o más valoraciones.</i></p>
                    <?php endif; ?>

                    <div class="desglose-barras" style="margin-top: 1.5rem;">
                        <?php krsort($desgloseEstrellas); // Ordenar de 5 a 1 ?>
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
                    <p>Aún no has recibido ninguna calificación con comentario.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>