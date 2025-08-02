<main class="contenedor seccion">
    <?php if($_SESSION['rol'] == 'vendedor'): ?>
        <h1 class="mt-0 t-align-center"><?php echo $titulo; ?></h1>
    <?php endif; ?>

    <div class="notificaciones-contenedor">
        <?php if (!empty($notificaciones)): ?>
            <?php foreach ($notificaciones as $notificacion): ?>
                <div class="notificacion-item <?php echo $notificacion->leida ? '' : 'no-leida'; ?>" data-id="<?php echo $notificacion->id; ?>">
                    <a href="<?php echo htmlspecialchars($notificacion->url); ?>" class="notificacion-item__enlace">
                        <div class="notificacion-item__icono">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="notificacion-item__contenido">
                            <p class="notificacion-item__mensaje"><?php echo htmlspecialchars($notificacion->mensaje); ?></p>
                            <span class="notificacion-item__fecha"><?php echo date('d/m/Y h:i A', strtotime($notificacion->creado)); ?></span>
                        </div>
                    </a>
                    <div class="notificacion-item__acciones">
                        <?php if (!$notificacion->leida): ?>
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
            <p class="t-align-center">No tienes notificaciones nuevas.</p>
        <?php endif; ?>
    </div>
</main>