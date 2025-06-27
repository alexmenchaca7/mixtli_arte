<h2 class="dashboard__heading"><?php echo $titulo; ?></h2>

<?php if(!empty($reportes)): ?>
    <div class="dashboard__contenedor">
        <div class="dashboard__contenedor-tabla">
            <table class="table">
                <thead class="table__thead">
                    <tr>
                        <th scope="col" class="table__th">Producto Reportado</th>
                        <th scope="col" class="table__th">Motivo</th>
                        <th scope="col" class="table__th">Estado</th>
                        <th scope="col" class="table__th">Fecha</th>
                        <th scope="col" class="table__th"></th>
                    </tr>
                </thead>
    
                <tbody class="table__tbody">
                    <?php foreach($reportes as $reporte): ?>
                        <tr class="table__tr">
                            <td class="table__td">
                                <?php echo htmlspecialchars($reporte->producto->nombre ?? 'Producto Eliminado'); ?>
                            </td>
                            <td class="table__td"><?php echo htmlspecialchars($reporte->motivo); ?></td>
                            <td class="table__td"><?php echo htmlspecialchars(ucfirst($reporte->estado)); ?></td>
                            <td class="table__td"><?php echo date('d/m/Y', strtotime($reporte->creado)); ?></td>
                            
                            <td class="table__td--acciones">
                                <a class="table__accion table__accion--editar" href="/admin/reportes/ver?id=<?php echo $reporte->id; ?>">
                                    <i class="fa-solid fa-eye"></i>
                                    Ver Detalles
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <p class="t-align-center">No hay reportes de productos.</p>
<?php endif; ?>

<?php echo $paginacion; ?>