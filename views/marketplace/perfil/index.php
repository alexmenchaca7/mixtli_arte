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
                 <?php if(!empty($valoraciones)): ?>
                    <?php foreach($valoraciones as $valoracion): ?>
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
                    <p>Aún no has recibido ninguna calificación aprobada.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>