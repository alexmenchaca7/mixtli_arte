<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor-boton">
    <a class="dashboard__boton" href="/admin/reportes">
        <i class="fa-solid fa-circle-arrow-left"></i>
        Volver
    </a>
</div>

<div class="dashboard__contenedor">
    <div class="reporte-detalle">
        
        <div class="dashboard__card">
            <div class="reporte-seccion">
                <h3><i class="fa-solid fa-flag"></i> Detalles del Reporte</h3>
                <p><strong>Motivo:</strong> <?php echo htmlspecialchars($reporte->motivo); ?></p>
                <p><strong>Comentarios del Comprador:</strong> <?php echo htmlspecialchars($reporte->comentarios ?? 'Sin comentarios'); ?></p>
                <p><strong>Fecha del Reporte:</strong> <?php echo date('d/m/Y H:i', strtotime($reporte->creado)); ?></p>
                <p><strong>Estado Actual:</strong> <span class="reporte-estado reporte-estado--<?php echo $reporte->estado; ?>"><?php echo ucfirst($reporte->estado); ?></span></p>
            </div>
        </div>

        <div class="dashboard__card">
            <div class="reporte-seccion">
                <h3><i class="fa-solid fa-box"></i> Producto Reportado</h3>
                <?php if ($producto): ?>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($producto->nombre); ?></p>
                    <p><strong>Precio:</strong> $<?php echo htmlspecialchars($producto->precio); ?></p>
                    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($producto->descripcion); ?></p>
                    <div class="reporte-imagenes-contenedor">
                        <?php if (!empty($imagenes_producto)): ?>
                            <?php foreach ($imagenes_producto as $imagen): ?>
                                <?php if (!empty($imagen->url)): ?>
                                    <picture>
                                        <source srcset="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.webp" type="image/webp">
                                        <source srcset="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.png" type="image/png">
                                        <img src="/img/productos/<?php echo htmlspecialchars($imagen->url); ?>.png" alt="Imagen del producto" style="max-width: 150px; margin: 0.5rem;">
                                    </picture>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>El producto no tiene imágenes asignadas.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="reporte-error">El producto asociado a este reporte ya ha sido eliminado.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard__card">
            <div class="reporte-seccion">
                <h3><i class="fa-solid fa-user"></i> Historial del Vendedor</h3>
                <?php if ($vendedor): ?>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($vendedor->nombre . ' ' . $vendedor->apellido); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($vendedor->email); ?></p>
                    
                    <h4>Otros Productos del Vendedor (<?php echo count($historial_otros_productos); ?>)</h4>
                    <?php if(!empty($historial_otros_productos)): ?>
                        <ul>
                            <?php foreach($historial_otros_productos as $otro_producto): ?>
                                <li><?php echo htmlspecialchars($otro_producto->nombre); ?> (ID: <?php echo $otro_producto->id; ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>El vendedor no tiene otros productos.</p>
                    <?php endif; ?>
                    
                    <h4>Otros Reportes Recibidos (<?php echo count($historial_otros_reportes); ?>)</h4>
                    <?php if(!empty($historial_otros_reportes)): ?>
                        <ul>
                            <?php foreach($historial_otros_reportes as $otro_reporte): ?>
                                <li>Reporte por "<?php echo htmlspecialchars($otro_reporte->motivo); ?>" el <?php echo date('d/m/Y', strtotime($otro_reporte->creado)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>El vendedor no tiene otros reportes en sus productos.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="reporte-error">No se encontró información del vendedor.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if($reporte->estado === 'pendiente'): ?>
            <div class="dashboard__card">
                <div class="reporte-seccion">
                    <h3><i class="fa-solid fa-gavel"></i> Acciones de Moderación</h3>
                    <div class="reporte-acciones">
                        <form class="table__formulario" action="/admin/reportes/clasificar" method="POST">
                            <input type="hidden" name="id" value="<?php echo $reporte->id; ?>">
                            <input type="hidden" name="clasificacion" value="valido">
                            <button class="table__accion table__accion--editar" type="submit">Marcar Válido y Eliminar</button>
                        </form>
                        <form class="table__formulario" action="/admin/reportes/clasificar" method="POST">
                            <input type="hidden" name="id" value="<?php echo $reporte->id; ?>">
                            <input type="hidden" name="clasificacion" value="no_valido">
                            <button class="table__accion table__accion--eliminar" type="submit">Marcar como No Válido</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>