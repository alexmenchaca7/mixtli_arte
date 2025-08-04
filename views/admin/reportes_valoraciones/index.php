<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<div class="dashboard__contenedor">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

    <?php if(!empty($reportes)): ?>
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Fecha</th> 
                        <th scope="col" class="table__th">Comentario</th>
                        <th scope="col" class="table__th">Autor</th>
                        <th scope="col" class="table__th">Producto</th>
                        <th scope="col" class="table__th">Reportador</th>
                        <th scope="col" class="table__th">Motivo</th>
                        <th scope="col" class="table__th">Detalles</th>
                        <th scope="col" class="table__th">Estado</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
    
                <tbody class="table__tbody">
                    <?php foreach($reportes as $reporte): ?>
                        <tr class="table__tr">
                            <td class="table__td"><?php echo htmlspecialchars($reporte->creado); ?></td> <td class="table__td" style="white-space: normal;"><?php echo htmlspecialchars($reporte->valoracion->comentario ?? 'N/A'); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($reporte->autorComentario->nombre . ' ' . $reporte->autorComentario->apellido ?? 'N/A'); ?></td>
                            <td class="table__td">
                                <?php if(isset($reporte->producto)): ?>
                                    <a href="/marketplace/producto?id=<?php echo $reporte->producto->id; ?>" target="_blank" class="table__accion table__accion--editar">
                                        <?php echo htmlspecialchars($reporte->producto->nombre); ?>
                                    </a>
                                <?php else: ?>
                                    <span>N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="table__td"><?php echo htmlspecialchars($reporte->reportador->nombre . ' ' . $reporte->reportador->apellido ?? 'N/A'); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars($reporte->motivo); ?></td>
                            <td class="table__td" style="white-space: normal;"><?php echo htmlspecialchars($reporte->comentarios ?? 'N/A'); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars(ucfirst($reporte->estado)); ?></td>
                            
                            <td class="table__td--acciones">
                                <?php if($reporte->estado === 'pendiente'): ?>
                                    <form class="table__formulario" action="/admin/reportes-valoraciones/resolver" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $reporte->id; ?>">
                                        <input type="hidden" name="accion" value="descartar">
                                        <button class="table__accion table__accion--editar" type="submit">Descartar Reporte</button>
                                    </form>
                                    <form class="table__formulario" action="/admin/reportes-valoraciones/resolver" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $reporte->id; ?>">
                                        <input type="hidden" name="accion" value="sancionar">
                                        <button class="table__accion table__accion--eliminar" type="submit">Sancionar al Autor</button>
                                    </form>
                                <?php else: ?>
                                    <span class="table__accion table__accion--revisado">Revisado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php echo $paginacion; ?>
    <?php else: ?>
        <p class="t-align-center">No hay reportes de comentarios.</p>
    <?php endif; ?>
</div>