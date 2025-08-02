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
                <div class="notificacion-item <?php echo $notificacion->leida ? '' : 'no-leida'; ?>" data-id="<?php echo $notificacion->id; ?>">
                    <a href="<?php echo htmlspecialchars($notificacion->url); ?>" class="notificacion-item__enlace">
                        <div class="notificacion-item__icono">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="notificacion-item__contenido">
                            <p class="notificacion-item__mensaje"><?php echo htmlspecialchars(stripslashes($notificacion->mensaje)); ?></p>
                            <span class="notificacion-item__fecha"><?php echo date('d/m/Y h:i A', strtotime($notificacion->creado)); ?></span>
                        </div>
                    </a>
                    <div class="notificacion-item__acciones">
                        <?php if ($notificacion->leida): ?>
                            <button type="button" class="accion--marcar-no-leida" title="Marcar como no leída">
                                <i class="fa-solid fa-envelope"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="accion--marcar-leida" title="Marcar como leída">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        <?php endif; ?>
                        <button type="button" class="accion--eliminar" title="Eliminar">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="t-align-center">No tienes notificaciones.</p>
        <?php endif; ?>
    </div>
</main>