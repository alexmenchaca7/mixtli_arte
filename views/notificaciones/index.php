<main class="contenedor seccion">
    <div class="notificaciones-header">
        <h2 class="notificaciones-titulo">Notificaciones</h2>
        <button type="button" id="marcar-todas-leidas" class="boton-rosa" style="display: <?php echo $noLeidasCount > 0 ? 'inline-flex' : 'none'; ?>;">
            <i class="fa-solid fa-check-double"></i> Marcar todas como leídas
        </button>
    </div>

    <div class="notificaciones-contenedor">
        <?php if (!empty($notificaciones)): ?>
            <?php foreach ($notificaciones as $notificacion): ?>
                <?php
                    // Decodificamos el JSON una vez al inicio para simplificar la lógica
                    $mensajeData = json_decode(stripslashes($notificacion->mensaje), true);
                    $esJsonConSugerencias = (json_last_error() === JSON_ERROR_NONE && !empty($mensajeData['sugerencias']));
                ?>

                <div class="notificacion-item <?php echo $notificacion->leida ? '' : 'no-leida'; ?>" data-id="<?php echo $notificacion->id; ?>">

                    <div class="notificacion-item__principal">
                        <a href="<?php echo htmlspecialchars($notificacion->url); ?>" class="notificacion-item__enlace">
                            <div class="notificacion-item__icono">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div class="notificacion-item__contenido">
                                <?php
                                    // Obtenemos el texto del mensaje, ya sea del JSON o directamente
                                    $mensajeTexto = $esJsonConSugerencias ? $mensajeData['mensaje'] : stripslashes($notificacion->mensaje);
                                    echo '<p class="notificacion-item__mensaje">' . htmlspecialchars($mensajeTexto) . '</p>';
                                ?>
                                <span class="notificacion-item__fecha"><?php echo date('d/m/Y h:i A', strtotime($notificacion->creado)); ?></span>
                            </div>
                        </a>
                        <div class="notificacion-item__acciones">
                            <?php if ($notificacion->leida): ?>
                                <button type="button" class="accion--marcar-no-leida" title="Marcar como no leída"><i class="fa-solid fa-envelope"></i></button>
                            <?php else: ?>
                                <button type="button" class="accion--marcar-leida" title="Marcar como leída"><i class="fa-solid fa-check"></i></button>
                            <?php endif; ?>
                            <button type="button" class="accion--eliminar" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                    </div>

                    <?php if ($esJsonConSugerencias): ?>
                        <div class="notificacion-item__sugerencias-wrapper">
                            <div class="notificacion-item__sugerencias">
                                <?php foreach ($mensajeData['sugerencias'] as $sugerencia): ?>
                                    <a href="<?php echo htmlspecialchars($sugerencia['urlProducto']); ?>" class="notificacion-item__sugerencia" title="<?php echo htmlspecialchars($sugerencia['nombre']); ?>">
                                        <img src="<?php echo htmlspecialchars($sugerencia['urlImagen']); ?>" alt="<?php echo htmlspecialchars($sugerencia['nombre']); ?>" class="notificacion-item__sugerencia-imagen">
                                        <span class="notificacion-item__sugerencia-nombre"><?php echo htmlspecialchars($sugerencia['nombre']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="t-align-center">No tienes notificaciones.</p>
        <?php endif; ?>
    </div>

    <?php echo $paginacion; ?>
</main>